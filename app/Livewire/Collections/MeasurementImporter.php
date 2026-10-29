<?php

namespace App\Livewire\Collections;

use App\Enums\PermissionType;
use App\Enums\UploadSteps;
use App\Models\AnalysisType;
use App\Models\AnalysisUpload;
use App\Models\BoneHistoDetail;
use App\Models\Collection;
use App\Models\DexaDetail;
use App\Models\Gene;
use App\Models\Measurement;
use App\Models\MeasurementValue;
use App\Models\MicroCtDetail;
use App\Models\Region;
use App\Models\Sample;
use App\Models\SpecimenType;
use App\Models\Study;
use App\Models\User;
use App\Services\AnalysisUploadService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Phase one of the bulk measurement import: upload one representative file for a
 * given analysis (e.g. CT / femur / trabecular), run the existing
 * AnalysisUploadService synonym matching, and let a human verify the resulting
 * column -> measurement mapping. The approved mapping is stored on the collection
 * so phase two can apply it to every study's file unchanged.
 *
 * This reuses AnalysisUploadService exactly: it constructs a throwaway
 * AnalysisUpload carrying the analysis context, which is how the service knows
 * which measurement set to match against.
 */
class MeasurementImporter extends Component
{
    use WithFileUploads;

    public Collection $collection;

    public $file;

    // Analysis context for CT / femur / trabecular. Editable so other
    // bone/region/analysis combinations reuse the same component.
    public array $context = [
        'analysis_type_id' => 1, // microCT
        'specimen_type_id' => 2, // femur
        'region_id' => 2, // trabecular
        'analyte_category_id' => null,
        'test_method' => null,
        'bone_tested' => null,
        'compartment' => null,
    ];

    // Modality-specific metadata — same for every file in this batch (one
    // scanner/protocol for the whole screen), set once here rather than per
    // file. Each modality has its own fields and writes to its own detail
    // model, mirroring the manual upload form's per-modality metadata cards.
    // Only known/confirmed modalities are handled explicitly; anything else
    // shows no metadata section rather than guessing at fields.
    public bool $bodyWeightIncluded = false; // lives on analysis_uploads itself, shared across modalities that ask for it

    public array $microCtMeta = [
        'voxel_size' => null,   // µm, e.g. 10.4
        'scan_mode' => 'ex_vivo', // 'in_vivo' or 'ex_vivo'
        'scanner' => null,   // e.g. "Scanco µCT 35"
        'xray_tube_potential' => null,   // kV, optional
        'integration_time' => null,   // ms, optional
        'analytics_software' => null,   // e.g. "CTAn 1.18", optional
    ];

    public array $dexaMeta = [
        'dexa_instrument' => null,
    ];

    public array $boneHistoMeta = [
        'measurement_software' => null,
        'histological_approach' => null,
    ];

    // Result of the synonym matching, shown for verification. One row per column.
    public array $columnSummary = [];

    // Editable mapping the user can correct: column_index => measurement_id|null
    public array $mapping = [];

    public bool $mapped = false;

    public ?string $error = null;

    // Backfilling acquisition metadata onto microCT uploads created before
    // this section existed.
    public ?array $backfillResult = null;

    public ?string $backfillError = null;

    // Phase two: the folder of per-study files.
    public $dataFiles = [];

    public ?array $importResult = null;

    // Chunked processing state, so a large batch doesn't exceed the request
    // time limit and the UI can show progress between chunks.
    public int $chunkSize = 5;

    public bool $importing = false;

    public int $processedCount = 0;

    public int $totalToProcess = 0;

    public ?array $runningResult = null;

    // All measurements available for this context, for the correction dropdowns.
    public $availableMeasurements = [];

