<?php

namespace App\Http\Requests;

use App\Models\AnalysisType;
use App\Models\Region;
use App\Models\SpecimenType;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class PrepareUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $uploadId = $this->route('upload_id');
        $isUpdate = $uploadId !== null;

        return [
            'analysis_type_id' => 'required|exists:analysis_types,id',
            'specimen_type_id' => 'nullable|exists:specimen_types,id',
            'region_id' => 'nullable|exists:regions,id',
            // file required for new uploads
            'file' => $isUpdate ? 'nullable|file|mimes:csv' : 'required|file|mimes:csv',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $this->validateSpecimenTypeAndRegion($validator);
                $this->validateModalityMetadata($validator);
            },
        ];
    }

    protected function validateModalityMetadata(Validator $validator): void
    {
        $analysisType = AnalysisType::find($this->input('analysis_type_id'));
        if (! $analysisType) {
            return;
        }

        $rulesByCode = [
            'micro_ct' => [
                'body_weight_included' => 'required|boolean',
                'voxel_size' => 'required|numeric|min:0|max:9999.9999|decimal:0,4',
                'scan_mode' => 'required|in:in_vivo,ex_vivo',
                'scanner' => 'required|string|max:255',
                'xray_tube_potential' => 'nullable|numeric|min:0|max:999999.99|decimal:0,2',
                'integration_time' => 'nullable|numeric|min:0|max:999999.99|decimal:0,2',
                'analytics_software' => 'nullable|string|max:255',
            ],
            'dexa' => [
                'body_weight_included' => 'required|boolean',
                'dexa_instrument' => 'required|string|max:255',
            ],
            'bone_histo' => [
                'body_weight_included' => 'required|boolean',
                'measurement_software' => 'required|string|max:255',
                'histological_approach' => 'nullable|string',
            ],
        ];

        $rules = $rulesByCode[$analysisType->code] ?? null;
        if (! $rules) {
            return;
        }

        $attributeNames = [
            'body_weight_included' => 'body weight inclusion',
            'voxel_size' => 'voxel size',
            'scan_mode' => 'scan mode',
            'scanner' => 'scanner',
            'xray_tube_potential' => 'x-ray tube potential',
            'integration_time' => 'integration time',
            'analytics_software' => 'analytics software',
            'dexa_instrument' => 'DEXA instrument',
            'measurement_software' => 'measurement software',
            'histological_approach' => 'histological approach',
        ];

        $customMessages = [];
        foreach ($rules as $field => $ruleString) {
            $label = ucfirst($attributeNames[$field] ?? $field);
            $customMessages["{$field}.required"] = "{$label} is required for {$analysisType->name}.";
        }

        $subValidator = \Illuminate\Support\Facades\Validator::make(
            $this->all(),
            $rules,
            $customMessages,
            array_intersect_key($attributeNames, $rules)
        );

        if ($subValidator->fails()) {
            foreach ($subValidator->errors()->toArray() as $field => $messages) {
                foreach ($messages as $message) {
                    $validator->errors()->add($field, $message);
                }
            }
        }
    }

    protected function validateSpecimenTypeAndRegion(Validator $validator): void
    {
        $analysisType = AnalysisType::find($this->input('analysis_type_id'));
        // map of valid specimens
        $validMap = [
            'micro_ct' => [
                'specimen' => ['Femur', 'Tibia', 'Vertebra'],
                'region' => ['Cortical', 'Trabecular'],
            ],
            'bone_histo' => [
                'specimen' => ['Femur', 'Tibia', 'Vertebra'],
                'region' => ['Cortical', 'Trabecular'],
            ],
            'dexa' => [
                'specimen' => ['Femur', 'Tibia', 'Vertebra', 'Whole Body'],
            ],
            'clinical_chemistry' => [
                'specimen' => ['Serum', 'Plasma', 'Urine'],
            ],
        ];

        $config = $validMap[$analysisType->code] ?? null;

        if (! $config) {
            $validator->errors()->add('analysis_type_id', 'Invalid analysis type.');

            return;
        }

        // validate specimen
        if (isset($config['specimen'])) {
            $specimenId = $this->input('specimen_type_id');

            if (! $specimenId) {
                $validator->errors()->add('specimen_type_id', 'Specimen type is required for '.$analysisType->name.'.');
            } else {
                $specimenType = SpecimenType::find($specimenId);
                if (! $specimenType || ! in_array($specimenType->name, $config['specimen'])) {
                    $validator->errors()->add('specimen_type_id', 'Invalid specimen type for '.$analysisType->name.'.');
                }
            }
        }

        // validate region
        if (isset($validMap[$analysisType->code]['region'])) {
            $regionId = $this->input('region_id');

            if (! $regionId) {
                $validator->errors()->add('region_id', 'Region is required for '.$analysisType->name.'.');
            } else {
                $region = Region::find($regionId);
                if (! $region || ! in_array($region->label, $config['region'])) {
                    $validator->errors()->add('region_id', 'Invalid region for '.$analysisType->name.'.');
                }
            }
        }
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Please upload a CSV file.',
            'file.mimes' => 'The file must be a CSV (.csv) file.',
        ];
    }
}
