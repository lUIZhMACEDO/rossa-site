<?php

namespace App\Http\Requests;

use App\Models\AnalysisUpload;
use App\Models\Measurement;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMeasurementMappingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // mapping handles header row, column measurement mappings, custom measurements and units
        return [
            'column_measurement_mappings' => 'required|array|min:1',
            'column_measurement_mappings.*' => 'nullable|exists:measurements,id',
            'custom_measurement_checkbox' => 'nullable|array',
            'custom_measurement_checkbox.*' => 'nullable|boolean',
            'custom_measurements' => 'nullable|array',
            'custom_measurements.*' => 'nullable|string|max:255',
            'custom_units' => 'nullable|array',
            'custom_units.*' => 'nullable|string|max:50',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $this->validateSampleIdentifierMapped($validator);
                $this->validateMeasurements($validator);
                $this->validateCustomMeasurements($validator);
            },
        ];
    }

    // validate that the sample identifier is mapped - REQUIRED
    protected function validateSampleIdentifierMapped(Validator $validator): void
    {
        $mappings = $this->input('column_measurement_mappings', []);
        $customCheckboxes = $this->input('custom_measurement_checkbox', []);

        // get measurement for sample identifier
        $sampleIdentifierMeasurement = Measurement::where('name', 'sample_identifier')->first();

        if (! $sampleIdentifierMeasurement) {
            // skip if does not exist
            return;
        }

        // look for mapping to sample identifier
        $isMapped = false;
        foreach ($mappings as $colIndex => $measurementId) {
            // skip custom
            if (! empty($customCheckboxes[$colIndex])) {
                continue;
            }

            if ($measurementId == $sampleIdentifierMeasurement->id) {
                $isMapped = true;
                break;
            }
        }

        if (! $isMapped) {
            $validator->errors()->add(
                'column_measurement_mappings',
                'You must map at least one column to "Sample Identifier" (Sample Name). This is required to identify each sample row.'
            );
        }
    }

    protected function validateMeasurements(Validator $validator): void
    {
        $mappings = $this->input('column_measurement_mappings', []);
        $customCheckboxes = $this->input('custom_measurement_checkbox', []);

        // for checking duplicates
        $duplicateMeasurements = [];

        // get upload
        $uploadId = $this->route('upload_id');
        $upload = AnalysisUpload::where('id', $uploadId)->first();

        if (! $upload) {
            return;
        }

        // get valid measurements - like controller logic
        $validMeasurementQuery = Measurement::query()
            // filter by analysis type - null is general measurements
            ->where(function ($query) use ($upload) {
                $query->where('analysis_type_id', $upload->analysis_type_id)
                    ->orWhereNull('analysis_type_id');
            })
            // filter by specimen type if set, else general measurements
            ->where(function ($query) use ($upload) {
                if ($upload->specimen_type_id) {
                    $query->where('specimen_type_id', $upload->specimen_type_id)
                        ->orWhereNull('specimen_type_id');
                } else {
                    $query->whereNull('specimen_type_id');
                }
            })
            // filter by region if set
            ->where(function ($regionQuery) use ($upload) {
                $regionQuery->when($upload->region_id, function ($q) use ($upload) {
                    $q->whereHas('regions', function ($q2) use ($upload) {
                        $q2->where('regions.id', $upload->region_id);
                    });
                })
                    ->orWhereDoesntHave('regions');
            });

        // filter by grouping for mechanical testing
        if ($upload->analysisType && $upload->analysisType->code === 'mechanical_testing') {
            $mechanicalDetails = $upload->mechanicalTestingDetails;
            if ($mechanicalDetails && $mechanicalDetails->test_method) {
                // map test method to grouping
                $grouping = match ($mechanicalDetails->test_method) {
                    'bending_3pt', 'bending_4pt' => 'Bending',
                    'torsion' => 'Torsion',
                    'compression' => 'Compression',
                    default => null,
                };

                if ($grouping) {
                    // only filter by grouping on measurements that have an analysis type
                    $validMeasurementQuery->where(function ($groupQuery) use ($grouping) {
                        $groupQuery->where('measurements.grouping', $grouping)
                            ->orWhereNull('measurements.analysis_type_id');
                    });
                }
            }
        }

        $validMeasurementIds = $validMeasurementQuery->pluck('id')->toArray();

        // validate each mapping
        foreach ($mappings as $colIndex => $measurementId) {
            // skip empty and custom mappings
            if (empty($measurementId) || ! empty($customCheckboxes[$colIndex])) {
                continue;
            }

            if (! in_array((int) $measurementId, $validMeasurementIds)) {
                if ($upload->analysisType && $upload->analysisType->code === 'mechanical_testing') {
                    $validator->errors()->add(
                        "column_measurement_mappings.{$colIndex}",
                        "The selected measurement is not valid for this upload's test method. Please select a measurement from the correct test method group."
                    );
                } else {
                    $validator->errors()->add(
                        "column_measurement_mappings.{$colIndex}",
                        "The selected measurement is not valid for this upload's analysis type, specimen type, or region."
                    );
                }
            }

            // track cols to get duplicates
            if (! isset($duplicateMeasurements[$measurementId])) {
                $duplicateMeasurements[$measurementId] = [$colIndex];
            } else {
                $duplicateMeasurements[$measurementId][] = $colIndex;
            }
        }

        // filter to duplicates and add errors
        foreach ($duplicateMeasurements as $measurementId => $columns) {
            if (count($columns) > 1) {
                $measurementName = Measurement::find($measurementId)?->display_name ?? "ID {$measurementId}";
                $message = "Duplicate mapping detected: \"{$measurementName}\". Each measurement can only be mapped once.";

                $validator->errors()->add('column_measurement_mappings', $message);

                foreach ($columns as $colIdx) {
                    $validator->errors()->add("row_error.{$colIdx}", $message);
                }
            }
        }
    }

    protected function validateCustomMeasurements(Validator $validator): void
    {
        $customCheckboxes = $this->input('custom_measurement_checkbox', []);
        $customMeasurements = $this->input('custom_measurements', []);

        foreach ($customCheckboxes as $colIndex => $isCustom) {
            if ($isCustom) {
                $customName = $customMeasurements[$colIndex] ?? '';
                if (empty(trim($customName))) {
                    $validator->errors()->add(
                        "custom_measurements.{$colIndex}",
                        'Custom measurement name is required when the custom checkbox is checked.'
                    );
                }
                // check if units are required
            }
        }
    }

    public function messages(): array
    {
        return [
            'column_measurement_mappings.required' => 'Column mappings are required.',
            'column_measurement_mappings.min' => 'At least one column mapping is required.',
        ];
    }
}