    // When true, only the modality metadata fields + save/backfill action are
    // shown — no file mapping, no bulk import. Used to give the corresponding
    // investigator a way to supply acquisition metadata (scanner, voxel size,
    // etc.) without giving them the bulk-import/backfill machinery, which
    // writes across every study in the collection at once. Guarded on both
    // the view (hidden) and the server (methods below refuse to run), since
    // hiding a button alone doesn't stop a direct Livewire action call.
    #[Locked]
    public bool $metadataOnly = false;

    public function mount(Collection $collection, bool $metadataOnly = false)
    {
        $this->collection = $collection;
        $this->metadataOnly = $metadataOnly;
        $this->prefillMetadataForSelectedType();
    }

    /**
     * Re-prefill whenever the Analysis Type dropdown changes, so switching
     * between e.g. microCT and DEXA loads that modality's own last-used
     * values rather than leaving the previous modality's fields showing.
     * Livewire 3 calls updated{PropertyPath} for nested array properties.
     */
    public function updatedContextAnalysisTypeId(): void
    {
        $this->prefillMetadataForSelectedType();
    }

    /**
     * Pre-fill this modality's metadata from whatever was used on the most
     * recent upload of the same type in this collection. The scanner/
     * protocol/instrument is the same across every batch of a given modality
     * for a screen, so this saves re-typing it each time. Looked up directly
     * by analysis_upload_id rather than through a model relationship, since
     * none is confirmed to exist on these detail models.
     */
    /**
     * Prefill order, most authoritative first:
     *   1. The collection's own saved defaults (a fact about the collection,
     *      settable before any upload exists — see the acquisition-defaults
     *      migration).
     *   2. Failing that, whatever the most recent upload of this modality in
     *      this collection actually used.
     * Values still remain editable per batch, since one batch may legitimately
     * differ from the collection's standard settings.
     */
    private function prefillMetadataForSelectedType(): void
    {
        $c = $this->collection;

        $uploadIds = AnalysisUpload::whereIn(
            'study_id',
            $this->collection->studies()->select('id')
        )->where('analysis_type_id', $this->context['analysis_type_id'])->pluck('id');

        switch ($this->selectedAnalysisTypeCode) {
            case 'micro_ct':
                $last = $uploadIds->isNotEmpty()
                    ? MicroCtDetail::whereIn('analysis_upload_id', $uploadIds)->latest('id')->first()
                    : null;

                $this->microCtMeta['voxel_size'] = $c->default_voxel_size ?? $last?->voxel_size;
                $this->microCtMeta['scan_mode'] = $c->default_scan_mode ?? $last?->scan_mode ?? 'ex_vivo';
                $this->microCtMeta['scanner'] = $c->default_scanner ?? $last?->scanner;
                $this->microCtMeta['xray_tube_potential'] = $c->default_xray_tube_potential ?? $last?->xray_tube_potential;
                $this->microCtMeta['integration_time'] = $c->default_integration_time ?? $last?->integration_time;
                $this->microCtMeta['analytics_software'] = $c->default_analytics_software ?? $last?->analytics_software;
                break;

            case 'dexa':
                $last = $uploadIds->isNotEmpty()
                    ? DexaDetail::whereIn('analysis_upload_id', $uploadIds)->latest('id')->first()
                    : null;

                $this->dexaMeta['dexa_instrument'] = $c->default_dexa_instrument ?? $last?->dexa_instrument;
                break;

            case 'bone_histo':
                $last = $uploadIds->isNotEmpty()
                    ? BoneHistoDetail::whereIn('analysis_upload_id', $uploadIds)->latest('id')->first()
                    : null;

                $this->boneHistoMeta['measurement_software'] = $c->default_measurement_software ?? $last?->measurement_software;
                $this->boneHistoMeta['histological_approach'] = $c->default_histological_approach ?? $last?->histological_approach;
                break;

            default:
                // Unrecognized modality — nothing known to prefill.
                return;
        }

        // body_weight_included lives on the upload itself, shared across
        // whichever of the above modalities asks for it.
        $lastUpload = $uploadIds->isNotEmpty()
            ? AnalysisUpload::whereIn('id', $uploadIds)->whereNotNull('body_weight_included')->latest('id')->first()
            : null;

        $this->bodyWeightIncluded = (bool) ($c->default_body_weight_included ?? $lastUpload?->body_weight_included ?? false);
    }

