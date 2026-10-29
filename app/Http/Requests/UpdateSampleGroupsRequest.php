<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSampleGroupsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $study = $this->route('study');

        // only get sample groups for the study
        return [
            'sample_group_id' => 'required|array',
            'sample_group_id.*' => [
                'nullable',
                Rule::exists('sample_groups', 'id')->where('study_id', $study->id),
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $this->validateAtLeastOneSampleGrouped($validator);
            },
        ];
    }

    protected function validateAtLeastOneSampleGrouped(Validator $validator): void
    {
        $sampleGroups = $this->input('sample_group_id', []);

        // filter out null/empty assignments
        $assignedGroups = array_filter($sampleGroups, fn ($value) => ! is_null($value) && $value !== '');

        if (empty($assignedGroups)) {
            $validator->errors()->add(
                'sample_group_id',
                'At least one sample must be assigned to a group. If you want to skip samples, you can leave them unassigned, but at least one must have a group.'
            );
        }
    }

    public function messages(): array
    {
        return [
            'sample_group_id.required' => 'Sample group assignments are required.',
            'sample_group_id.*.exists' => 'The selected sample group is invalid.',
        ];
    }
}
