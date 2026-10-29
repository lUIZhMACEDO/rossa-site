<?php

namespace App\Support;

use App\Models\MediaUpload;
use App\Models\Study;

class MediaUploadTable
{
    public static function longBoneExtents(): array
    {
        return [
            'whole_bone' => 'Whole bone',
            'proximal_region' => 'Proximal region',
            'distal_region' => 'Distal region',
            'mid_diaphysis' => 'Mid-diaphysis',
            'cortical_only' => 'Cortical only',
            'trabecular_only' => 'Trabecular only',
        ];
    }

    public static function vertebraExtents(): array
    {
        return [
            'whole_vertebra' => 'Whole vertebra',
            'vertebral_body_only' => 'Vertebral body only',
            'cranial_region' => 'Cranial region',
            'middle_region' => 'Middle region',
            'caudal_region' => 'Caudal region',
        ];
    }

    public static function processingLevels(): array
    {
        return [
            'original' => 'Original, unprocessed scan volume',
            'cropped_roi' => 'Cropped region of interest',
            'segmented' => 'Segmented/processed volume',
        ];
    }

    public static function boneTypes(): array
    {
        return [
            'femur' => 'Femur',
            'tibia' => 'Tibia',
            'vertebra' => 'Vertebra',
            'humerus' => 'Humerus',
            'radius' => 'Radius',
            'ulna' => 'Ulna',
            'other' => 'Other',
        ];
    }

    public static function spinalRegions(): array
    {
        return [
            'cervical' => 'Cervical',
            'thoracic' => 'Thoracic',
            'lumbar' => 'Lumbar',
        ];
    }

    public static function uploadLabels(): array
    {
        $longBoneExtents = self::longBoneExtents();
        $vertebraExtents = self::vertebraExtents();

        return [
            'processing_level' => self::processingLevels(),
            'bone_type' => self::boneTypes(),
            'spinal_region' => self::spinalRegions(),
            'long_bone_scan_extent' => array_merge($longBoneExtents, ['other' => 'Other']),
            'vertebra_scan_extent' => array_merge($vertebraExtents, ['other' => 'Other']),
        ];
    }

    public static function formatUserUpload(?array $userUpload, string $field): string
    {
        if (! $userUpload) {
            return '—';
        }

        $processingLevels = self::processingLevels();
        $boneTypes = self::boneTypes();
        $spinalRegions = self::spinalRegions();
        $scanExtents = array_merge(self::longBoneExtents(), self::vertebraExtents(), ['other' => 'Other']);

        $value = $userUpload[$field] ?? null;
        if (! $value) {
            return '—';
        }

        if ($field === 'processing_level') {
            return $processingLevels[$value] ?? $value;
        }

        if ($field === 'bone_type') {
            if ($value === 'other') {
                return $userUpload['bone_type_other'] ?? 'Other';
            }

            $label = $boneTypes[$value] ?? $value;
            if ($value === 'vertebra' && ! empty($userUpload['spinal_region'])) {
                $region = $spinalRegions[$userUpload['spinal_region']] ?? $userUpload['spinal_region'];
                $levels = $userUpload['vertebral_levels'] ?? '';
                if ($levels) {
                    return "{$label} ({$region}, {$levels})";
                }
            }

            return $label;
        }

        if ($field === 'scan_extent') {
            if ($value === 'other') {
                return $userUpload['scan_extent_other'] ?? 'Other';
            }

            return $scanExtents[$value] ?? $value;
        }

        return $value;
    }

    public static function rowViewData(Study $study, MediaUpload $media): array
    {
        $scanEditable = ! $study->isLockedForEditing(auth()->user())
            && ! $study->isPhenotypeAnalysisLocked(auth()->user());

        return [
            'study' => $study,
            'media' => $media,
            'sampleGroups' => $study->sampleGroups()->orderBy('label')->get(['id', 'label']),
            'scanEditable' => $scanEditable,
            'processingLevels' => self::processingLevels(),
            'boneTypes' => self::boneTypes(),
            'spinalRegions' => self::spinalRegions(),
            'longBoneExtents' => self::longBoneExtents(),
            'vertebraExtents' => self::vertebraExtents(),
            'formatUserUpload' => fn (?array $userUpload, string $field): string => self::formatUserUpload($userUpload, $field),
        ];
    }
}