    /**
     * What's actually persisted on the collection right now for the selected
     * modality, as label => value pairs. Read straight from the collection
     * columns (not the form fields) so it reflects the database, not just
     * whatever is currently typed into the inputs. Returns an empty array if
     * nothing has been saved yet for this modality.
     */
    public function getSavedDefaultsProperty(): array
    {
        $c = $this->collection;

        $rows = match ($this->selectedAnalysisTypeCode) {
            'micro_ct' => [
                'Voxel Size (µm)' => $c->default_voxel_size,
                'Scan Mode' => $c->default_scan_mode ? ucfirst(str_replace('_', ' ', $c->default_scan_mode)) : null,
                'Scanner' => $c->default_scanner,
                'X-ray Tube Potential (kV)' => $c->default_xray_tube_potential,
                'Integration Time (ms)' => $c->default_integration_time,
                'Analytics Software' => $c->default_analytics_software,
            ],
            'dexa' => [
                'DEXA Instrument' => $c->default_dexa_instrument,
            ],
            'bone_histo' => [
                'Measurement Software' => $c->default_measurement_software,
                'Histological Approach' => $c->default_histological_approach,
            ],
            default => [],
        };

        // Nothing saved at all for this modality yet — show nothing rather
        // than a table full of dashes.
        if (empty(array_filter($rows, fn ($v) => $v !== null && $v !== ''))) {
            return [];
        }

        // Body weight is shared across modalities that ask for it.
        if ($c->default_body_weight_included !== null) {
            $rows['Body Weight Included'] = $c->default_body_weight_included ? 'Yes' : 'No';
        }

        return $rows;
    }

    /**
     * Save the currently-entered values as the collection's standard settings.
     * Unlike backfill (which writes onto existing upload rows), this records
     * them as a property of the collection itself, so they can be set before
     * any data has been imported and will pre-fill future imports.
     */
    public function saveCollectionDefaults(): void
    {
        $this->authorizeMetadataEdit();

        $this->backfillResult = null;
        $this->backfillError = null;

        try {
            $updates = ['default_body_weight_included' => $this->bodyWeightIncluded];

            switch ($this->selectedAnalysisTypeCode) {
                case 'micro_ct':
                    $updates += [
                        'default_voxel_size' => $this->microCtMeta['voxel_size'],
                        'default_scan_mode' => $this->microCtMeta['scan_mode'],
                        'default_scanner' => $this->microCtMeta['scanner'],
                        'default_xray_tube_potential' => $this->microCtMeta['xray_tube_potential'],
                        'default_integration_time' => $this->microCtMeta['integration_time'],
                        'default_analytics_software' => $this->microCtMeta['analytics_software'],
                    ];
                    break;

                case 'dexa':
                    $updates += ['default_dexa_instrument' => $this->dexaMeta['dexa_instrument']];
                    break;

                case 'bone_histo':
                    $updates += [
                        'default_measurement_software' => $this->boneHistoMeta['measurement_software'],
                        'default_histological_approach' => $this->boneHistoMeta['histological_approach'],
                    ];
                    break;

                default:
                    $this->backfillError = 'No metadata is configured for this analysis type.';

                    return;
            }

            $this->collection->update($updates);
            $this->collection->refresh();

            session()->flash('success', 'Acquisition settings saved for this collection.');
        } catch (\Throwable $e) {
            $this->backfillError = get_class($e).': '.$e->getMessage();
            report($e);
        }
    }

