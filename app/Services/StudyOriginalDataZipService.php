<?php

namespace App\Services;

use App\Enums\UploadSteps;
use App\Models\AnalysisUpload;
use App\Models\MediaUpload;
use App\Models\Study;
use App\Support\MediaUploadTable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipStream\CompressionMethod;
use ZipStream\ZipStream;

class StudyOriginalDataZipService
{
    /**
     * @return array{uploads: Collection, mediaUploads: Collection, totalBytes: int}
     */
    public function downloadableFiles(Study $study): array
    {
        $uploads = $study->analysisUploads()
            ->with(['analysisType', 'specimenType', 'analyteCategory', 'region', 'mechanicalTestingDetails'])
            ->where('current_step', UploadSteps::COMPLETE)
            ->orderBy('original_filename')
            ->get();

        $mediaUploads = $study->mediaUploads()
            ->with(['sampleGroup'])
            ->downloadable()
            ->orderBy('original_filename')
            ->get();

        $totalBytes = (int) $uploads->sum('size') + (int) $mediaUploads->sum('size');

        return compact('uploads', 'mediaUploads', 'totalBytes');
    }

    public function stream(Study $study): StreamedResponse
    {
        ['uploads' => $uploads, 'mediaUploads' => $mediaUploads] = $this->downloadableFiles($study);

        abort_if($uploads->isEmpty() && $mediaUploads->isEmpty(), 404);

        $zipName = $this->zipFilename($study);

        if (session()->isStarted()) {
            session()->save();
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        return response()->streamDownload(function () use ($study, $uploads, $mediaUploads, $zipName) {
            set_time_limit(0);

            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            $zip = new ZipStream(
                outputName: $zipName,
                sendHttpHeaders: false,
                defaultCompressionMethod: CompressionMethod::STORE,
                defaultEnableZeroHeader: true,
                enableZip64: true,
            );

            $usedNames = [];
            $manifestFiles = [];

            foreach ($uploads as $upload) {
                $archivePath = $this->uniqueArchivePath(
                    'spreadsheets',
                    (string) $upload->original_filename,
                    $usedNames,
                );

                if ($this->addStorageFile($zip, $upload->path, $archivePath, (int) $upload->size)) {
                    $manifestFiles[$archivePath] = $this->spreadsheetManifestEntry($upload, $archivePath);
                }
            }

            foreach ($mediaUploads as $media) {
                $archivePath = $this->uniqueArchivePath(
                    'images',
                    (string) $media->original_filename,
                    $usedNames,
                );

                if ($this->addStorageFile($zip, $media->path, $archivePath, (int) $media->size)) {
                    $manifestFiles[$archivePath] = $this->imageManifestEntry($media, $archivePath);
                }
            }

            $zip->addFile(
                fileName: 'file-manifest.json',
                data: json_encode([
                    'study_id' => $study->id,
                    'study_identifier' => $study->identifier,
                    'generated_at' => now()->toIso8601String(),
                    'files' => $manifestFiles,
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            );

            $zip->finish();
        }, $zipName, [
            'Content-Type' => 'application/zip',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);

        return number_format($bytes / (1024 ** $power), $power >= 2 ? 1 : 0).' '.$units[$power];
    }

    protected function spreadsheetManifestEntry(AnalysisUpload $upload, string $archivePath): array
    {
        return [
            'path' => $archivePath,
            'original_filename' => $upload->original_filename,
            'type' => 'spreadsheet',
            'size' => $upload->size,
            'metadata' => [
                'analysis_type' => $upload->analysisType?->name,
                'context' => $upload->getContextDescription(),
                'specimen_type' => $upload->specimenType?->name,
                'analyte_category' => $upload->analyteCategory?->name,
                'region' => $upload->region?->label,
            ],
        ];
    }

    protected function imageManifestEntry(MediaUpload $media, string $archivePath): array
    {
        $userUpload = is_array($media->metadata['user_upload'] ?? null)
            ? $media->metadata['user_upload']
            : [];

        $scanMetadata = is_array($media->metadata) ? $media->metadata : [];
        unset($scanMetadata['user_upload']);

        return [
            'path' => $archivePath,
            'original_filename' => $media->original_filename,
            'type' => 'image',
            'size' => $media->size,
            'mime_type' => $media->mime_type,
            'metadata' => [
                'sample_group' => $media->sampleGroup?->label,
                'processing_level' => ! empty($userUpload['processing_level'])
                    ? MediaUploadTable::formatUserUpload($userUpload, 'processing_level')
                    : null,
                'bone_type' => ! empty($userUpload['bone_type'])
                    ? MediaUploadTable::formatUserUpload($userUpload, 'bone_type')
                    : null,
                'scan_extent' => ! empty($userUpload['scan_extent'])
                    ? MediaUploadTable::formatUserUpload($userUpload, 'scan_extent')
                    : null,
                'user_upload' => $userUpload ?: null,
                'scan_header' => $scanMetadata ?: null,
            ],
        ];
    }

    protected function zipFilename(Study $study): string
    {
        $base = $study->identifier ?: 'study-'.$study->id;

        return preg_replace('/[^A-Za-z0-9._-]+/', '-', $base).'-original-data.zip';
    }

    protected function uniqueArchivePath(string $directory, string $filename, array &$usedNames): string
    {
        $filename = $filename !== '' ? $filename : 'file';
        $key = $directory.'/'.$filename;

        if (! isset($usedNames[$key])) {
            $usedNames[$key] = 1;

            return $key;
        }

        $usedNames[$key]++;
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $stem = pathinfo($filename, PATHINFO_FILENAME) ?: 'file';

        return $extension !== ''
            ? "{$directory}/{$stem}_{$usedNames[$key]}.{$extension}"
            : "{$directory}/{$stem}_{$usedNames[$key]}";
    }

    protected function addStorageFile(ZipStream $zip, ?string $path, string $nameInZip, int $knownSize = 0): bool
    {
        if (! $path || ! Storage::exists($path)) {
            return false;
        }

        $exactSize = $knownSize > 0 ? $knownSize : null;

        try {
            $absolute = Storage::path($path);
            if (is_readable($absolute) && is_file($absolute)) {
                $zip->addFileFromPath(
                    fileName: $nameInZip,
                    path: $absolute,
                    exactSize: $exactSize,
                );

                return true;
            }
        } catch (\Throwable) {
            // Disk may not expose local paths (e.g. S3); fall through to stream.
        }

        $stream = Storage::readStream($path);
        if (! is_resource($stream)) {
            return false;
        }

        try {
            $zip->addFileFromStream(
                fileName: $nameInZip,
                stream: $stream,
                exactSize: $exactSize,
            );

            return true;
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }
}
