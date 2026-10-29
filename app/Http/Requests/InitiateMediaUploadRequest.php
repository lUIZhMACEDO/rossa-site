<?php

namespace App\Http\Requests;

use App\Models\Study;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class InitiateMediaUploadRequest extends FormRequest
{
    public const BONE_TYPES = [
        'femur',
        'tibia',
        'vertebra',
        'humerus',
        'radius',
        'ulna',
        'other',
    ];

    public const PROCESSING_LEVELS = [
        'original',
        'cropped_roi',
        'segmented',
    ];

    public const SPINAL_REGIONS = [
        'cervical',
        'thoracic',
        'lumbar',
    ];

    public const LONG_BONE_SCAN_EXTENTS = [
        'whole_bone',
        'proximal_region',
        'distal_region',
        'mid_diaphysis',
        'cortical_only',
        'trabecular_only',
        'other',
    ];

    public const VERTEBRA_SCAN_EXTENTS = [
        'whole_vertebra',
        'vertebral_body_only',
        'cranial_region',
        'middle_region',
        'caudal_region',
        'other',
    ];

    public const VERTEBRAL_LEVEL_MAX = [
        'cervical' => 7,
        'thoracic' => 12,
        'lumbar' => 6,
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Study $study */
        $study = $this->route('study');

        return [
            'original_filename' => ['required', 'string', 'max:255'],
            'size' => ['required', 'integer', 'min:1', 'max:'.config('media_upload.max_file_size')],
            'mime_type' => ['nullable', 'string', 'max:255'],
            'sample_group_id' => [
                'required',
                'integer',
                Rule::exists('sample_groups', 'id')->where(fn ($query) => $query->where('study_id', $study->id)),
            ],
            'user_upload' => ['required', 'array'],
            'user_upload.kind' => ['required', 'string', Rule::in(['raw_micro_ct'])],
            'user_upload.processing_level' => ['required', 'string', Rule::in(self::PROCESSING_LEVELS)],
            'user_upload.bone_type' => ['required', 'string', Rule::in(self::BONE_TYPES)],
            'user_upload.bone_type_other' => ['nullable', 'string', 'max:100'],
            'user_upload.spinal_region' => ['nullable', 'string', Rule::in(self::SPINAL_REGIONS)],
            'user_upload.vertebral_levels' => ['nullable', 'string', 'max:50'],
            'user_upload.scan_extent' => ['required', 'string'],
            'user_upload.scan_extent_other' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var Study $study */
            $study = $this->route('study');

            $filename = (string) $this->input('original_filename');
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $allowed = config('media_upload.allowed_extensions', ['isq', 'dcm', 'aim', 'zip']);

            if ($extension === '' || ! in_array($extension, $allowed, true)) {
                $validator->errors()->add(
                    'original_filename',
                    'File must be one of: .'.implode(', .', $allowed)
                );
            }

            $size = (int) $this->input('size');
            if ($size > 0 && ! $study->canStoreBytes($size)) {
                $validator->errors()->add(
                    'size',
                    'This study has reached the storage limit.'
                );
            }

            $boneType = (string) $this->input('user_upload.bone_type');
            $scanExtent = (string) $this->input('user_upload.scan_extent');
            $allowedExtents = $boneType === 'vertebra'
                ? self::VERTEBRA_SCAN_EXTENTS
                : self::LONG_BONE_SCAN_EXTENTS;

            if ($scanExtent !== '' && ! in_array($scanExtent, $allowedExtents, true)) {
                $validator->errors()->add(
                    'user_upload.scan_extent',
                    'The selected scan extent is not valid for this bone type.'
                );
            }

            if ($boneType === 'other') {
                $other = trim((string) $this->input('user_upload.bone_type_other'));
                if ($other === '') {
                    $validator->errors()->add(
                        'user_upload.bone_type_other',
                        'Specify the bone type when selecting Other.'
                    );
                }
            }
            if ($scanExtent === 'other') {
                $extentOther = trim((string) $this->input('user_upload.scan_extent_other'));
                if ($extentOther === '') {
                    $validator->errors()->add(
                        'user_upload.scan_extent_other',
                        'Specify the scan extent when selecting Other.'
                    );
                }
            }

            if ($boneType !== 'vertebra') {
                return;
            }

            $spinalRegion = (string) $this->input('user_upload.spinal_region');
            if ($spinalRegion === '' || ! in_array($spinalRegion, self::SPINAL_REGIONS, true)) {
                $validator->errors()->add(
                    'user_upload.spinal_region',
                    'Spinal region is required for vertebra uploads.'
                );
            }

            $levels = trim((string) $this->input('user_upload.vertebral_levels'));
            if ($levels === '') {
                $validator->errors()->add(
                    'user_upload.vertebral_levels',
                    'Vertebral level(s) are required for vertebra uploads.'
                );

                return;
            }

            if ($spinalRegion !== '' && ! $this->vertebralLevelsAreValid($levels, $spinalRegion)) {
                $max = self::VERTEBRAL_LEVEL_MAX[$spinalRegion] ?? null;
                $validator->errors()->add(
                    'user_upload.vertebral_levels',
                    $max
                        ? "Enter a single level or range (e.g. 5 or 3-5) within 1–{$max} for {$spinalRegion}."
                        : 'Enter a single level or range (e.g. 5 or 3-5).'
                );
            }
        });
    }

    public function userUploadPayload(): array
    {
        $boneType = (string) data_get($this->validated(), 'user_upload.bone_type');
        $payload = [
            'kind' => (string) data_get($this->validated(), 'user_upload.kind'),
            'processing_level' => (string) data_get($this->validated(), 'user_upload.processing_level'),
            'bone_type' => $boneType,
            'scan_extent' => (string) data_get($this->validated(), 'user_upload.scan_extent'),
        ];

        if ($boneType === 'vertebra') {
            $payload['spinal_region'] = (string) data_get($this->validated(), 'user_upload.spinal_region');
            $payload['vertebral_levels'] = trim((string) data_get($this->validated(), 'user_upload.vertebral_levels'));
        }

        if ($boneType === 'other') {
            $payload['bone_type_other'] = trim((string) data_get($this->validated(), 'user_upload.bone_type_other'));
        }

        if ((string) data_get($this->validated(), 'user_upload.scan_extent') === 'other') {
            $payload['scan_extent_other'] = trim((string) data_get($this->validated(), 'user_upload.scan_extent_other'));
        }

        return $payload;
    }

    protected function vertebralLevelsAreValid(string $value, string $spinalRegion): bool
    {
        $max = self::VERTEBRAL_LEVEL_MAX[$spinalRegion] ?? null;
        if ($max === null) {
            return false;
        }

        if (preg_match('/^(\d+)$/', $value, $matches)) {
            $level = (int) $matches[1];

            return $level >= 1 && $level <= $max;
        }

        if (preg_match('/^(\d+)\s*-\s*(\d+)$/', $value, $matches)) {
            $start = (int) $matches[1];
            $end = (int) $matches[2];

            return $start >= 1
                && $end <= $max
                && $start <= $end;
        }

        return false;
    }
}
