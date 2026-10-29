<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Facade that routes scan-dataset files to format-specific metadata readers.
 */
class ScanDatasetMetadataReader
{
    public function __construct(
        protected IsqMetadataReader $isq,
        protected AimMetadataReader $aim,
        protected DicomMetadataReader $dicom,
    ) {}

    /**
     * @return array<string, mixed>|null
     */
    public function readFromPath(string $absolutePath, ?string $originalFilename = null): ?array
    {
        if (! is_file($absolutePath) || ! is_readable($absolutePath)) {
            return null;
        }

        try {
            $extension = strtolower(pathinfo($originalFilename ?? $absolutePath, PATHINFO_EXTENSION));
            $magic = $this->readMagic($absolutePath, 16);
            if ($magic === null) {
                return null;
            }

            if ($magic === IsqMetadataReader::HEADER_ID || $extension === 'isq') {
                return $this->isq->readFromPath($absolutePath);
            }

            if (str_starts_with($magic, 'AIMDATA_V030') || $extension === 'aim') {
                return $this->aim->readFromPath($absolutePath);
            }

            if ($extension === 'dcm' || $this->looksLikeDicom($absolutePath)) {
                return $this->dicom->readFromPath($absolutePath);
            }

            // Fallbacks by probing remaining readers (magic-driven)
            if ($meta = $this->aim->readFromPath($absolutePath)) {
                return $meta;
            }
            if ($meta = $this->dicom->readFromPath($absolutePath)) {
                return $meta;
            }

            return null;
        } catch (Throwable $e) {
            Log::warning('Failed to read scan dataset metadata.', [
                'path' => $absolutePath,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function readMagic(string $path, int $length): ?string
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return null;
        }

        try {
            $data = fread($handle, $length);

            return $data === false ? null : $data;
        } finally {
            fclose($handle);
        }
    }

    private function looksLikeDicom(string $path): bool
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return false;
        }

        try {
            if (fseek($handle, 128) !== 0) {
                return false;
            }
            $prefix = fread($handle, 4);

            return $prefix === 'DICM';
        } finally {
            fclose($handle);
        }
    }
}
