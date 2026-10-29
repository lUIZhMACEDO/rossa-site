<?php

namespace App\Http\Requests;

use App\Models\Study;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignMediaUploadSampleGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Study $study */
        $study = $this->route('study');

        return [
            'sample_group_id' => [
                'required',
                'integer',
                Rule::exists('sample_groups', 'id')->where(fn ($query) => $query->where('study_id', $study->id)),
            ],
        ];
    }
}
