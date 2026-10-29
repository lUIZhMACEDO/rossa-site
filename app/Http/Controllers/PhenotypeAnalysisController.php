<?php

namespace App\Http\Controllers;

use App\Enums\UploadSteps;
use App\Exceptions\StudyLockedException;
use App\Http\Requests\FinalizeUploadRequest;
use App\Http\Requests\PrepareUploadRequest;
use App\Http\Requests\UpdateMeasurementMappingsRequest;
use App\Http\Requests\UpdateSampleGroupsRequest;
use App\Models\AnalysisType;
use App\Models\AnalysisUpload;
use App\Models\BoneHistoDetail;
use App\Models\DexaDetail;
use App\Models\Measurement;
use App\Models\MicroCtDetail;
use App\Models\Sample;
use App\Models\Study;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PhenotypeAnalysisController extends Controller
{
    public function index(Request $request, Study $study)
    {
        $study->load([
            'analysisUploads' => fn ($query) => $query->orderBy('created_at', 'desc')
                ->with(['analysisType'])
                ->withCount(['samples']),
        ]);

        // group by context
        $groupedUploads = $study->analysisUploads->groupBy(function ($upload) {
            return json_encode($upload->getContext());
        })->map(function ($uploads) {
            return [
                'analysis_type' => $uploads->first()->analysisType->name,
                'analysis_type_code' => $uploads->first()->analysisType->code,
                'context' => $uploads->first()->getContext(),
                'context_description' => $uploads->first()->getContextDescription(),
                'uploads' => $uploads,
            ];
        })->values()
            ->sortBy(fn ($item) => $item['context_description'])
            ->sortBy(fn ($item) => $item['analysis_type']);

        // modality tiles rendered above the uploads table
        $analysisTypes = AnalysisType::all();

        return view('study.phenotype-analysis.index', compact('study', 'groupedUploads', 'analysisTypes'));
    }

    public function upload(Request $request, Study $study, $upload_id = null)
    {
        // prevent starting a new upload
        if ($upload_id === null && $study->isPhenotypeAnalysisLocked(auth()->user())) {
            throw new StudyLockedException;
        }

        // get upload and analysis type
        $upload = $upload_id
            ? $study->analysisUploads()->findOrFail($upload_id)
            : new AnalysisUpload;

        $analysisType = $upload->analysisType ?? AnalysisType::find($request->input('analysis_type_id'));
        if (! $analysisType) {
            return view('study.phenotype-analysis.upload', [
                'study' => $study,
                'upload' => $upload,
                'analysisTypes' => AnalysisType::all(),
            ]);
        }

        // determine the current step (cap COMPLETE / SCAN_DATASETS navigability at Review)
        $maxStep = $upload->current_step
            ? (in_array($upload->current_step, [UploadSteps::COMPLETE, UploadSteps::SCAN_DATASETS], true)
                ? UploadSteps::REVIEW
                : $upload->current_step)
            : UploadSteps::PREPARE_UPLOAD;
        $stepInput = $request->input('step');
        $requestedStep = $stepInput !== null ? UploadSteps::tryFrom((int) $stepInput) : null;

        // Scan Datasets moved to its own tab
        if ($requestedStep === UploadSteps::SCAN_DATASETS) {
            if ($analysisType->code === 'micro_ct'
                && in_array($upload->current_step, [UploadSteps::SCAN_DATASETS, UploadSteps::COMPLETE], true)) {
                return redirect()->route('study.scan-datasets.index', $study);
            }
            $requestedStep = UploadSteps::REVIEW;
        }

        $step = ($requestedStep && $requestedStep->value <= $maxStep->value)
            ? $requestedStep
            : $maxStep;

        $analysisUploadService = $upload->uploadService();

        // prep info for measurements step
        if ($step === UploadSteps::MAP_MEASUREMENTS) {
            // load mechanical testing details if applicable
            if ($upload->analysisType && $upload->analysisType->code === 'mechanical_testing') {
                $upload->load('mechanicalTestingDetails');
            }

            // Preload the measurements that are available for this study, including custom
            $upload->load([
                'measurements' => function ($query) use ($upload) {
                    // Measurements are already filtered by analysis type via the relationship.

                    // Filter by specimen type if set on the upload
                    if ($upload->specimen_type_id) {
                        $query->where(function ($subQuery) use ($upload) {
                            // Include measurements matching this specimen type OR null (general measurements per analysis type)
                            $subQuery->where('measurements.specimen_type_id', $upload->specimen_type_id)
                                ->orWhereNull('measurements.specimen_type_id');
                        });
                    }

                    // This further filters by region if set on the upload.
                    // IMPORTANT: Wrap all the OR conditions together
                    $query->where(function ($regionQuery) use ($upload) {
                        $regionQuery->when($upload->region_id, function ($q) use ($upload) {
                            $q->whereHas('regions', function ($q2) use ($upload) {
                                $q2->where('regions.id', $upload->region_id);
                            });
                        })
                            ->orWhereDoesntHave('regions')
                            ->orWhereIn('measurements.id', $upload->column_measurement_mappings ?? []);
                    });

                    // additional filter by grouping for mechanical testing
                    if ($upload->analysisType && $upload->analysisType->code === 'mechanical_testing' && $upload->test_method) {
                        // map test method to grouping
                        $grouping = match ($upload->test_method) {
                            'bending_3pt', 'bending_4pt' => 'Bending',
                            'torsion' => 'Torsion',
                            'compression' => 'Compression',
                            default => null,
                        };

                        if ($grouping) {
                            // generic measurements merged below
                            $query->where('measurements.grouping', $grouping);
                        }
                    }

                    $query->orderBy('name');

                    // @TODO support custom measurements per study
                },
            ]);
            // Always include the generic measurements (those without an analysis type)
            $upload->measurements = $upload->measurements->merge(
                Measurement::whereNull('analysis_type_id')->get()
            );

            $upload->column_measurement_mappings = $upload->column_measurement_mappings !== null && count($upload->column_measurement_mappings) > 0
                ? $upload->column_measurement_mappings
                : $analysisUploadService->columnMappingArray();
        }

        if ($step === UploadSteps::GROUP_SAMPLES || $step === UploadSteps::REVIEW) {
            $upload->load([
                'measurements' => fn ($q) => $q->whereIn('measurements.id', array_filter($upload->column_measurement_mappings ?? [])),
            ]);
        }

        // prep info for groups step
        if ($step === UploadSteps::GROUP_SAMPLES) {
            $upload->loadCount('samples');

            // cache the model that represents the sample identifier measurement w/ synonyms
            $sampleIdentifierMeasurement = Measurement::where('name', 'sample_identifier')->first();
            $sampleIdentifierColIndex = collect($upload->column_measurement_mappings)->search($sampleIdentifierMeasurement?->id) ?? null;

            // This is a subset of data from the upload/cache to be used for grouping
            $samples = collect();

            // When we have an existing group cache, use those saved elements
            if ($upload->group_cache !== null) {
                $samples = $upload->restoreGroupCacheSamples()
                    ->sortBy('row_index');
            } else {
                // always build samples from the upload file, then merge in existing group assignments
                $existingDbSamples = $upload->samples()->get()->keyBy('row_index');

                $uploadService = $upload->uploadService();
                $uploadService->detectHeaderRowIndex();
                $uploadService->iterateRows(function ($rowIndex, $rowData) use ($upload, $sampleIdentifierColIndex, &$samples, $uploadService, $existingDbSamples) {

                    // Check if this row has a sample identifier (skip empty rows)
                    $hasSampleId = $sampleIdentifierColIndex !== null &&
                                   isset($rowData[$sampleIdentifierColIndex]) &&
                                   ! empty(trim($rowData[$sampleIdentifierColIndex]));

                    $isHeaderRow = $rowIndex === $uploadService->headerRowIndex;

                    if ($hasSampleId && ! $isHeaderRow) {
                        // check if exists in db
                        $existingSample = $existingDbSamples->get($rowIndex);

                        $sample = new Sample;
                        $sample->analysis_upload_id = $upload->id;
                        $sample->row_index = $rowIndex;
                        $sample->label = trim($rowData[$sampleIdentifierColIndex]);
                        $sample->raw_upload_data = json_encode($rowData);

                        // use existing assignment if available
                        if ($existingSample) {
                            $sample->sample_group_id = $existingSample->sample_group_id;
                        }

                        $samples->push($sample);
                    }
                });
                $samples = $samples->sortBy('row_index');
            }
        }

        // prep info for review step (only show samples with group assignments)
        if ($step === UploadSteps::REVIEW) {
            if ($upload->group_cache !== null) {
                $samples = $upload->restoreGroupCacheSamples()
                    ->filter(fn ($s) => $s->sample_group_id !== null)
                    ->sortBy('row_index')
                    ->values();
            } else {
                $samples = $upload->samples()
                    ->whereNotNull('sample_group_id')
                    ->with('sampleGroup', 'measurementValues')
                    ->get()
                    ->sortBy('row_index')
                    ->values();
            }
        }

        return view('study.phenotype-analysis.upload', [
            'study' => $study,
            'upload' => $upload,
            'analysisType' => $analysisType,
            'step' => $step,
            'maxStep' => $maxStep,
            'samples' => $samples ?? null,
        ]);
    }

    // PREPARE_UPLOAD step
    public function updatePrepareUpload(PrepareUploadRequest $request, Study $study, $upload_id)
    {
        // request self validates
        $upload = $study->analysisUploads()->findOrFail($upload_id);

        // check if need to wipe subsequent steps
        $specimenTypeChanged = $upload->specimen_type_id != $request->input('specimen_type_id');
        $regionChanged = $upload->region_id != $request->input('region_id');
        $hasNewFile = $request->hasFile('file');
        $needsClearSubsequentSteps = $specimenTypeChanged || $regionChanged || $hasNewFile;

        // update specimen type and region
        $upload->specimen_type_id = $request->input('specimen_type_id');
        $upload->region_id = $request->input('region_id');
        $upload->body_weight_included = $request->has('body_weight_included') ? $request->boolean('body_weight_included') : $upload->body_weight_included;

        // handle file re-upload
        if ($hasNewFile) {
            $file = $request->file('file');
            $additionalBytes = (int) $file->getSize() - (int) ($upload->size ?? 0);
            if ($additionalBytes > 0 && ! $study->canStoreBytes($additionalBytes)) {
                return redirect()->back()->withErrors([
                    'file' => 'This study has reached the storage limit.',
                ])->withInput();
            }

            // delete old file
            if ($upload->path && Storage::exists($upload->path)) {
                Storage::delete($upload->path);
            }

            // store new file
            $upload->path = $file->store('analysis_uploads');
            $upload->original_filename = $file->getClientOriginalName();
            $upload->size = $file->getSize();
            $upload->mime_type = $file->getMimeType();

            // re-detect header row
            $uploadService = $upload->uploadService();
            $headerIndex = $uploadService->detectHeaderRowIndex();
            $upload->header_row_index = $headerIndex;

            // verify sample name anchor
            if ($uploadService->verifySampleNameAnchor() === false) {
                return redirect()->back()->withErrors(['file' => 'The uploaded file does not contain a valid sample name anchor. Please ensure a row has "Sample Name" as one of the headers.'])->withInput();
            }
        }

        // clear subsequent steps if configuration changed
        if ($needsClearSubsequentSteps) {
            $upload->column_measurement_mappings = [];
            $upload->group_cache = null;
            $upload->sample_group_ids = null;

            // delete existing samples if any
            $upload->samples()->forceDelete();
        }

        $upload->current_step = UploadSteps::MAP_MEASUREMENTS;
        $upload->save();

        $this->saveModalityMetadata($upload, $request);

        return redirect()
            ->route('study.phenotype-analysis.upload', [
                'study' => $study,
                'upload_id' => $upload->id,
                'step' => UploadSteps::MAP_MEASUREMENTS->value,
            ])
            ->with('success', 'Upload configuration updated. Previous mappings and groupings have been cleared.');
    }

    // per modality metadata update
    protected function saveModalityMetadata(AnalysisUpload $upload, Request $request): void
    {
        $code = $upload->analysisType?->code;

        switch ($code) {
            case 'micro_ct':
                MicroCtDetail::updateOrCreate(
                    ['analysis_upload_id' => $upload->id],
                    [
                        'voxel_size' => $request->input('voxel_size'),
                        'scan_mode' => $request->input('scan_mode'),
                        'scanner' => $request->input('scanner'),
                        'xray_tube_potential' => $request->input('xray_tube_potential'),
                        'integration_time' => $request->input('integration_time'),
                        'analytics_software' => $request->input('analytics_software'),
                    ]
                );
                break;

            case 'dexa':
                DexaDetail::updateOrCreate(
                    ['analysis_upload_id' => $upload->id],
                    [
                        'dexa_instrument' => $request->input('dexa_instrument'),
                    ]
                );
                break;

            case 'bone_histo':
                BoneHistoDetail::updateOrCreate(
                    ['analysis_upload_id' => $upload->id],
                    [
                        'measurement_software' => $request->input('measurement_software'),
                        'histological_approach' => $request->input('histological_approach'),
                    ]
                );
                break;
        }
    }

    // MAP_MEASUREMENTS step
    public function updateMeasurementMappings(UpdateMeasurementMappingsRequest $request, Study $study, $upload_id)
    {
        // request self validates
        $upload = $study->analysisUploads()->findOrFail($upload_id);

        // get mappings
        $mappings = $request->input('column_measurement_mappings', []);
        $customCheckboxes = $request->input('custom_measurement_checkbox', []);
        $customMeasurements = $request->input('custom_measurements', []);
        $customUnits = $request->input('custom_units', []);

        foreach ($mappings as $colIndex => $measurementId) {
            // check if col uses custom measurement
            if (! empty($customCheckboxes[$colIndex]) && isset($customMeasurements[$colIndex]) && ! empty(trim($customMeasurements[$colIndex]))) {
                $displayName = trim($customMeasurements[$colIndex]);

                // check if exists in the study
                $existingMeasurement = Measurement::where('display_name', $displayName)
                    ->where('study_id', $study->id)
                    ->first();

                if ($existingMeasurement) {
                    // if existing, use it
                    $mappings[$colIndex] = $existingMeasurement->id;
                } else {
                    // create new
                    $name = Measurement::generateUniqueName(str()->slug($displayName));
                    $customMeasurement = new Measurement;
                    $customMeasurement->display_name = $displayName;
                    $customMeasurement->name = $name;
                    $customMeasurement->unit = isset($customUnits[$colIndex]) ? trim($customUnits[$colIndex]) : null;
                    $customMeasurement->analysis_type_id = $upload->analysis_type_id;
                    $customMeasurement->study_id = $study->id;
                    $customMeasurement->synonyms = [$displayName, $name];
                    $customMeasurement->save();

                    // update mapping
                    $mappings[$colIndex] = $customMeasurement->id;
                }
            }
        }

        $upload->column_measurement_mappings = $mappings;
        $upload->current_step = UploadSteps::GROUP_SAMPLES;
        $upload->save();

        return redirect()
            ->route('study.phenotype-analysis.upload', [
                'study' => $study,
                'upload_id' => $upload->id,
                'step' => UploadSteps::GROUP_SAMPLES->value,
            ])
            ->with('success', 'Column mappings saved successfully. Now assign samples to groups.');
    }

    // GROUP_SAMPLES step
    public function updateGroupSamples(UpdateSampleGroupsRequest $request, Study $study, $upload_id)
    {
        // request self validates
        $upload = $study->analysisUploads()->findOrFail($upload_id);

        $sampleGroupMappings = $request->input('sample_group_id', []);
        $uploadService = $upload->uploadService();

        // find sample id column
        $sampleIdentifierMeasurement = Measurement::where('name', 'sample_identifier')->first();
        $sampleIdentifierColIndex = null;
        if ($sampleIdentifierMeasurement) {
            foreach ($upload->column_measurement_mappings as $colIndex => $measurementId) {
                if ($measurementId == $sampleIdentifierMeasurement->id) {
                    $sampleIdentifierColIndex = $colIndex;
                    break;
                }
            }
        }
        if ($sampleIdentifierColIndex === null) {
            return redirect()->route('study.phenotype-analysis.upload', [
                'study' => $study,
                'upload_id' => $upload->id,
                'step' => UploadSteps::MAP_MEASUREMENTS->value,
            ])->with('error', 'Sample identifier column not found. Please map a column to "Sample Identifier" (Sample Name).');
        }

        $samples = collect();

        $uploadService->detectHeaderRowIndex();
        $uploadService->iterateRows(function ($rowIndex, $rowData) use ($upload, $sampleGroupMappings, $sampleIdentifierColIndex, &$samples, $uploadService) {
            // check if row has a sample id
            $hasSampleId = $sampleIdentifierColIndex !== null &&
                isset($rowData[$sampleIdentifierColIndex]) &&
                ! empty(trim($rowData[$sampleIdentifierColIndex]));
            $isHeaderRow = $rowIndex === $uploadService->headerRowIndex;

            // process all rows with identifiers (excluding header)
            if ($hasSampleId && ! $isHeaderRow) {
                // create sample
                $sample = $upload->samples()->make([
                    'sample_group_id' => $sampleGroupMappings[$rowIndex] ?? null,
                    'raw_upload_data' => json_encode($rowData),
                    'row_index' => $rowIndex,
                ]);

                // create measurement values
                foreach ($upload->column_measurement_mappings as $colIndex => $measurementId) {
                    if ($measurementId !== null && isset($rowData[$colIndex]) && trim($rowData[$colIndex]) !== '') {
                        $mv = $sample->measurementValues()->make([
                            'measurement_id' => $measurementId,
                            'value' => trim($rowData[$colIndex]),
                        ]);
                        if ($colIndex == $sampleIdentifierColIndex) {
                            $sample->label = trim($rowData[$colIndex]);
                        }
                        $sample->measurementValues->push($mv);
                    }
                }

                $samples->push($sample);
            }
        });

        $upload->group_cache = $samples;

        // keep track of the current sample group ids
        $upload->sample_group_ids = $samples->pluck('sample_group_id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $upload->current_step = UploadSteps::REVIEW;
        $upload->save();

        return redirect()
            ->route('study.phenotype-analysis.upload', [
                'study' => $study,
                'upload_id' => $upload->id,
                'step' => UploadSteps::REVIEW->value,
            ])
            ->with('success', 'Sample groupings saved. Please review before finalizing.');
    }

    // REVIEW step
    public function finalizeUpload(FinalizeUploadRequest $request, Study $study, $upload_id)
    {
        $upload = $study->analysisUploads()->findOrFail($upload_id);
        $isMicroCt = $upload->analysisType?->code === 'micro_ct';

        if ($upload->group_cache === null && $upload->samples()->exists()) {
            return redirect()
                ->route('study.phenotype-analysis', ['study' => $study])
                ->with('success', 'Analysis data & samples have already been created.');
        }

        return DB::transaction(function () use ($upload, $study, $isMicroCt) {
            $upload->samples()->delete();

            // restore and persist cached samples (only those with a group assignment)
            $cachedSamples = $upload->restoreGroupCacheSamples();

            foreach ($cachedSamples as $sample) {
                // only persist samples that have a group assignment
                if ($sample->sample_group_id === null) {
                    continue;
                }

                $sample->analysis_upload_id = $upload->id;
                $sample->save();

                foreach ($sample->measurementValues as $mv) {
                    $mv->sample_id = $sample->id;
                    $mv->save();
                }
            }

            // clear the cache
            $upload->group_cache = null;

            $upload->current_step = UploadSteps::COMPLETE;
            $upload->save();

            $message = 'Analysis data & samples have been created successfully.';
            if ($isMicroCt) {
                $message .= ' You can optionally upload scan images from the Image Upload tab.';
            }

            return redirect()
                ->route('study.phenotype-analysis', ['study' => $study])
                ->with('success', $message);
        });
    }

    public function store(PrepareUploadRequest $request, Study $study)
    {
        $analysisType = AnalysisType::findOrFail($request->input('analysis_type_id'));

        // update the upload
        $upload = $study->analysisUploads()->findOrNew($request->input('upload_id'));
        $upload->analysis_type_id = $analysisType->id;
        $upload->specimen_type_id = $request->input('specimen_type_id');
        $upload->region_id = $request->input('region_id');
        $upload->body_weight_included = $request->has('body_weight_included') ? $request->boolean('body_weight_included') : null;
        $file = $request->file('file');

        $existingSize = $upload->exists ? (int) ($upload->size ?? 0) : 0;
        $additionalBytes = (int) $file->getSize() - $existingSize;
        if ($additionalBytes > 0 && ! $study->canStoreBytes($additionalBytes)) {
            return redirect()->back()->withErrors([
                'file' => 'This study has reached the storage limit.',
            ])->withInput();
        }

        $upload->path = $file->store('analysis_uploads');
        $upload->original_filename = $file->getClientOriginalName();
        $upload->size = $file->getSize();
        $upload->mime_type = $file->getMimeType();
        $upload->save();

        // determine header row index
        $uploadService = $upload->uploadService();
        $headerIndex = $uploadService->detectHeaderRowIndex();
        $upload->header_row_index = $headerIndex;

        // update step
        $upload->current_step = UploadSteps::MAP_MEASUREMENTS;
        $upload->save();

        $this->saveModalityMetadata($upload, $request);

        // validate sample name anchor
        if ($uploadService->verifySampleNameAnchor() === false) {
            $upload->delete();

            return redirect()->back()->withErrors(['file' => 'The uploaded file does not contain a valid sample name anchor. Please ensure a row has "Sample Name" as one of the headers.'])->withInput();
        }

        return redirect()->route('study.phenotype-analysis.upload', ['study' => $study, 'upload_id' => $upload->id, 'step' => UploadSteps::MAP_MEASUREMENTS->value])->with('success', 'Analysis upload saved successfully');
    }

    public function destroy(Request $request, Study $study, string $upload_id)
    {
        $upload = $study->analysisUploads()->findOrFail($upload_id);

        // delete upload (cascades to samples and measurement_values)
        $upload->delete();

        return redirect()->route('study.phenotype-analysis', ['study' => $study])->with('success', 'Analysis data & samples have been deleted successfully.');
    }
}
