<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Reads Scanco AIM file header metadata (AIMDATA V020 / V030).
 *
 * Ported from the vtkScancoCTReader layout used by ITKIOScanco / vtk-dicom:
 * preheader integer sizes → image structure (dims, element spacing) → processing log.
 */
class AimMetadataReader
{
    /**
     * @return array<string, mixed>|null
     */
    public function readFromPath(string $absolutePath): ?array
    {
        if (! is_file($absolutePath) || ! is_readable($absolutePath)) {
            return null;
        }

        try {
            $handle = fopen($absolutePath, 'rb');
            if ($handle === false) {
                return null;
            }

            try {
                return $this->parseHandle($handle);
            } finally {
                fclose($handle);
            }
        } catch (Throwable $e) {
            Log::warning('Failed to read AIM metadata.', [
                'path' => $absolutePath,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @param  resource  $handle
     * @return array<string, mixed>|null
     */
    private function parseHandle($handle): ?array
    {
        $probe = fread($handle, 160);
        if ($probe === false || strlen($probe) < 20) {
            return null;
        }

        $offset = 0;
        $intSize = 4;
        $version = 'AIMDATA_V020';

        $magic = substr($probe, 0, 16);
        if (str_starts_with($magic, 'AIMDATA_V030')) {
            $version = rtrim($magic);
            $intSize = 8;
            $offset = 16;
        } elseif (str_starts_with($magic, 'AIMDATA_')) {
            $version = rtrim($magic);
            $offset = 16;
            $intSize = 4;
        }
        // V020 files often omit the 16-byte id and start with preheader ints.

        $preheaderSize = $this->readInt($probe, $offset, $intSize);
        $structSize = $this->readInt($probe, $offset + $intSize, $intSize);
        $logSize = $this->readInt($probe, $offset + 2 * $intSize, $intSize);

        if ($preheaderSize < 0 || $structSize < 20 || $logSize < 0) {
            return null;
        }

        // Cap pathological sizes (AIM headers are typically well under a few MB).
        $maxSection = 16 * 1024 * 1024;
        if ($preheaderSize > $maxSection || $structSize > $maxSection || $logSize > $maxSection) {
            return null;
        }

        $headerSize = $offset + $preheaderSize + $structSize + $logSize;
        if ($headerSize < strlen($probe)) {
            return null;
        }

        if (fseek($handle, 0) !== 0) {
            return null;
        }

        $data = fread($handle, $headerSize);
        if ($data === false || strlen($data) < $headerSize) {
            return null;
        }

        $structStart = $offset + $preheaderSize;
        $h = $structStart + 20;
        $dataType = $this->readInt($data, $h, 4);
        $h += 4;

        $structValues = [];
        for ($i = 0; $i < 21; $i++) {
            $structValues[$i] = $this->readInt($data, $h, $intSize);
            $h += $intSize;
        }

        $elementSize = [];
        for ($i = 0; $i < 3; $i++) {
            $elementSize[$i] = $this->readFloat($data, $h);
            if ($elementSize[$i] == 0.0) {
                $elementSize[$i] = 1.0;
            }
            $h += 4;
        }

        $dimX = $structValues[3] ?? 0;
        $dimY = $structValues[4] ?? 0;
        $dimZ = $structValues[5] ?? 0;

        $logStart = $offset + $preheaderSize + $structSize;
        $logBlob = substr($data, $logStart, $logSize);
        $processingLog = $this->parseProcessingLog($logBlob);

        $metadata = [
            'format' => 'aim',
            'version' => $version,
            'data_type' => $dataType,
            'preheader_size' => $preheaderSize,
            'struct_size' => $structSize,
            'log_size' => $logSize,
            'header_size' => $headerSize,
            'position' => [
                'x' => $structValues[0] ?? null,
                'y' => $structValues[1] ?? null,
                'z' => $structValues[2] ?? null,
            ],
            'dimx_p' => $dimX,
            'dimy_p' => $dimY,
            'dimz_p' => $dimZ,
            'element_size' => [
                'x' => $elementSize[0],
                'y' => $elementSize[1],
                'z' => $elementSize[2],
            ],
            'processing_log' => $processingLog,
        ];

        // Promote well-known processing-log keys when present
        foreach ([
            'Patient Name' => 'patient_name',
            'Index Patient' => 'patient_index',
            'Index Measurement' => 'measurement_index',
            'Scanner ID' => 'scanner_id',
            'Scanner type' => 'scanner_type',
            'Original Creation-Date' => 'creation_date',
            'Time' => 'modification_date',
            'Energy [V]' => 'energy_v',
            'Intensity [uA]' => 'intensity_ua',
            'Mu_Scaling' => 'mu_scaling',
            'Site' => 'site',
        ] as $logKey => $metaKey) {
            if (array_key_exists($logKey, $processingLog)) {
                $metadata[$metaKey] = $processingLog[$logKey];
            }
        }

        return $metadata;
    }

    /**
     * @return array<string, string>
     */
    private function parseProcessingLog(string $log): array
    {
        $result = [];
        $lines = preg_split("/\r\n|\n|\r/", $log) ?: [];

        foreach ($lines as $line) {
            $line = rtrim($line, "\0");
            if ($line === '' || str_starts_with($line, '!')) {
                continue;
            }

            // key and value separated by multiple spaces (vtkScancoCTReader)
            if (! preg_match('/^(\S.*?)\s{2,}(.*)$/', $line, $matches)) {
                continue;
            }

            $key = trim($matches[1]);
            $value = trim($matches[2]);
            if ($key === '') {
                continue;
            }
            $result[$key] = $value;
        }

        return $result;
    }

    private function readInt(string $data, int $offset, int $size): int
    {
        if ($offset < 0 || $offset + $size > strlen($data)) {
            return 0;
        }

        if ($size === 8) {
            $low = unpack('V', substr($data, $offset, 4))[1];
            $high = unpack('V', substr($data, $offset + 4, 4))[1];
            // Signed 64-bit; for typical AIM sizes high is 0.
            if (PHP_INT_SIZE >= 8) {
                return (int) (($high << 32) | $low);
            }

            return (int) $low;
        }

        return unpack('l', substr($data, $offset, 4))[1];
    }

    private function readFloat(string $data, int $offset): float
    {
        if ($offset < 0 || $offset + 4 > strlen($data)) {
            return 0.0;
        }

        return unpack('f', substr($data, $offset, 4))[1];
    }
}