    /**
     * Write the currently-entered acquisition metadata onto every microCT
     * upload in this collection that doesn't have a MicroCtDetail row yet —
     * covers uploads created before this section existed. Uses the raw
     * analysis_upload_id FK rather than a model relationship, since none is
     * confirmed to exist on either model.
     */
    /**
     * Works for any of the three known modalities, writing to whichever
     * detail model matches the currently selected Analysis Type. Extends
     * cleanly if a fourth modality's fields get confirmed later.
     */
    public function backfillAcquisitionMetadata(): void
    {
        $this->authorizeBulkImport();

        $this->backfillResult = null;
        $this->backfillError = null;

        $code = $this->selectedAnalysisTypeCode;

        $validationError = match ($code) {
            'micro_ct' => (empty($this->microCtMeta['voxel_size']) || empty($this->microCtMeta['scan_mode']) || empty($this->microCtMeta['scanner']))
                ? 'Fill in Voxel Size, Scan Mode, and Scanner above before backfilling.'
                : null,
            'dexa' => empty($this->dexaMeta['dexa_instrument'])
                ? 'Fill in DEXA Instrument above before backfilling.'
                : null,
            'bone_histo' => empty($this->boneHistoMeta['measurement_software'])
                ? 'Fill in Measurement Software above before backfilling.'
                : null,
            default => 'Backfill is not available for this analysis type.',
        };

        if ($validationError) {
            $this->backfillError = $validationError;

            return;
        }

        try {
            $typeId = AnalysisType::where('code', $code)->value('id');

            $uploadIds = AnalysisUpload::whereIn(
                'study_id',
                $this->collection->studies()->select('id')
            )->where('analysis_type_id', $typeId)->pluck('id');

            [$detailModel, $existingDetailUploadIds, $buildRow] = match ($code) {
                'micro_ct' => [
                    MicroCtDetail::class,
                    MicroCtDetail::whereIn('analysis_upload_id', $uploadIds)->pluck('analysis_upload_id'),
                    fn ($id) => [
                        'analysis_upload_id' => $id,
                        'voxel_size' => $this->microCtMeta['voxel_size'],
                        'scan_mode' => $this->microCtMeta['scan_mode'],
                        'scanner' => $this->microCtMeta['scanner'],
                        'xray_tube_potential' => $this->microCtMeta['xray_tube_potential'],
                        'integration_time' => $this->microCtMeta['integration_time'],
                        'analytics_software' => $this->microCtMeta['analytics_software'],
                    ],
                ],
                'dexa' => [
                    DexaDetail::class,
                    DexaDetail::whereIn('analysis_upload_id', $uploadIds)->pluck('analysis_upload_id'),
                    fn ($id) => [
                        'analysis_upload_id' => $id,
                        'dexa_instrument' => $this->dexaMeta['dexa_instrument'],
                    ],
                ],
                'bone_histo' => [
                    BoneHistoDetail::class,
                    BoneHistoDetail::whereIn('analysis_upload_id', $uploadIds)->pluck('analysis_upload_id'),
                    fn ($id) => [
                        'analysis_upload_id' => $id,
                        'measurement_software' => $this->boneHistoMeta['measurement_software'],
                        'histological_approach' => $this->boneHistoMeta['histological_approach'],
                    ],
                ],
            };

            $missingIds = $uploadIds->diff($existingDetailUploadIds);

            foreach ($missingIds as $id) {
                $detailModel::create($buildRow($id));
            }

            if ($missingIds->isNotEmpty()) {
                AnalysisUpload::whereIn('id', $missingIds)
                    ->update(['body_weight_included' => $this->bodyWeightIncluded]);
            }

            $this->backfillResult = [
                'total' => $uploadIds->count(),
                'backfilled' => $missingIds->count(),
                'already_had' => $uploadIds->count() - $missingIds->count(),
            ];
        } catch (\Throwable $e) {
            $this->backfillError = get_class($e).': '.$e->getMessage();
            report($e);
        }
    }

