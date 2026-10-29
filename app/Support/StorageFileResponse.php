<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StorageFileResponse
{
    /** @see ZipStream File::CHUNKED_READ_BLOCK_SIZE (16 MiB) — fewer syscalls, still low memory */
    private const CHUNK_SIZE = 16 * 1024 * 1024;

    public static function download(string $path, string $filename, ?string $mimeType = null): StreamedResponse
    {
        abort_unless(Storage::exists($path), 404);

        $mimeType ??= Storage::mimeType($path) ?: 'application/octet-stream';

        $headers = [
            'Content-Type' => $mimeType,
            'X-Accel-Buffering' => 'no',
        ];

        try {
            $headers['Content-Length'] = (string) Storage::size($path);
        } catch (\Throwable) {
            // Some disks may not expose size; omit Content-Length.
        }

        return response()->streamDownload(function () use ($path) {
            $stream = Storage::readStream($path);
            if (! is_resource($stream)) {
                abort(500);
            }

            try {
                while (! feof($stream)) {
                    $chunk = fread($stream, self::CHUNK_SIZE);
                    if ($chunk === false || $chunk === '') {
                        break;
                    }
                    echo $chunk;
                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                }
            } finally {
                fclose($stream);
            }
        }, $filename, $headers);
    }
}
