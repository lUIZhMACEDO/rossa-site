<?php

namespace App\Models;

use App\Enums\MediaUploadStatus;
use App\Services\ScanDatasetMetadataReader;
use App\Traits\LocksWithStudy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MediaUpload extends Model
{
    use LocksWithStudy;

    public function isStudyLocked(): bool
    {
        $study = $this->getStudyForLock();

        return $study?->isPhenotypeAnalysisLocked(auth()->user()) ?? false;
    }

    protected $fillable = [
        'study_id',
        'sample_group_id',
        'path',
        'original_filename',
        'mime_type',
        'size',
        'status',
        'upload_uuid',
        'bytes_received',
        'chunk_size',
        'malware_scan_status',
        'malware_scan_message',
        'malware_scanned_at',
        'metadata',
    ];

    protected $casts = [
        'size' => 'integer',
        'bytes_received' => 'integer',
        'chunk_size' => 'integer',
        'malware_scanned_at' => 'datetime',
        'status' => MediaUploadStatus::class,
        'metadata' => 'array',
    ];

    public function sampleGroup()
    {
        return $this->belongsTo(SampleGroup::class);
    }

    public function study()
    {
        return $this->belongsTo(Study::class);
    }

    public function scopeFailedScan($query)
    {
        return $query->where('status', MediaUploadStatus::Failed)
            ->whereIn('malware_scan_status', ['infected', 'error']);
    }

    public function isComplete(): bool
    {
        return $this->status === MediaUploadStatus::Complete;
    }

    public function isDownloadable(): bool
    {
        return $this->isComplete() && $this->malware_scan_status === 'passed';
    }

    public function scopeDownloadable($query)
    {
        return $query
            ->where('status', MediaUploadStatus::Complete)
            ->where('malware_scan_status', 'passed');
    }

    public function isInProgress(): bool
    {
        return in_array($this->status, [
            MediaUploadStatus::Pending,
            MediaUploadStatus::Uploading,
        ], true);
    }

    public function extension(): string
    {
        return strtolower(pathinfo((string) $this->original_filename, PATHINFO_EXTENSION));
    }

    /**
     * Parse and persist scan-dataset header metadata if missing and file is readable.
     * Preserves any user_upload fields collected at initiate time.
     */
    public function ensureMetadata(?ScanDatasetMetadataReader $reader = null): void
    {
        if (! $this->path) {
            return;
        }

        $existing = is_array($this->metadata) ? $this->metadata : [];
        if (isset($existing['format'])) {
            return;
        }

        if (! in_array($this->status, [
            MediaUploadStatus::Scanning,
            MediaUploadStatus::Complete,
        ], true)) {
            return;
        }

        if (! Storage::exists($this->path)) {
            return;
        }

        $reader ??= app(ScanDatasetMetadataReader::class);
        $metadata = $reader->readFromPath(Storage::path($this->path), $this->original_filename);

        if ($metadata === null) {
            return;
        }

        $merged = $this->jsonSafeMetadata($metadata);
        if (isset($existing['user_upload']) && is_array($existing['user_upload'])) {
            $merged['user_upload'] = $existing['user_upload'];
        }

        $this->metadata = $merged;
        static::withoutLocking(fn () => $this->save());
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    public function jsonSafeMetadata(array $metadata): array
    {
        $encoded = json_encode(
            $metadata,
            JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR
        );

        if ($encoded === false) {
            return ['format' => $metadata['format'] ?? 'unknown'];
        }

        $decoded = json_decode($encoded, true);

        return is_array($decoded) ? $decoded : ['format' => $metadata['format'] ?? 'unknown'];
    }

    public function nextChunkIndex(): int
    {
        if (! $this->chunk_size || ! $this->size) {
            return 0;
        }

        $totalChunks = (int) ceil($this->size / $this->chunk_size);

        if ($this->bytes_received >= $this->size) {
            return $totalChunks;
        }

        return (int) floor($this->bytes_received / $this->chunk_size);
    }

    public function partPath(): string
    {
        if (str_ends_with($this->path, '.part')) {
            return $this->path;
        }

        return $this->path.'.part';
    }

    public function finalPath(): string
    {
        if (str_ends_with($this->path, '.part')) {
            return substr($this->path, 0, -5);
        }

        return $this->path;
    }

    public function deleteStoredFiles(): void
    {
        $paths = array_unique([
            $this->path,
            $this->partPath(),
            $this->finalPath(),
        ]);

        foreach ($paths as $path) {
            if ($path && Storage::exists($path)) {
                Storage::delete($path);
            }
        }

        $dir = dirname($this->path);
        if ($dir && $dir !== '.' && Storage::directoryExists($dir)) {
            $files = Storage::files($dir);
            if (count($files) === 0) {
                Storage::deleteDirectory($dir);
            }
        }
    }
}