    public function runMapping()
    {
        $this->authorizeBulkImport();

        $this->error = null;
        $this->mapped = false;

        $this->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        try {
            // Store the sample file so the service can read it from disk. It is
            // read-only here for mapping; phase two stores each study's own file.
            $path = $this->file->store('analysis_uploads/_mapping_samples');

            // Throwaway upload row carrying the context, so the service loads the
            // correct measurement set. Not persisted.
            $probe = new AnalysisUpload([
                'study_id' => $this->collection->studies()->value('id'),
                'path' => $path,
                'analysis_type_id' => $this->context['analysis_type_id'],
                'specimen_type_id' => $this->context['specimen_type_id'],
                'region_id' => $this->context['region_id'],
                'analyte_category_id' => $this->context['analyte_category_id'],
                'test_method' => $this->context['test_method'],
                'bone_tested' => $this->context['bone_tested'],
            ]);

            $service = new AnalysisUploadService($probe);

            if (! $service->verifySampleNameAnchor()) {
                $this->error = 'No "Sample Name" header found. Rename the sample-identifier '
                    .'column to "Sample Name" so the matcher can anchor on it.';
                Storage::delete($path);

                return;
            }

            $summary = $service->generateColumnSummary();

            // Build the editable view + the mapping array in one pass.
            $this->columnSummary = [];
            $this->mapping = [];

            foreach ($summary as $col) {
                $measurementId = $col['autofill_measurement']?->id;

                $this->columnSummary[] = [
                    'index' => $col['column_index'],
                    'header' => $col['headerContent'],
                    'type' => $col['inferred_type'],
                    'samples' => collect($col['sample_values'])
                        ->reject(fn ($v) => (string) $v === (string) $col['headerContent'])
                        ->take(3)->implode(', '),
                    'matched' => $col['autofill_measurement']?->display_name,
                ];

                $this->mapping[$col['column_index']] = $measurementId;
            }

            // Measurements offered in the correction dropdowns: the same set the
            // service matched against (this context's measurements).
            $this->availableMeasurements = $service->measurements
                ->sortBy('display_name')
                ->map(fn ($m) => ['id' => $m->id, 'name' => $m->display_name])
                ->values()
                ->toArray();

            $this->mapped = true;

            // Keep the sample file around only long enough to map; remove it now.
            Storage::delete($path);
        } catch (\Throwable $e) {
            $this->error = get_class($e).': '.$e->getMessage();
            report($e);
        }
    }

    /**
     * Phase two: apply the verified mapping to a folder of per-study files.
     * Each file is one study's CT/femur/trabecular data. Filename (or the
     * Line_ID column) identifies the gene -> study. Group assignment comes from
     * each row's Group_Type + Sex.
     */
    /**
     * Start a chunked import. Validates, initialises progress + result state,
     * and kicks off the first chunk. The view re-dispatches processChunk after
     * each chunk so each runs in its own short request (no time-limit issues)
     * and the progress bar advances.
     */
    public function runImport()
    {
        $this->authorizeBulkImport();

        $this->error = null;
        $this->importResult = null;

        if (! $this->mapped) {
            $this->error = 'Verify the column mapping first.';

            return;
        }

        $this->validate([
            'dataFiles' => 'required|array|min:1',
            'dataFiles.*' => 'file|mimes:csv,txt',
        ]);

        $this->importing = true;
        $this->processedCount = 0;
        $this->totalToProcess = count($this->dataFiles);
        $this->runningResult = [
            'files_total' => $this->totalToProcess,
            'files_imported' => 0,
            'files_skipped' => 0,
            'samples_created' => 0,
            'values_created' => 0,
            'jobs_dispatched' => 0,
            'problems' => [],
        ];

        // Ask the view to process the first chunk.
        $this->dispatch('process-chunk');
    }

