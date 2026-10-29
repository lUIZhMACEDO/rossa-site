<?php

namespace App\Jobs;

use App\Enums\MediaUploadStatus;
use App\Models\MediaUpload;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class ScanMediaUpload implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 1440;

    public bool $failOnTimeout = true;

    public function __construct(public MediaUpload $media)
    {
        $this->onQueue('malware-scans');
    }

    public function handle(): void
    {
        $this->media->refresh();

        if ($this->media->status !== MediaUploadStatus::Scanning) {
            return;
        }

        try {
            $this->media->ensureMetadata();
        } catch (Throwable $e) {
            Log::warning('Failed to persist scan dataset metadata before malware scan.', [
                'media_upload_id' => $this->media->id,
                'error' => $e->getMessage(),
            ]);
        }

        $path = Storage::path($this->media->path);
        if (! is_file($path) || ! is_readable($path)) {
            throw new RuntimeException('The quarantined upload is missing or unreadable.');
        }

        $fileSize = filesize($path);
        if ($fileSize === false || $fileSize !== $this->media->size) {
            throw new RuntimeException('The quarantined upload size changed before scanning.');
        }

        $windowSize = (int) config('media_upload.malware_scan.window_size');
        $overlap = (int) config('media_upload.malware_scan.overlap');
        $step = $windowSize - $overlap;
        $maxFileSize = (int) config('media_upload.max_file_size');

        if ($windowSize < 1 || $windowSize > $maxFileSize || $overlap < 0 || $step < 1) {
            throw new RuntimeException('The malware scan window and overlap configuration is invalid.');
        }

        // Files that fit in one ClamAV window: scan the original path once (no temp copy).
        if ($fileSize <= $windowSize) {
            $result = $this->runClamScan($path);

            if ($result['status'] !== 'clean') {
                $this->markFailed($result['status'], "Window 1: {$result['message']}");

                return;
            }

            $this->markPassed(1);

            return;
        }

        $source = fopen($path, 'rb');
        if ($source === false) {
            throw new RuntimeException('Unable to open the quarantined upload for scanning.');
        }

        $windowsScanned = 0;

        try {
            for ($offset = 0; $offset < $fileSize; $offset += $step) {
                $length = min($windowSize, $fileSize - $offset);
                $windowsScanned++;
                $result = $this->scanWindow($source, $path, $offset, $length);

                if ($result['status'] !== 'clean') {
                    $this->markFailed($result['status'], "Window {$windowsScanned}: {$result['message']}");

                    return;
                }

                if ($offset + $length >= $fileSize) {
                    break;
                }
            }
        } finally {
            fclose($source);
        }

        $this->markPassed($windowsScanned);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Media upload malware scan failed.', [
            'media_upload_id' => $this->media->id,
            'error' => $exception?->getMessage(),
        ]);

        $media = MediaUpload::find($this->media->id);
        if ($media && $media->status === MediaUploadStatus::Scanning) {
            $this->quarantineFile($media);
            $media->status = MediaUploadStatus::Failed;
            $media->malware_scan_status = 'error';
            $media->malware_scan_message = 'The malware scan could not be completed.';
            $media->malware_scanned_at = now();
            $this->saveMedia($media);
        }
    }

    protected function scanWindow($source, string $sourcePath, int $offset, int $length): array
    {
        $temporaryPath = tempnam(dirname($sourcePath), '.clamav-window-');
        if ($temporaryPath === false) {
            throw new RuntimeException('Unable to create a temporary malware scan window.');
        }

        try {
            $temporary = fopen($temporaryPath, 'wb');
            if ($temporary === false) {
                throw new RuntimeException('Unable to open a temporary malware scan window.');
            }

            try {
                if (fseek($source, $offset) !== 0) {
                    throw new RuntimeException('Unable to seek within the quarantined upload.');
                }

                $copied = stream_copy_to_stream($source, $temporary, $length);
                if ($copied !== $length) {
                    throw new RuntimeException('Unable to prepare the complete malware scan window.');
                }
            } finally {
                fclose($temporary);
            }

            return $this->runClamScan($temporaryPath);
        } finally {
            if (is_file($temporaryPath)) {
                // nosemgrep: php.lang.security.unlink-use.unlink-use -- path comes from tempnam() above, not user input
                unlink($temporaryPath);
            }
        }
    }

    protected function runClamScan(string $scanPath): array
    {
        $process = new Process([
            (string) config('media_upload.malware_scan.binary'),
            '--fdpass',
            '--no-summary',
            $scanPath,
        ]);
        $process->setTimeout((int) config('media_upload.malware_scan.process_timeout'));
        $process->run();

        $output = trim($process->getOutput()."\n".$process->getErrorOutput());

        if ($process->getExitCode() === 0) {
            return ['status' => 'clean', 'message' => 'Clean'];
        }

        if (str_contains($output, 'Heuristics.Limits.Exceeded')) {
            return ['status' => 'error', 'message' => 'ClamAV exceeded a scan limit.'];
        }

        if ($process->getExitCode() === 1) {
            return ['status' => 'infected', 'message' => 'ClamAV detected malicious content.'];
        }

        Log::error('ClamAV returned an error while scanning an upload window.', [
            'media_upload_id' => $this->media->id,
            'exit_code' => $process->getExitCode(),
            'output' => $output,
        ]);

        return ['status' => 'error', 'message' => 'ClamAV could not scan this window.'];
    }

    protected function markPassed(int $windowsScanned): void
    {
        $this->media->status = MediaUploadStatus::Complete;
        $this->media->malware_scan_status = 'passed';
        $this->media->malware_scan_message = "Scan passed ({$windowsScanned} windows).";
        $this->media->malware_scanned_at = now();
        $this->saveMedia($this->media);
    }

    protected function markFailed(string $scanStatus, string $message): void
    {
        $this->quarantineFile($this->media);
        $this->media->status = MediaUploadStatus::Failed;
        $this->media->malware_scan_status = $scanStatus;
        $this->media->malware_scan_message = $message;
        $this->media->malware_scanned_at = now();
        $this->saveMedia($this->media);
    }

    protected function quarantineFile(MediaUpload $media): void
    {
        if (! $media->path || ! Storage::exists($media->path)) {
            return;
        }

        $destination = "media_uploads/quarantine/{$media->study_id}/{$media->upload_uuid}/".basename($media->path);

        Storage::move($media->path, $destination);
        $media->path = $destination;
    }

    protected function saveMedia(MediaUpload $media): void
    {
        MediaUpload::withoutLocking(fn () => $media->save());
    }
}
