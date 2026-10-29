<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Lightweight DICOM metadata reader (explicit VR little-endian).
 *
 * Walks tags after the 128-byte preamble + "DICM" marker until Pixel Data
 * (or a byte budget). Sequence / item / binary values are skipped so trailing
 * patient and image tags are still reached.
 */
class DicomMetadataReader
{
    private const PIXEL_DATA_GROUP = 0x7FE0;

    private const PIXEL_DATA_ELEMENT = 0x0010;

    private const ITEM = 0xE000;

    private const ITEM_DELIM = 0xE00D;

    private const SEQ_DELIM = 0xE0DD;

    private const UNDEFINED_LENGTH = 0xFFFFFFFF;

    private const MAX_BYTES = 4 * 1024 * 1024;

    /** Max value length we bother decoding (larger values are skipped). */
    private const MAX_DECODE_LENGTH = 4096;

    /** VRs that use 2 reserved bytes + 32-bit length. */
    private const LONG_VRS = [
        'OB', 'OD', 'OF', 'OL', 'OV', 'OW', 'SQ', 'SV', 'UC', 'UR', 'UT', 'UN', 'UV',
    ];

    /** Binary-ish VRs — store a placeholder, never utf-8 garbage. */
    private const BINARY_VRS = [
        'OB', 'OD', 'OF', 'OL', 'OV', 'OW', 'SQ', 'UN',
    ];