    /**
     * Process the next $chunkSize files, then either ask for another chunk or
     * finish. Runs as its own request, so the time limit resets each chunk.
     */
    public function processChunk()
    {
        $this->authorizeBulkImport();

        if (! $this->importing) {
            return;
        }

        set_time_limit(0);

        // Resolve indexes once per chunk (cheap; keeps each request self-contained).
        $studies = $this->collection->studies()->with(['sampleGroups', 'animalModels'])->get();
        $groupIndex = $this->buildGroupIndex($studies);
        $geneToStudy = $this->buildGeneIndex($studies);

        $chunk = array_slice($this->dataFiles, $this->processedCount, $this->chunkSize);
        $result = $this->runningResult;

        foreach ($chunk as $file) {
            $tokens = $this->geneTokensFromFilename($file->getClientOriginalName());

            $studyId = null;
            foreach ($tokens as $token) {
                if ($studyId = ($geneToStudy[strtolower($token)] ?? null)) {
                    break;
                }
            }

            if (! $studyId) {
                $tried = implode(', ', $tokens);
                $result['files_skipped']++;
                $result['problems'][] = "{$file->getClientOriginalName()}: no study matched any of [{$tried}]";

                continue;
            }

            // Skip if this study already has an upload for this exact analysis
            // context — prevents duplicate data when a batch is re-run after a
            // partial failure.
            if ($this->studyAlreadyHasUpload($studyId)) {
                $result['files_skipped']++;
                $result['problems'][] = "{$file->getClientOriginalName()}: already imported for this analysis (skipped)";

                continue;
            }

            try {
                DB::transaction(function () use ($file, $studyId, $groupIndex, &$result) {
                    $counts = $this->importOneFile($file, $studyId, $groupIndex);
                    $result['files_imported']++;
                    $result['samples_created'] += $counts['samples'];
                    $result['values_created'] += $counts['values'];
                    $result['jobs_dispatched'] += 1;
                });
            } catch (\Throwable $e) {
                $result['files_skipped']++;
                $result['problems'][] = "{$file->getClientOriginalName()}: ".$e->getMessage();
                report($e);
            }
        }

        $this->processedCount += count($chunk);
        $this->runningResult = $result;

        if ($this->processedCount >= $this->totalToProcess) {
            // Done: publish the final result and stop.
            $this->importing = false;
            $this->importResult = $result;
            $this->dataFiles = [];
        } else {
            // More to do: trigger the next chunk.
            $this->dispatch('process-chunk');
        }
    }

    public function getProgressPercentProperty(): int
    {
        if ($this->totalToProcess === 0) {
            return 0;
        }

        return (int) floor(($this->processedCount / $this->totalToProcess) * 100);
    }

