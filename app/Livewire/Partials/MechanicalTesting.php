<?php

namespace App\Livewire\Partials;

use App\Enums\UploadSteps;
use App\Exceptions\StudyLockedException;
use App\Models\AnalysisUpload;
use App\Models\MechanicalTestingDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class MechanicalTesting extends Component
{
    use WithFileUploads;

    public $upload;

    public $study;

    public $experimentalStudy;

    public $availableSubTabs;

    public $activeSubTab;

    public $analysisTypeId;

    // to display existing file if possible
    public $existingFileName;

    public $existingFilePath;

    public $boneTested;

    public $orientation;

    // === Geometric Properties ===
    // bending 3pt
    public $supportSpan3pt;

    // bending 4pt
    public $supportSpan4pt;

    public $loadingSpan4pt;

    // torsion
    public $gageLengthTorsion;

    // compression
    public $gageLengthCompression;

    // Preload fields
    public $preloadValue;

    public $preloadUnit = 'N';

    public $customPreloadValue;

    public $customTorsionValue;

    public $offAxisAngle;

    // public $anatomicalReference; // deprecated.. no longer need based of feedback #67
    public $orientationOther;

    // Loading Rate fields
    public $loadingRateBending;

    public $loadingRateCompression;

    public $loadingRateCompressionUnit = 'mm/s';

    public $loadingRateTorsion;

    public $loadingRateTorsionUnit = 'degrees/s';

    // Storage and Testing Conditions
    public $storageMedium;

    public $storageMediumOther;

    public $testConditioning;

    public $testConditioningOther;

    public $storageTemperature;

    public $storageTemperatureOther;

    public $testingTemperature;

    public $testingTemperatureOther;

    public $usePreload = false;

    public $csvFile;

    // Live feedback messages for file upload
    public $fileMessage;

    public $fileError;

    protected function rules()
    {
        $rules = [
            'boneTested' => 'required|in:femur,tibia,ulna,humerus,radius,vertebra,forelimb',

            // geometry
            'supportSpan3pt' => 'exclude_unless:activeSubTab,bending_3pt|required|numeric|min:0',
            'supportSpan4pt' => 'exclude_unless:activeSubTab,bending_4pt|required|numeric|min:0',
            'loadingSpan4pt' => 'exclude_unless:activeSubTab,bending_4pt|required|numeric|min:0',
            'gageLengthTorsion' => 'exclude_unless:activeSubTab,torsion|required|numeric|min:0',
            'gageLengthCompression' => 'exclude_unless:activeSubTab,compression|required|numeric|min:0',

            // Loading Rate
            'loadingRateBending' => 'exclude_unless:activeSubTab,bending_3pt,bending_4pt|required|numeric|min:0',
            'loadingRateCompression' => 'exclude_unless:activeSubTab,compression|required|numeric|min:0',
            'loadingRateCompressionUnit' => 'exclude_unless:activeSubTab,compression|required|in:mm/s,%/s',
            'loadingRateTorsion' => 'exclude_unless:activeSubTab,torsion|required|numeric|min:0',
            'loadingRateTorsionUnit' => 'exclude_unless:activeSubTab,torsion|required|in:degrees/s,Nmm/s',

            // Storage and Testing Conditions
            'storageMedium' => 'required|in:dry,pbs,saline,ethanol,formalin,other',
            'storageMediumOther' => 'required_if:storageMedium,other|nullable|string',
            'testConditioning' => 'required|in:pbs-soaked,saline-soaked,pbs-gauze,submerged,air-dried,other',
            'testConditioningOther' => 'required_if:testConditioning,other|nullable|string',
            'storageTemperature' => 'required|in:fresh,-20,-80,4,room,other',
            'storageTemperatureOther' => 'required_if:storageTemperature,other|nullable|string',
            'testingTemperature' => 'required|in:room,37,4,other',
            'testingTemperatureOther' => 'required_if:testingTemperature,other|nullable|string',
            'usePreload' => 'required|boolean',
        ];

        // orientation varies by test
        if ($this->activeSubTab === 'compression') {
            $rules['orientation'] = 'required|in:axial,off-axis';
            $rules['offAxisAngle'] = 'nullable|required_if:orientation,off-axis|numeric|min:0';
        } elseif ($this->activeSubTab === 'torsion') {
            $rules['orientation'] = 'nullable';
            // $rules['anatomicalReference'] = 'nullable';
        } elseif (in_array($this->activeSubTab, ['bending_3pt', 'bending_4pt'])) {
            $rules['orientation'] = 'required|in:anterior,posterior,medial,lateral,other';
            $rules['orientationOther'] = 'required_if:orientation,other|nullable|string';
        }

        // preload rules vary by test (required if usePreload is true)
        if ($this->usePreload) {
            if ($this->activeSubTab === 'torsion') {
                $rules['customTorsionValue'] = 'required|numeric|min:0';
            } else {
                $rules['preloadValue'] = 'required|in:0.1,0.5,1.0,custom';
                $rules['customPreloadValue'] = 'nullable|numeric|min:0|required_if:preloadValue,custom';
            }
        }

        return $rules;
    }

    protected function validationAttributes(): array
    {
        return [
            'boneTested' => 'bone tested',
            'orientation' => 'orientation',
            'orientationOther' => 'custom orientation',
            'offAxisAngle' => 'off-axis angle',
            'supportSpan3pt' => 'support span',
            'supportSpan4pt' => 'support span',
            'loadingSpan4pt' => 'loading span',
            'gageLengthTorsion' => 'gage length',
            'gageLengthCompression' => 'gage length',
            'loadingRateBending' => 'loading rate',
            'loadingRateCompression' => 'loading rate',
            'loadingRateCompressionUnit' => 'loading rate unit',
            'loadingRateTorsion' => 'loading rate',
            'loadingRateTorsionUnit' => 'loading rate unit',
            'storageMedium' => 'storage medium',
            'storageMediumOther' => 'custom storage medium',
            'testConditioning' => 'test conditioning',
            'testConditioningOther' => 'custom test conditioning',
            'storageTemperature' => 'storage temperature',
            'storageTemperatureOther' => 'custom storage temperature',
            'testingTemperature' => 'testing temperature',
            'testingTemperatureOther' => 'custom testing temperature',
            'usePreload' => 'preload selection',
            'preloadValue' => 'preload value',
            'customPreloadValue' => 'custom preload value',
            'customTorsionValue' => 'custom torsion value',
        ];
    }

    protected function messages(): array
    {
        $context = 'Mechanical Testing';
        $messages = [];

        foreach ($this->validationAttributes() as $field => $label) {
            $sentence = ucfirst($label)." is required for {$context}.";
            $messages["{$field}.required"] = $sentence;
            $messages["{$field}.required_if"] = $sentence;
        }

        return $messages;
    }

    public function mount($study, $upload, $activeSubTab, $analysisTypeId = null)
    {
        $this->study = $study;
        $this->upload = $upload;
        $this->analysisTypeId = $analysisTypeId;

        $this->availableSubTabs = [
            'bending_3pt' => '3 Point Bending',
            'bending_4pt' => '4 Point Bending',
            'torsion' => 'Torsion',
            'compression' => 'Compression',
        ];
        $this->activeSubTab = $activeSubTab ?? 'bending_3pt';

        // check if we have data on the upload already
        if ($this->upload && $this->upload->exists) {
            $config = MechanicalTestingDetail::where('analysis_upload_id', $this->upload->id)->first();

            if ($config) {
                $this->boneTested = $config->bone_tested;
                $this->orientation = $config->orientation;
                $this->preloadUnit = $config->preload_unit;
                $this->supportSpan3pt = $config->support_span_3pt;
                $this->supportSpan4pt = $config->support_span_4pt;
                $this->loadingSpan4pt = $config->loading_span_4pt;
                $this->gageLengthCompression = $config->gage_length_compression;
                $this->gageLengthTorsion = $config->gage_length_torsion;
                $this->loadingRateBending = $config->loading_rate_bending;
                $this->loadingRateCompression = $config->loading_rate_compression;
                $this->loadingRateCompressionUnit = $config->loading_rate_compression_unit ?? 'mm/s';
                $this->loadingRateTorsion = $config->loading_rate_torsion;
                $this->loadingRateTorsionUnit = $config->loading_rate_torsion_unit ?? 'degrees/s';
                $this->storageMedium = $config->storage_medium;
                $this->storageMediumOther = $config->storage_medium_other;
                $this->testConditioning = $config->test_conditioning;
                $this->testConditioningOther = $config->test_conditioning_other;
                $this->storageTemperature = $config->storage_temperature;
                $this->storageTemperatureOther = $config->storage_temperature_other;
                $this->testingTemperature = $config->testing_temperature;
                $this->testingTemperatureOther = $config->testing_temperature_other;
                $this->usePreload = $config->use_preload ?? false;
                $this->offAxisAngle = $config->off_axis_angle;
                // $this->anatomicalReference = $config->anatomical_reference;
                $this->orientationOther = $config->orientation_other;
                $this->activeSubTab = $config->test_method ?? $this->activeSubTab;

                $this->hydratePreloadFields($config);
            }

            // display file data on ui
            if ($this->upload->path && Storage::exists($this->upload->path)) {
                $this->existingFilePath = $this->upload->path;
                $this->existingFileName = $this->upload->original_filename;
            }
        }
    }

    public function render()
    {
        return view('livewire.partials.mechanical-testing', [
            'study' => $this->study,
        ]);
    }

    public function setActiveSubTab($subTab)
    {
        $this->activeSubTab = $subTab;
        $this->clearMethodSpecificFields();
        // livewire-specific methods
        $this->resetValidation();
        $this->resetErrorBag();
    }

    public function updatedActiveSubTab($value)
    {
        $this->clearMethodSpecificFields();

        // livewire-specific methods
        $this->resetValidation();
        $this->resetErrorBag();
    }

    public function updatedOrientation($value)
    {
        if ($value !== 'off-axis') {
            $this->offAxisAngle = null;
        }

        if ($value !== 'other') {
            $this->orientationOther = null;
        }
    }

    public function updatedUsePreload($value)
    {
        if (! $value) {
            $this->preloadValue = null;
            $this->customPreloadValue = null;
            $this->customTorsionValue = null;
        }
    }

    public function updatedCsvFile()
    {
        $this->ensureStudyUnlocked();

        if (! $this->csvFile) {
            return;
        }

        try {
            $newSize = (int) $this->csvFile->getSize();
            $existingSize = ($this->upload && $this->upload->exists) ? (int) ($this->upload->size ?? 0) : 0;
            $additionalBytes = $newSize - $existingSize;
            if ($additionalBytes > 0 && ! $this->study->canStoreBytes($additionalBytes)) {
                $this->fileError = 'This study has reached the storage limit.';
                $this->fileMessage = null;

                return;
            }

            // store temporarily
            $path = $this->csvFile->store('analysis-uploads');

            // temporary upload for file path validation
            $tempUpload = new AnalysisUpload([
                'path' => $path,
                'analysis_type_id' => $this->analysisTypeId,
            ]);

            // validate new file path using temp upload
            $uploadService = $tempUpload->uploadService();
            $headerIndex = $uploadService->detectHeaderRowIndex();

            if ($uploadService->verifySampleNameAnchor() === false) {
                // delete new file, keep old upload unchanged
                Storage::delete($path);
                $this->fileError = 'The uploaded file does not contain a valid sample name anchor. Please ensure a row has "Sample Name" as one of the headers.';
                $this->fileMessage = null;

                return;
            }

            // save upload record
            $metadata = [
                'path' => $path,
                'original_filename' => $this->csvFile->getClientOriginalName(),
                'mime_type' => $this->csvFile->getMimeType(),
                'size' => $this->csvFile->getSize(),
                'header_row_index' => $headerIndex,
            ];

            if (! $this->upload || ! $this->upload->exists) {
                // new upload
                $this->upload = AnalysisUpload::create(array_merge([
                    'study_id' => $this->study->id,
                    'analysis_type_id' => $this->analysisTypeId,
                    'uploaded_by' => Auth::id(),
                ], $metadata));
            } else {
                // updating existing upload
                $oldPath = $this->upload->path;

                $this->upload->update($metadata);

                // delete old file after successful update
                if ($oldPath && Storage::exists($oldPath)) {
                    Storage::delete($oldPath);
                }

                // clear subsequent steps
                $this->upload->column_measurement_mappings = [];
                $this->upload->group_cache = null;
                $this->upload->sample_group_ids = null;
                $this->upload->save();
                $this->upload->samples()->forceDelete();
            }

            $this->existingFileName = $metadata['original_filename'];
            $this->existingFilePath = $path;
            $this->fileMessage = 'File uploaded successfully.';
            $this->fileError = null;

        } catch (\Exception $e) {
            // Remove the file in case of fail
            if (isset($path) && Storage::exists($path)) {
                Storage::delete($path);
            }
            $this->fileError = 'Failed to upload file: '.$e->getMessage();
            $this->fileMessage = null;
        }
    }

    private function clearFields()
    {
        $this->clearMethodSpecificFields();
        $this->storageMedium = null;
        $this->storageMediumOther = null;
        $this->testConditioning = null;
        $this->testConditioningOther = null;
        $this->storageTemperature = null;
        $this->storageTemperatureOther = null;
        $this->testingTemperature = null;
        $this->testingTemperatureOther = null;
    }

    private function clearMethodSpecificFields(): void
    {
        $this->boneTested = null;
        $this->orientation = null;
        $this->orientationOther = null;
        $this->offAxisAngle = null;

        $this->preloadValue = null;
        $this->preloadUnit = 'N';
        $this->customPreloadValue = null;
        $this->customTorsionValue = null;
        $this->usePreload = false;

        $this->supportSpan3pt = null;
        $this->supportSpan4pt = null;
        $this->loadingSpan4pt = null;
        $this->gageLengthTorsion = null;
        $this->gageLengthCompression = null;

        $this->loadingRateBending = null;
        $this->loadingRateCompression = null;
        $this->loadingRateCompressionUnit = 'mm/s';
        $this->loadingRateTorsion = null;
        $this->loadingRateTorsionUnit = 'degrees/s';
    }

    private function hydratePreloadFields(MechanicalTestingDetail $config): void
    {
        $this->customPreloadValue = null;
        $this->customTorsionValue = null;

        if ($this->activeSubTab === 'torsion') {
            if ($config->preload_unit === 'Nmm' && $config->preload_value !== null) {
                $this->usePreload = true;
                $this->customTorsionValue = $config->preload_value;
            }
        } else {
            $presetValues = [0.1, 0.5, 1.0];
            $value = $config->preload_value;

            // try to match the preload value to one of these presets with some epsilon tolerance
            $matchedPreset = collect($presetValues)->first(function ($preset) use ($value) {
                return $value !== null && abs((float) $value - $preset) < 0.00001;
            });

            if ($matchedPreset !== null) {
                $this->preloadValue = (string) $matchedPreset;
            } elseif ($config->preload_value !== null) {
                $this->preloadValue = 'custom';
                $this->customPreloadValue = $config->preload_value;
            } else {
                $this->preloadValue = null;
            }
        }
    }

    public function save()
    {
        $this->ensureStudyUnlocked();

        // refresh upload from db to ensure an upload exists
        if ($this->upload && $this->upload->exists) {
            $this->upload->refresh();
        }

        if (! ($this->upload && $this->upload->exists && $this->upload->path && Storage::exists($this->upload->path))) {
            $this->fileError = 'Please upload a CSV file before saving.';

            return;
        }

        $this->validate();

        if (! $this->upload || ! $this->upload->exists) {
            $this->fileError = 'Upload record not found.';

            return;
        }

        $finalPreloadValue = null;
        $finalPreloadUnit = null;

        if ($this->usePreload) {
            if ($this->activeSubTab === 'torsion') {
                $finalPreloadValue = $this->customTorsionValue;
                $finalPreloadUnit = 'Nmm';
            } else {
                if ($this->preloadValue === 'custom') {
                    $finalPreloadValue = $this->customPreloadValue;
                    $finalPreloadUnit = 'N';
                } else {
                    $finalPreloadValue = $this->preloadValue; // preset values already numeric-ish strings
                    $finalPreloadUnit = 'N';
                }
            }
        }

        // Save mechanical testing details
        MechanicalTestingDetail::updateOrCreate(
            [
                'analysis_upload_id' => $this->upload->id,
            ],
            [
                'test_method' => $this->activeSubTab,
                'bone_tested' => $this->boneTested,
                'orientation' => $this->orientation,
                'preload_value' => $finalPreloadValue,
                'preload_unit' => $finalPreloadUnit,
                'support_span_3pt' => $this->supportSpan3pt,
                'support_span_4pt' => $this->supportSpan4pt,
                'loading_span_4pt' => $this->loadingSpan4pt,
                'gage_length_compression' => $this->gageLengthCompression,
                'gage_length_torsion' => $this->gageLengthTorsion,
                'storage_medium' => $this->storageMedium,
                'storage_medium_other' => $this->storageMediumOther,
                'test_conditioning' => $this->testConditioning,
                'test_conditioning_other' => $this->testConditioningOther,
                'storage_temperature' => $this->storageTemperature,
                'storage_temperature_other' => $this->storageTemperatureOther,
                'testing_temperature' => $this->testingTemperature,
                'testing_temperature_other' => $this->testingTemperatureOther,
                'use_preload' => $this->usePreload,
                'off_axis_angle' => $this->offAxisAngle,
                // 'anatomical_reference' => $this->anatomicalReference,
                'orientation_other' => $this->orientationOther,
                'loading_rate_bending' => $this->loadingRateBending,
                'loading_rate_compression' => $this->loadingRateCompression,
                'loading_rate_compression_unit' => $this->loadingRateCompressionUnit,
                'loading_rate_torsion' => $this->loadingRateTorsion,
                'loading_rate_torsion_unit' => $this->loadingRateTorsionUnit,
            ]
        );

        // check if test method changed - clear mapping
        $testMethodChanged = $this->upload->test_method !== $this->activeSubTab;

        // save test method and bone tested to upload
        $this->upload->test_method = $this->activeSubTab;
        $this->upload->bone_tested = $this->boneTested;

        // clear mappings if test method changed
        if ($testMethodChanged) {
            $this->upload->column_measurement_mappings = [];
        }
        // clear subsequent steps
        $this->upload->group_cache = null;
        $this->upload->sample_group_ids = null;
        $this->upload->save();
        $this->upload->samples()->forceDelete();

        $this->upload->current_step = UploadSteps::MAP_MEASUREMENTS;
        $this->upload->save();

        session()->flash('message', 'Mechanical testing details saved successfully.');

        return redirect()->route('study.phenotype-analysis.upload', [
            'study' => $this->study->id,
            'upload_id' => $this->upload->id,
            'step' => UploadSteps::MAP_MEASUREMENTS->value,
        ]);
    }

    private function ensureStudyUnlocked(): void
    {
        if ($this->study && $this->study->isPhenotypeAnalysisLocked(auth()->user())) {
            throw new StudyLockedException;
        }
    }
}