    /** @var array<string, string> group,element => name */
    private const TAG_NAMES = [
        '0002,0000' => 'FileMetaInformationGroupLength',
        '0002,0001' => 'FileMetaInformationVersion',
        '0002,0002' => 'MediaStorageSOPClassUID',
        '0002,0003' => 'MediaStorageSOPInstanceUID',
        '0002,0010' => 'TransferSyntaxUID',
        '0002,0012' => 'ImplementationClassUID',
        '0002,0013' => 'ImplementationVersionName',
        '0008,0005' => 'SpecificCharacterSet',
        '0008,0008' => 'ImageType',
        '0008,0016' => 'SOPClassUID',
        '0008,0018' => 'SOPInstanceUID',
        '0008,0020' => 'StudyDate',
        '0008,0021' => 'SeriesDate',
        '0008,0023' => 'ContentDate',
        '0008,0030' => 'StudyTime',
        '0008,0031' => 'SeriesTime',
        '0008,0050' => 'AccessionNumber',
        '0008,0060' => 'Modality',
        '0008,0070' => 'Manufacturer',
        '0008,0080' => 'InstitutionName',
        '0008,0081' => 'InstitutionAddress',
        '0008,0090' => 'ReferringPhysicianName',
        '0008,1030' => 'StudyDescription',
        '0008,103E' => 'SeriesDescription',
        '0008,1050' => 'PerformingPhysicianName',
        '0008,1090' => 'ManufacturerModelName',
        '0008,2110' => 'LossyImageCompressionRatio',
        '0010,0010' => 'PatientName',
        '0010,0020' => 'PatientID',
        '0010,0030' => 'PatientBirthDate',
        '0010,0040' => 'PatientSex',
        '0018,0015' => 'BodyPartExamined',
        '0018,0050' => 'SliceThickness',
        '0018,0060' => 'KVP',
        '0018,1063' => 'FrameTime',
        '0018,1151' => 'XRayTubeCurrent',
        '0018,5100' => 'PatientPosition',
        '0020,000D' => 'StudyInstanceUID',
        '0020,000E' => 'SeriesInstanceUID',
        '0020,0010' => 'StudyID',
        '0020,0011' => 'SeriesNumber',
        '0020,0013' => 'InstanceNumber',
        '0020,0032' => 'ImagePositionPatient',
        '0020,0037' => 'ImageOrientationPatient',
        '0028,0002' => 'SamplesPerPixel',
        '0028,0004' => 'PhotometricInterpretation',
        '0028,0008' => 'NumberOfFrames',
        '0028,0009' => 'FrameIncrementPointer',
        '0028,0010' => 'Rows',
        '0028,0011' => 'Columns',
        '0028,0030' => 'PixelSpacing',
        '0028,0100' => 'BitsAllocated',
        '0028,0101' => 'BitsStored',
        '0028,0102' => 'HighBit',
        '0028,0103' => 'PixelRepresentation',
        '5000,0020' => 'CurveLabel',
    ];

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
            Log::warning('Failed to read DICOM metadata.', [
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
        $preamble = fread($handle, 132);
        if ($preamble === false || strlen($preamble) < 132) {
            return null;
        }

        if (substr($preamble, 128, 4) !== 'DICM') {
            return null;
        }

        $tags = [];
        $bytesRead = 132;
        $ok = $this->readDataSet($handle, $bytesRead, $tags, null);

        if (! $ok && $tags === []) {
            return null;
        }

        return array_merge(['format' => 'dcm', 'has_dicm_prefix' => true], $tags);
    }

    /**
     * @param  resource  $handle
     * @param  array<string, mixed>  $tags
     * @param  int|null  $stopAt  absolute file offset to stop (inclusive end exclusive), or null
     */
    private function readDataSet($handle, int &$bytesRead, array &$tags, ?int $stopAt): bool
    {
        while ($bytesRead < self::MAX_BYTES) {
            if ($stopAt !== null && $bytesRead >= $stopAt) {
                return true;
            }

            $tagBytes = fread($handle, 4);
            if ($tagBytes === false || strlen($tagBytes) < 4) {
                return true;
            }
            $bytesRead = (int) ($bytesRead + 4);

            $group = unpack('v', substr($tagBytes, 0, 2))[1];
            $element = unpack('v', substr($tagBytes, 2, 2))[1];

            // Pixel data: stop walking (do not read pixels).
            if ($group === self::PIXEL_DATA_GROUP && $element === self::PIXEL_DATA_ELEMENT) {
                return true;
            }

            // Item / delimeter tags have no VR — only a 32-bit length.
            if ($group === 0xFFFE) {
                $lenBytes = fread($handle, 4);
                if ($lenBytes === false || strlen($lenBytes) < 4) {
                    return false;
                }
                $bytesRead = (int) ($bytesRead + 4);
                $length = unpack('V', $lenBytes)[1];

                // Delimiters should only appear inside undefined SQ/items; ignore at top level.
                if ($element === self::SEQ_DELIM || $element === self::ITEM_DELIM) {
                    continue;
                }

                if ($element === self::ITEM) {
                    if ($length === self::UNDEFINED_LENGTH) {
                        $nested = [];
                        if (! $this->readDataSetUntilItemDelim($handle, $bytesRead, $nested)) {
                            return false;
                        }
                    } elseif ($length > 0) {
                        if (! $this->skipBytes($handle, $bytesRead, $length)) {
                            return false;
                        }
                    }

                    continue;
                }

                // Unknown FFFE tag — try to skip defined length.
                if ($length !== self::UNDEFINED_LENGTH && $length > 0) {
                    if (! $this->skipBytes($handle, $bytesRead, $length)) {
                        return false;
                    }
                }

                continue;
            }

            $vrBytes = fread($handle, 2);
            if ($vrBytes === false || strlen($vrBytes) < 2) {
                return false;
            }
            $bytesRead = (int) ($bytesRead + 2);
            $vr = $vrBytes;

            // If VR looks invalid, back out can't recover easily — stop cleanly.
            if (! preg_match('/^[A-Z]{2}$/', $vr)) {
                return true;
            }

            if (in_array($vr, self::LONG_VRS, true)) {
                $extra = fread($handle, 6);
                if ($extra === false || strlen($extra) < 6) {
                    return false;
                }
                $bytesRead = (int) ($bytesRead + 6);
                $length = unpack('V', substr($extra, 2, 4))[1];
            } else {
                $lenBytes = fread($handle, 2);
                if ($lenBytes === false || strlen($lenBytes) < 2) {
                    return false;
                }
                $bytesRead = (int) ($bytesRead + 2);
                $length = unpack('v', $lenBytes)[1];
            }

            if ($length === self::UNDEFINED_LENGTH) {
                if ($vr === 'SQ') {
                    if (! $this->skipUndefinedSequence($handle, $bytesRead, $tags)) {
                        return false;
                    }

                    $this->storePlaceholder($tags, $group, $element, $vr, null);

                    continue;
                }

                // Encapsulated OB/OW etc. (including pixel fragments if we reached them).
                if (! $this->skipUndefinedEncapsulated($handle, $bytesRead)) {
                    return false;
                }

                $this->storePlaceholder($tags, $group, $element, $vr, null);

                continue;
            }

            if ($length < 0) {
                return false;
            }

            // Cap runaway lengths without aborting the whole walk.
            if ($length > self::MAX_BYTES) {
                return true;
            }

            if ($length === 0) {
                continue;
            }

            if (in_array($vr, self::BINARY_VRS, true) || $length > self::MAX_DECODE_LENGTH) {
                if (! $this->skipBytes($handle, $bytesRead, $length)) {
                    return false;
                }
                $this->storePlaceholder($tags, $group, $element, $vr, $length);

                continue;
            }

            $valueBytes = fread($handle, $length);
            if ($valueBytes === false || strlen($valueBytes) < $length) {
                return false;
            }
            $bytesRead = (int) ($bytesRead + $length);

            $decoded = $this->decodeValue($vr, $valueBytes);
            if ($decoded === null || $decoded === '') {
                continue;
            }

            $tagKey = sprintf('%04X,%04X', $group, $element);
            $name = self::TAG_NAMES[$tagKey] ?? $tagKey;
            $tags[$name] = $decoded;
        }

        return true;
    }

    /**
     * Undefined-length sequence: items until (FFFE,E0DD).
     *
     * @param  resource  $handle
     * @param  array<string, mixed>  $tags
     */
    private function skipUndefinedSequence($handle, int &$bytesRead, array &$tags): bool
    {
        while ($bytesRead < self::MAX_BYTES) {
            $tagBytes = fread($handle, 4);
            if ($tagBytes === false || strlen($tagBytes) < 4) {
                return false;
            }
            $bytesRead = (int) ($bytesRead + 4);

            $group = unpack('v', substr($tagBytes, 0, 2))[1];
            $element = unpack('v', substr($tagBytes, 2, 2))[1];

            $lenBytes = fread($handle, 4);
            if ($lenBytes === false || strlen($lenBytes) < 4) {
                return false;
            }
            $bytesRead = (int) ($bytesRead + 4);
            $length = unpack('V', $lenBytes)[1];

            if ($group === 0xFFFE && $element === self::SEQ_DELIM) {
                return true;
            }

            if ($group === 0xFFFE && $element === self::ITEM) {
                if ($length === self::UNDEFINED_LENGTH) {
                    // Undefined item: nested dataset until item delim; ignore nested tags for metadata.
                    $discard = [];
                    if (! $this->readDataSetUntilItemDelim($handle, $bytesRead, $discard)) {
                        return false;
                    }
                } elseif ($length > 0) {
                    if (! $this->skipBytes($handle, $bytesRead, $length)) {
                        return false;
                    }
                }

                continue;
            }

            // Unexpected tag inside undefined SQ — try explicit length skip if possible.
            if ($length !== self::UNDEFINED_LENGTH && $length > 0 && $length < self::MAX_BYTES) {
                if (! $this->skipBytes($handle, $bytesRead, $length)) {
                    return false;
                }

                continue;
            }

            return false;
        }

        return false;
    }

    /**
     * Undefined-length item content until (FFFE,E00D). Nested tags are not collected.
     *
     * @param  resource  $handle
     * @param  array<string, mixed>  $tags
     */
    private function readDataSetUntilItemDelim($handle, int &$bytesRead, array &$tags): bool
    {
        while ($bytesRead < self::MAX_BYTES) {
            $tagBytes = fread($handle, 4);
            if ($tagBytes === false || strlen($tagBytes) < 4) {
                return false;
            }

            $group = unpack('v', substr($tagBytes, 0, 2))[1];
            $element = unpack('v', substr($tagBytes, 2, 2))[1];

            if ($group === 0xFFFE && $element === self::ITEM_DELIM) {
                $bytesRead = (int) ($bytesRead + 4);
                $lenBytes = fread($handle, 4);
                if ($lenBytes === false || strlen($lenBytes) < 4) {
                    return false;
                }
                $bytesRead = (int) ($bytesRead + 4);

                return true;
            }

            // Rewind the 4 tag bytes into a re-parse path: unread is hard in PHP streams,
            // so parse the rest of this element here instead of rewinding.
            $bytesRead = (int) ($bytesRead + 4);

            if ($group === 0xFFFE) {
                $lenBytes = fread($handle, 4);
                if ($lenBytes === false || strlen($lenBytes) < 4) {
                    return false;
                }
                $bytesRead = (int) ($bytesRead + 4);
                $length = unpack('V', $lenBytes)[1];
                if ($length !== self::UNDEFINED_LENGTH && $length > 0) {
                    if (! $this->skipBytes($handle, $bytesRead, $length)) {
                        return false;
                    }
                }

                continue;
            }

            $vrBytes = fread($handle, 2);
            if ($vrBytes === false || strlen($vrBytes) < 2) {
                return false;
            }
            $bytesRead = (int) ($bytesRead + 2);
            $vr = $vrBytes;

            if (! preg_match('/^[A-Z]{2}$/', $vr)) {
                return false;
            }

            if (in_array($vr, self::LONG_VRS, true)) {
                $extra = fread($handle, 6);
                if ($extra === false || strlen($extra) < 6) {
                    return false;
                }
                $bytesRead = (int) ($bytesRead + 6);
                $length = unpack('V', substr($extra, 2, 4))[1];
            } else {
                $lenBytes = fread($handle, 2);
                if ($lenBytes === false || strlen($lenBytes) < 2) {
                    return false;
                }
                $bytesRead = (int) ($bytesRead + 2);
                $length = unpack('v', $lenBytes)[1];
            }

            if ($length === self::UNDEFINED_LENGTH) {
                if ($vr === 'SQ') {
                    $discard = [];
                    if (! $this->skipUndefinedSequence($handle, $bytesRead, $discard)) {
                        return false;
                    }

                    continue;
                }
                if (! $this->skipUndefinedEncapsulated($handle, $bytesRead)) {
                    return false;
                }

                continue;
            }

            if ($length > 0) {
                if (! $this->skipBytes($handle, $bytesRead, $length)) {
                    return false;
                }
            }
        }

        return false;
    }

    /**
     * Encapsulated multi-fragment OB/OW (undefined length): items until sequence delim.
     *
     * @param  resource  $handle
     */
    private function skipUndefinedEncapsulated($handle, int &$bytesRead): bool
    {
        // Same structure as undefined SQ (basic offset table + fragments as items).
        $discard = [];

        return $this->skipUndefinedSequence($handle, $bytesRead, $discard);
    }

    /**
     * @param  resource  $handle
     */
    private function skipBytes($handle, int &$bytesRead, int $length): bool
    {
        if ($length <= 0) {
            return true;
        }

        if (fseek($handle, $length, SEEK_CUR) === 0) {
            $bytesRead = (int) ($bytesRead + $length);

            return true;
        }

        $remaining = $length;
        while ($remaining > 0) {
            $chunk = min(1024 * 1024, $remaining);
            $data = fread($handle, $chunk);
            if ($data === false || strlen($data) === 0) {
                return false;
            }
            $got = strlen($data);
            $bytesRead = (int) ($bytesRead + $got);
            $remaining -= $got;
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $tags
     */
    private function storePlaceholder(array &$tags, int $group, int $element, string $vr, ?int $length): void
    {
        // Keep interesting non-binary SQ notes short; skip pure private binary noise by default.
        if (in_array($vr, ['OB', 'OW', 'OF', 'OD', 'OL', 'OV', 'UN'], true)) {
            return;
        }

        if ($vr === 'SQ') {
            $tagKey = sprintf('%04X,%04X', $group, $element);
            $name = self::TAG_NAMES[$tagKey] ?? $tagKey;
            $tags[$name] = $length === null
                ? '[sequence]'
                : "[sequence, {$length} bytes]";
        }
    }

    private function decodeValue(string $vr, string $bytes): mixed
    {
        $bytes = rtrim($bytes, "\0");

        return match ($vr) {
            'US' => match (true) {
                strlen($bytes) >= 2 => unpack('v', substr($bytes, 0, 2))[1],
                strlen($bytes) === 1 => ord($bytes[0]),
                default => null,
            },
            'SS' => match (true) {
                strlen($bytes) >= 2 => unpack('s', substr($bytes, 0, 2))[1],
                strlen($bytes) === 1 => unpack('c', $bytes)[1],
                default => null,
            },
            'UL' => strlen($bytes) >= 4 ? unpack('V', substr($bytes, 0, 4))[1] : null,
            'SL' => strlen($bytes) >= 4 ? unpack('l', substr($bytes, 0, 4))[1] : null,
            'FL' => strlen($bytes) >= 4 ? unpack('f', substr($bytes, 0, 4))[1] : null,
            'FD' => strlen($bytes) >= 8 ? unpack('d', substr($bytes, 0, 8))[1] : null,
            'AT' => strlen($bytes) >= 4
                ? sprintf('%04X,%04X', unpack('v', substr($bytes, 0, 2))[1], unpack('v', substr($bytes, 2, 2))[1])
                : null,
            'OB', 'OW', 'OF', 'OD', 'OL', 'OV', 'UN', 'SQ' => null,
            default => trim(str_replace("\0", '', $bytes)),
        };
    }
}