    private function importOneFile($file, int $studyId, array $groupIndex): array
    {
        // Store the real file permanently — this is what the report shows and
        // what gets banked in S3 (Storage default disk).
        $path = $file->store('analysis_uploads');

        $upload = AnalysisUpload::create([
            'study_id' => $studyId,
            'path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'analysis_type_id' => $this->context['analysis_type_id'],
            'specimen_type_id' => $this->context['specimen_type_id'],
            'region_id' => $this->context['region_id'],
            'analyte_category_id' => $this->context['analyte_category_id'],
            'test_method' => $this->context['test_method'],
            'bone_tested' => $this->context['bone_tested'],
            'compartment' => $this->context['compartment'],
            'column_measurement_mappings' => $this->mapping,
            'sample_group_ids' => array_values(array_filter(
                collect($groupIndex[$studyId] ?? [])->flatten(1)->all()
            )),
            'header_row_index' => 0,
            'body_weight_included' => $this->bodyWeightIncluded,
            // Created incomplete on purpose: the AnalysisUploadObserver triggers
            // statistics when the upload reaches COMPLETE, and the samples/values
            // must already exist when that fires. We flip to COMPLETE last.
            'current_step' => UploadSteps::REVIEW,
        ]);

        // Modality-specific details, same pattern as the manual upload's
        // saveModalityMetadata() — one row per upload, keyed on analysis_upload_id,
        // written to whichever detail model matches the selected modality.
        switch ($this->selectedAnalysisTypeCode) {
            case 'micro_ct':
                MicroCtDetail::updateOrCreate(
                    ['analysis_upload_id' => $upload->id],
                    [
                        'voxel_size' => $this->microCtMeta['voxel_size'],
                        'scan_mode' => $this->microCtMeta['scan_mode'],
                        'scanner' => $this->microCtMeta['scanner'],
                        'xray_tube_potential' => $this->microCtMeta['xray_tube_potential'],
                        'integration_time' => $this->microCtMeta['integration_time'],
                        'analytics_software' => $this->microCtMeta['analytics_software'],
                    ]
                );
                break;

            case 'dexa':
                DexaDetail::updateOrCreate(
                    ['analysis_upload_id' => $upload->id],
                    ['dexa_instrument' => $this->dexaMeta['dexa_instrument']]
                );
                break;

            case 'bone_histo':
                BoneHistoDetail::updateOrCreate(
                    ['analysis_upload_id' => $upload->id],
                    [
                        'measurement_software' => $this->boneHistoMeta['measurement_software'],
                        'histological_approach' => $this->boneHistoMeta['histological_approach'],
                    ]
                );
                break;

                // Any other modality: no metadata model confirmed yet, so
                // nothing is written rather than guessing at fields.
        }

        $service = new AnalysisUploadService($upload);

        // Establish the header row, then find the metadata columns once.
        $service->detectHeaderRowIndex();
        $headerRow = $service->rowSampling()[$service->headerRowIndex] ?? [];
        $headerCols = $this->locateMetaColumns($headerRow);

        $sampleCount = 0;
        $valueCount = 0;
        $rowIndex = 0;

        $service->iterateRows(function ($index, $row) use (
            &$sampleCount, &$valueCount, &$rowIndex, $headerCols,
            $upload, $studyId, $groupIndex
        ) {
            $rowIndex++;

            $groupType = strtolower(trim($row[$headerCols['group_type']] ?? ''));
            $sex = strtoupper(trim($row[$headerCols['sex']] ?? ''));
            $label = trim($row[$headerCols['sample_name']] ?? '');

            $isControl = str_starts_with($groupType, 'control');
            $sampleGroupId = $groupIndex[$studyId][$isControl ? 1 : 0][$sex] ?? null;

            // Rows with no resolvable group (blank, unexpected sex) are skipped
            // rather than orphaned.
            if (! $sampleGroupId || $label === '') {
                return;
            }

            $sample = Sample::create([
                'analysis_upload_id' => $upload->id,
                'label' => $label,
                'sample_group_id' => $sampleGroupId,
                'raw_upload_data' => json_encode(array_values($row)),
                'row_index' => $rowIndex,
            ]);
            $sampleCount++;

            // Write a measurement value per mapped column.
            foreach ($this->mapping as $colIndex => $measurementId) {
                if ($measurementId === null) {
                    continue;
                }
                $value = $row[$colIndex] ?? null;
                if ($value === null || $value === '' || ! is_numeric($value)) {
                    continue;
                }
                MeasurementValue::create([
                    'sample_id' => $sample->id,
                    'measurement_id' => $measurementId,
                    'value' => $value,
                ]);
                $valueCount++;
            }
        }, null, true);

        // Now that samples and values exist, mark the upload complete. This is
        // the event the AnalysisUploadObserver fires statistics on, mirroring
        // how the manual wizard reaches COMPLETE only at the final step.
        $upload->update(['current_step' => UploadSteps::COMPLETE]);

        return ['samples' => $sampleCount, 'values' => $valueCount];
    }

    /**
     * True if the target study already has an analysis_uploads row for this exact
     * context (analysis type + specimen + region). Used to skip duplicate files
     * on a re-run.
     */
    private function studyAlreadyHasUpload(int $studyId): bool
    {
        return AnalysisUpload::where('study_id', $studyId)
            ->where('analysis_type_id', $this->context['analysis_type_id'])
            ->where('specimen_type_id', $this->context['specimen_type_id'])
            ->where('region_id', $this->context['region_id'])
            ->exists();
    }

