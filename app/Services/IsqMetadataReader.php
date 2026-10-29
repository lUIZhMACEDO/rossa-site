<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Reads Scanco µCT ISQ file header metadata (CTDATA-HEADER_V1).
 *
 * Header layout from Scanco / SCIFIO documentation (little-endian):
 *
 *  0  char check[16]           // "CTDATA-HEADER_V1"
 * 16  int data_type
 * 20  int nr_of_bytes
 * 24  int nr_of_blocks
 * 28  int patient_index
 * 32  int scanner_id
 * 36  int creation_date[2]     // VMS timestamp
 * 44  int dimx_p … dimz_p
 * 56  int dimx_µm … dimz_µm
 * …
 * 128 char name[40]
 * 168 int energy_V
 * 172 int intensity_µA
 * 508 int optional_header_blocks
 */
class IsqMetadataReader
{
    public const HEADER_ID = 'CTDATA-HEADER_V1';

    public const HEADER_BLOCK = 512;

    /** VMS epoch ticks (100 ns) between 1858-11-17 and 1970-01-01. */
    private const VMS_UNIX_EPOCH = 0x007C95674BEB4000;

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
                $header = fread($handle, self::HEADER_BLOCK);
            } finally {
                fclose($handle);
            }

            if ($header === false || strlen($header) < self::HEADER_BLOCK) {
                return null;
            }

            return $this->parseHeader($header);
        } catch (Throwable $e) {
            Log::warning('Failed to read ISQ metadata.', [
                'path' => $absolutePath,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public function parseHeader(string $header): ?array
    {
        if (strlen($header) < self::HEADER_BLOCK) {
            return null;
        }

        $check = substr($header, 0, 16);
        if ($check !== self::HEADER_ID) {
            return null;
        }

        $i = fn (int $offset): int => unpack('l', substr($header, $offset, 4))[1];

        $width = $i(44);
        $height = $i(48);
        $slices = $i(52);
        $physicalWidth = $i(56);
        $physicalHeight = $i(60);
        $physicalDepth = $i(64);

        $optionalBlocks = $i(508);
        $dataOffset = ($optionalBlocks + 1) * self::HEADER_BLOCK;

        $patientName = trim(rtrim(substr($header, 128, 40), "\0"));

        $metadata = [
            'format' => 'isq',
            'check' => $check,
            'data_type' => $i(16),
            'nr_of_bytes' => $i(20),
            'nr_of_blocks' => $i(24),
            'patient_index' => $i(28),
            'scanner_id' => $i(32),
            'creation_date_vms' => $this->readUint64Le($header, 36),
            'creation_date' => $this->vmsTimestampToIso($this->readUint64Le($header, 36)),
            'dimx_p' => $width,
            'dimy_p' => $height,
            'dimz_p' => $slices,
            'dimx_um' => $physicalWidth,
            'dimy_um' => $physicalHeight,
            'dimz_um' => $physicalDepth,
            'slice_thickness_um' => $i(68),
            'slice_increment_um' => $i(72),
            'slice_1_pos_um' => $i(76),
            'min_data_value' => $i(80),
            'max_data_value' => $i(84),
            'mu_scaling' => $i(88),
            'nr_of_samples' => $i(92),
            'nr_of_projections' => $i(96),
            'scandist_um' => $i(100),
            'scanner_type' => $i(104),
            'sampletime_us' => $i(108),
            'index_measurement' => $i(112),
            'site' => $i(116),
            'reference_line_um' => $i(120),
            'recon_alg' => $i(124),
            'name' => $patientName,
            'energy_v' => $i(168),
            'intensity_ua' => $i(172),
            'optional_header_blocks' => $optionalBlocks,
            'data_offset' => $dataOffset,
        ];

        if ($width > 0 && $height > 0 && $slices > 0) {
            $metadata['voxel_size_um'] = [
                'x' => $physicalWidth / $width,
                'y' => $physicalHeight / $height,
                'z' => $physicalDepth / $slices,
            ];
        }

        return $metadata;
    }

    private function readUint64Le(string $data, int $offset): string
    {
        // Return as string so JSON keeps full precision for 64-bit values.
        $low = unpack('V', substr($data, $offset, 4))[1];
        $high = unpack('V', substr($data, $offset + 4, 4))[1];

        if (PHP_INT_SIZE >= 8) {
            return (string) (($high << 32) | $low);
        }

        // Fallback string math for 32-bit PHP (unlikely in this env).
        return bcadd(bcmul((string) $high, '4294967296'), (string) $low);
    }

    private function vmsTimestampToIso(string $vmsTicks): ?string
    {
        try {
            if (! is_numeric($vmsTicks) || bccomp($vmsTicks, '0') <= 0) {
                return null;
            }

            $delta = bcsub($vmsTicks, (string) self::VMS_UNIX_EPOCH);
            $seconds = bcdiv($delta, '10000000', 0);
            $timestamp = (int) $seconds;
            if ($timestamp <= 0) {
                return null;
            }

            return gmdate('c', $timestamp);
        } catch (Throwable) {
            return null;
        }
    }
}
