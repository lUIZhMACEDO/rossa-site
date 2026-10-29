<?php

namespace App\Http\Controllers;

use App\Enums\MediaUploadStatus;
use App\Http\Requests\AssignMediaUploadSampleGroupRequest;
use App\Http\Requests\InitiateMediaUploadRequest;
use App\Http\Requests\UpdateMediaUploadUserMetadataRequest;
use App\Jobs\ScanMediaUpload;
use App\Models\MediaUpload;
use App\Models\Study;
use App\Support\MediaUploadTable;
use App\Support\StorageFileResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudyMediaUploadController extends Controller
{
    public function store(InitiateMediaUploadRequest $request, Study $study)
    {
        $this->assertPhenotypeEditable($study);

        $originalFilename = $request->input('original_filename');
        $uploadUuid = (string) Str::uuid();
        $sanitized = $this->sanitizeFilename($originalFilename);
        $relativeDir = "media_uploads/{$study->id}/{$uploadUuid}";
        $partPath = "{$relativeDir}/{$sanitized}.part";
        $chunkSize = (int) config('media_upload.chunk_size');

        if (Storage::put($partPath, '', ['visibility' => 'private']) === false) {
            return response()->json(['message' => 'Unable to initialize upload file.'], 500);
        }

        try {
            $media = MediaUpload::create([
                'study_id' => $study->id,
                'sample_group_id' => (int) $request->input('sample_group_id'),
                'path' => $partPath,
                'original_filename' => $originalFilename,
                'mime_type' => $request->input('mime_type'),
                'size' => (int) $request->input('size'),
                'status' => MediaUploadStatus::Pending,
                'upload_uuid' => $uploadUuid,
                'bytes_received' => 0,
                'chunk_size' => $chunkSize,
                'metadata' => [
                    'user_upload' => $request->userUploadPayload(),
                ],
            ]);
        } catch (\Throwable $e) {
            Storage::delete($partPath);
            $dir = dirname($partPath);
            if ($dir && $dir !== '.' && Storage::directoryExists($dir) && Storage::files($dir) === []) {
                Storage::deleteDirectory($dir);
            }

            throw $e;
        }

        return $this->jsonStatus($media->fresh(['sampleGroup']), 201, false);
    }

    public function show(Study $study, MediaUpload $media)
    {
        $this->assertBelongsToStudy($study, $media);

        return $this->jsonStatus($media);
    }

    public function storeChunk(Request $request, Study $study, MediaUpload $media, int $index)
    {
        $this->assertBelongsToStudy($study, $media);
        $this->assertPhenotypeEditable($study);

        if ($index < 0) {
            return response()->json(['message' => 'Invalid chunk index.'], 422);
        }

        // Buffer the body before locking so concurrent requests serialize on the row,
        // not on reading the request.
        $content = $request->getContent();
        $actualLength = strlen($content);

        return DB::transaction(function () use ($media, $index, $content, $actualLength) {
            $media = MediaUpload::query()->whereKey($media->id)->lockForUpdate()->firstOrFail();

            if (! $media->isInProgress()) {
                return response()->json(['message' => 'Upload is not accepting chunks.'], 422);
            }

            $chunkSize = (int) $media->chunk_size;
            $offset = $index * $chunkSize;

            if ($offset >= $media->size) {
                return response()->json(['message' => 'Chunk index exceeds file size.'], 422);
            }

            if ($media->bytes_received !== $offset) {
                if ($media->bytes_received > $offset) {
                    return $this->jsonStatus($media);
                }

                return response()->json([
                    'message' => 'Chunks must be uploaded in order.',
                    'bytes_received' => $media->bytes_received,
                    'next_chunk_index' => $media->nextChunkIndex(),
                ], 409);
            }

            $expectedLength = (int) min($chunkSize, $media->size - $offset);

            if ($actualLength !== $expectedLength) {
                return response()->json([
                    'message' => "Expected {$expectedLength} bytes for this chunk, received {$actualLength}.",
                ], 422);
            }

            $partPath = $media->partPath();
            $absolute = Storage::path($partPath);
            $handle = fopen($absolute, 'c+b');

            if ($handle === false) {
                return response()->json(['message' => 'Unable to open upload file.'], 500);
            }

            try {
                if (fseek($handle, $offset) !== 0) {
                    return response()->json(['message' => 'Unable to seek upload file.'], 500);
                }

                $written = fwrite($handle, $content);
                if ($written !== $actualLength) {
                    return response()->json(['message' => 'Failed to write full chunk.'], 500);
                }
            } finally {
                fclose($handle);
            }

            $media->bytes_received = $offset + $actualLength;
            $media->status = MediaUploadStatus::Uploading;
            $media->save();

            return $this->jsonStatus($media, ensureMetadata: false);
        });
    }

    public function complete(Study $study, MediaUpload $media)
    {
        $this->assertBelongsToStudy($study, $media);
        $this->assertPhenotypeEditable($study);

        if ($media->isComplete()) {
            return $this->jsonStatus($media);
        }

        if ($media->status === MediaUploadStatus::Scanning) {
            return $this->jsonStatus($media, 202);
        }

        if (! $media->isInProgress()) {
            return response()->json(['message' => 'Upload cannot be completed.'], 422);
        }

        if ((int) $media->bytes_received !== (int) $media->size) {
            return response()->json([
                'message' => 'Upload is incomplete.',
                'bytes_received' => $media->bytes_received,
                'size' => $media->size,
            ], 422);
        }

        $partPath = $media->partPath();
        $finalPath = $media->finalPath();

        if (! Storage::exists($partPath)) {
            return response()->json(['message' => 'Upload part file missing.'], 422);
        }

        $onDiskSize = Storage::size($partPath);
        if ((int) $onDiskSize !== (int) $media->size) {
            $media->status = MediaUploadStatus::Failed;
            $media->save();

            return response()->json([
                'message' => 'On-disk size does not match expected size.',
                'on_disk_size' => $onDiskSize,
                'size' => $media->size,
            ], 422);
        }

        if ($partPath !== $finalPath && ! Storage::move($partPath, $finalPath)) {
            $media->status = MediaUploadStatus::Failed;
            $media->save();

            return response()->json(['message' => 'Unable to finalize upload file.'], 500);
        }

        $media->path = $finalPath;
        $media->save();

        try {
            $media->ensureMetadata();
        } catch (\Throwable $e) {
            report($e);
        }

        if (config('media_upload.malware_scan.skip')) {

            $media->status = MediaUploadStatus::Complete;
            $media->malware_scan_status = 'passed';
            $media->malware_scan_message = 'Malware scan skipped (disabled).';
            $media->malware_scanned_at = now();
            $media->save();

            return $this->jsonStatus($media, 200, ensureMetadata: false);
        }

        $media->status = MediaUploadStatus::Scanning;
        $media->malware_scan_status = 'scanning';
        $media->malware_scan_message = null;
        $media->malware_scanned_at = null;
        $media->save();

        ScanMediaUpload::dispatch($media);

        return $this->jsonStatus($media, 202, ensureMetadata: false);
    }

    public function download(Study $study, MediaUpload $media): StreamedResponse
    {
        $this->assertBelongsToStudy($study, $media);

        if (! $media->isDownloadable() || ! Storage::exists($media->path)) {
            abort(404);
        }

        return StorageFileResponse::download($media->path, $media->original_filename, $media->mime_type);
    }

    public function destroy(Study $study, MediaUpload $media)
    {
        $this->assertBelongsToStudy($study, $media);
        $this->assertPhenotypeEditable($study);

        $media->deleteStoredFiles();
        $media->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Deleted.']);
        }

        return redirect()
            ->route('study.scan-datasets.index', $study)
            ->with('success', 'Media file deleted.');
    }

    public function row(Study $study, MediaUpload $media)
    {
        $this->assertBelongsToStudy($study, $media);
        $media->load('sampleGroup');

        try {
            $media->ensureMetadata();
        } catch (\Throwable $e) {
            report($e);
        }

        return view('study.phenotype-analysis.partials.media-upload-row', MediaUploadTable::rowViewData($study, $media));
    }

    public function assignSampleGroup(AssignMediaUploadSampleGroupRequest $request, Study $study, MediaUpload $media)
    {
        $this->assertBelongsToStudy($study, $media);
        $this->assertPhenotypeEditable($study);

        $media->update([
            'sample_group_id' => (int) $request->input('sample_group_id'),
        ]);

        return $this->mutationResponse($media);
    }

    public function updateUserUpload(UpdateMediaUploadUserMetadataRequest $request, Study $study, MediaUpload $media)
    {
        $this->assertBelongsToStudy($study, $media);
        $this->assertPhenotypeEditable($study);

        $metadata = is_array($media->metadata) ? $media->metadata : [];
        $metadata['user_upload'] = $request->userUploadPayload();
        $media->update([
            'metadata' => $metadata,
        ]);

        return $this->mutationResponse($media);
    }

    protected function mutationResponse(MediaUpload $media)
    {
        if (request()->header('HX-Request')) {
            return response()->noContent();
        }

        return $this->jsonStatus($media->fresh(['sampleGroup']), ensureMetadata: false);
    }

    protected function assertBelongsToStudy(Study $study, MediaUpload $media): void
    {
        abort_unless((int) $media->study_id === (int) $study->id, 404);
    }

    protected function assertPhenotypeEditable(Study $study): void
    {
        abort_if($study->isPhenotypeAnalysisLocked(auth()->user()), 403);
    }

    protected function jsonStatus(MediaUpload $media, int $status = 200, bool $ensureMetadata = true)
    {
        return response()->json(
            $this->statusPayload($media, $ensureMetadata),
            $status,
            [],
            JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR
        );
    }

    protected function statusPayload(MediaUpload $media, bool $ensureMetadata = true): array
    {
        $media->loadMissing('sampleGroup');
        if ($ensureMetadata) {
            try {
                $media->ensureMetadata();
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $metadata = $media->metadata;
        if (is_array($metadata)) {
            $metadata = $media->jsonSafeMetadata($metadata);
        }

        return [
            'id' => $media->id,
            'upload_uuid' => $media->upload_uuid,
            'original_filename' => $media->original_filename,
            'extension' => $media->extension(),
            'size' => $media->size,
            'bytes_received' => $media->bytes_received,
            'chunk_size' => $media->chunk_size,
            'next_chunk_index' => $media->nextChunkIndex(),
            'status' => $media->status->value,
            'status_label' => $media->status->label(),
            'mime_type' => $media->mime_type,
            'malware_scan_status' => $media->malware_scan_status,
            'malware_scan_message' => $media->malware_scan_message,
            'malware_scanned_at' => $media->malware_scanned_at?->toIso8601String(),
            'downloadable' => $media->isDownloadable(),
            'metadata' => $metadata,
            'sample_group_id' => $media->sample_group_id ? (int) $media->sample_group_id : null,
            'sample_group_label' => $media->sampleGroup?->label,
        ];
    }

    protected function sanitizeFilename(string $filename): string
    {
        $basename = basename($filename);
        $sanitized = preg_replace('/[^A-Za-z0-9._-]/', '_', $basename) ?: 'upload.bin';

        return $sanitized;
    }
}