    /**
     * [study_id][is_control 0|1][sex M|F] => sample_group_id
     */
    private function buildGroupIndex($studies): array
    {
        $index = [];
        foreach ($studies as $study) {
            foreach ($study->sampleGroups as $g) {
                $index[$study->id][$g->control ? 1 : 0][$g->sex] = $g->id;
            }
        }

        return $index;
    }

    /**
     * lowercased gene symbol (and synonyms) => study_id, for filename matching.
     */
    private function buildGeneIndex($studies): array
    {
        $index = [];
        foreach ($studies as $study) {
            $ko = $study->animalModels->first(fn ($m) => $m->gene_id !== null);
            if (! $ko || ! $ko->gene) {
                continue;
            }
            $index[strtolower($ko->gene->symbol)] = $study->id;

            // synonyms may be a cast array or a raw JSON string depending on the
            // model; normalize to an array so stale symbols (e.g. Cml2 -> Nat8f2)
            // resolve from the filename.
            $synonyms = $ko->gene->synonyms;
            if (is_string($synonyms)) {
                $synonyms = json_decode($synonyms, true) ?: [];
            }
            foreach ((array) ($synonyms ?? []) as $syn) {
                $index[strtolower(trim($syn))] = $study->id;
            }
        }

        return $index;
    }

    /**
     * Split a filename like "Cml2_Nat8f2_CT_femur_trabecular.csv" into its
     * underscore/dot-separated tokens: ["Cml2", "Nat8f2", "CT", "femur",
     * "trabecular"]. Any token might be the gene symbol — this lets a file be
     * named with BOTH an old and a current symbol (e.g. after appending the
     * current one to a filename that already used a stale one), without
     * needing to rename the original file or wait on synonym data.
     */
    private function geneTokensFromFilename(string $name): array
    {
        $base = preg_replace('/\.(csv|txt)$/i', '', $name);

        return array_values(array_filter(preg_split('/[_\.]/', $base)));
    }

    /**
     * Find the positions of Group_Type, Sex, and Sample Name in the header row.
     */
    private function locateMetaColumns(array $header): array
    {
        $find = function (array $names) use ($header) {
            foreach ($header as $i => $h) {
                if (in_array(strtolower(trim($h)), $names, true)) {
                    return $i;
                }
            }

            return null;
        };

        return [
            'group_type' => $find(['group_type', 'group type']),
            'sex' => $find(['sex']),
            'sample_name' => $find(['sample name', 'sample_id', 'sample id', 'sample_name']),
        ];
    }

    // Options for the context dropdowns, so the user picks by name not by ID.
    public function getAnalysisTypesProperty()
    {
        return AnalysisType::orderBy('name')->get(['id', 'name', 'code']);
    }

    // The selected analysis type's code (e.g. 'micro_ct'), so the view can show
    // the right acquisition-metadata fields for that modality rather than a
    // one-size-fits-all card. Different modalities have different metadata
    // (DEXA has its own instrument field, bone histo its own software field).
    public function getSelectedAnalysisTypeCodeProperty(): ?string
    {
        return $this->analysisTypes->firstWhere('id', $this->context['analysis_type_id'])?->code;
    }

    public function getSpecimenTypesProperty()
    {
        return SpecimenType::orderBy('name')->get(['id', 'name']);
    }

    public function getRegionsProperty()
    {
        return Region::orderBy('label')->get(['id', 'label']);
    }

    public function render()
    {
        return view('livewire.collections.measurement-importer');
    }

    private function authorizeMetadataEdit(): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        abort_unless($user !== null && $this->collection->canUserEditMetadata($user), 403);
    }

    private function authorizeBulkImport(): void
    {
        abort_if($this->metadataOnly, 403);

        Gate::authorize(PermissionType::MANAGE_COLLECTIONS->value);
    }
}
