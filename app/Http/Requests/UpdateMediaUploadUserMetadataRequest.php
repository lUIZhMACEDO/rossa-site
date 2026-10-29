<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateMediaUploadUserMetadataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_upload' => ['required', 'array'],
            'user_upload.kind' => ['required', 'string', Rule::in(['raw_micro_ct'])],
            'user_upload.processing_level' => ['required', 'string', Rule::in(InitiateMediaUploadRequest::PROCESSING_LEVELS)],
            'user_upload.bone_type' => ['required', 'string', Rule::in(InitiateMediaUploadRequest::BONE_TYPES)],
            'user_upload.bone_type_other' => ['nullable', 'string', 'max:100'],
            'user_upload.spinal_region' => ['nullable', 'string', Rule::in(InitiateMediaUploadRequest::SPINAL_REGIONS)],
            'user_upload.vertebral_levels' => ['nullable', 'string', 'max:50'],
            'user_upload.scan_extent' => ['required', 'string'],
            'user_upload.scan_extent_other' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $boneType = (string) $this->input('user_upload.bone_type');
            $scanExtent = (string) $this->input('user_upload.scan_extent');
            $allowedExtents = $boneType === 'vertebra'
                ? InitiateMediaUploadRequest::VERTEBRA_SCAN_EXTENTS
                : InitiateMediaUploadRequest::LONG_BONE_SCAN_EXTENTS;

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
            if ($spinalRegion === '' || ! in_array($spinalRegion, InitiateMediaUploadRequest::SPINAL_REGIONS, true)) {
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
                $max = InitiateMediaUploadRequest::VERTEBRAL_LEVEL_MAX[$spinalRegion] ?? null;
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
        $max = InitiateMediaUploadRequest::VERTEBRAL_LEVEL_MAX[$spinalRegion] ?? null;
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
