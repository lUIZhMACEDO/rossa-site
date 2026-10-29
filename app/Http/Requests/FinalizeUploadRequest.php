<?php

namespace App\Http\Requests;

use App\Models\AnalysisUpload;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class FinalizeUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'finalize' => 'required|boolean',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $this->validateUploadHasCachedSamples($validator);
            },
        ];
    }

    protected function validateUploadHasCachedSamples(Validator $validator): void
    {
        $study = $this->route('study');
        $uploadId = $this->route('upload_id');

        $upload = AnalysisUpload::where('study_id', $study->id)
            ->where('id', $uploadId)
            ->first();

        if (! $upload) {
            $validator->errors()->add('upload', 'Upload not found.');

            return;
        }

        if (! $upload->group_cache || empty($upload->group_cache)) {
            $validator->errors()->add(
                'finalize',
                'No sample data found to finalize. Please go back and assign samples to groups first.'
            );
        }
    }

    public function messages(): array
    {
        return [
            'finalize.required' => 'Finalization confirmation is required.',
        ];
    }
}
