<div>
    <x-session-alerts />

    <p class="text-muted small">
        Bulk-imports measurement data for this collection, one file per study. First map
        the columns on a representative file and verify the matching, then upload the folder
        of files to apply that same mapping to every study. Requires sample groups and
        comparison groups to already exist. Files must use <code>Sample Name</code> as the
        sample-identifier header.
    </p>

    {{-- Analysis context. Bone/Region only matter for column mapping, which
         isn't available here, so they're hidden in metadata-only mode. --}}
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label class="form-label">Analysis Type</label>
            <select class="form-select" wire:model.live="context.analysis_type_id">
                @foreach($this->analysisTypes as $at)
                    <option value="{{ $at->id }}">{{ $at->name }}</option>
                @endforeach
            </select>
        </div>
        @unless($metadataOnly)
            <div class="col-md-4">
                <label class="form-label">Bone / Specimen</label>
                <select class="form-select" wire:model="context.specimen_type_id">
                    @foreach($this->specimenTypes as $st)
                        <option value="{{ $st->id }}">{{ $st->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Region</label>
                <select class="form-select" wire:model="context.region_id">
                    @foreach($this->regions as $r)
                        <option value="{{ $r->id }}">{{ $r->label }}</option>
                    @endforeach
                </select>
            </div>
        @endunless
    </div>
    @unless($metadataOnly)
        <p class="text-muted small">
            Set these to match the data you are importing (e.g. Microcomputed Tomography /
            Femur / Cortical) before mapping. Changing them after mapping means you should
            re-run the matching, since the available measurements differ by region and bone.
        </p>
    @endunless

    {{-- Modality-specific metadata — same instrument/protocol for the whole
         batch, so it's set once here. The fields shown change with the
         Analysis Type dropdown above, since each modality asks for
         different things. --}}

    @if($this->selectedAnalysisTypeCode === 'micro_ct')
    <div class="card mb-3" wire:key="acquisition-micro-ct">
        <div class="card-body">
            <h6 class="card-title">Acquisition Metadata</h6>
            <p class="text-muted small">
                Applied to every upload in this batch. Set once here, same as the analysis
                settings above, since it's the same scanner and protocol for the whole screen.
            </p>

            <div class="mb-3">
                <label class="form-label required">Body Weight</label>
                <div class="form-text mb-2">Are body weight measurements included in the files you are about to upload?</div>
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" id="bw-yes" wire:model="bodyWeightIncluded" value="1">
                    <label class="btn btn-outline-primary" for="bw-yes">Yes</label>
                    <input type="radio" class="btn-check" id="bw-no" wire:model="bodyWeightIncluded" value="0">
                    <label class="btn btn-outline-primary" for="bw-no">No</label>
                </div>
            </div>

            <h6 class="text-muted small text-uppercase mt-4">Acquisition — Required</h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label required">Voxel Size (µm)</label>
                    <input type="number" step="0.0001" class="form-control" placeholder="e.g. 10.4"
                        wire:model="microCtMeta.voxel_size">
                    <div class="form-text">Isotropic voxel edge length</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label required">Scan Mode</label>
                    <select class="form-select" wire:model="microCtMeta.scan_mode">
                        <option value="">-- Select Scan Mode --</option>
                        <option value="in_vivo">In vivo</option>
                        <option value="ex_vivo">Ex vivo</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label required">Scanner</label>
                    <input type="text" class="form-control" placeholder="e.g. Scanco µCT 35"
                        wire:model="microCtMeta.scanner">
                </div>
            </div>

            <h6 class="text-muted small text-uppercase mt-4">Acquisition Parameters — Optional</h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">X-ray Tube Potential (kV)</label>
                    <input type="number" step="0.01" class="form-control" placeholder="e.g. 70"
                        wire:model="microCtMeta.xray_tube_potential">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Integration Time (ms)</label>
                    <input type="number" step="0.01" class="form-control" placeholder="e.g. 400"
                        wire:model="microCtMeta.integration_time">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Analytics Software</label>
                    <input type="text" class="form-control" placeholder="e.g. CTAn 1.18, Dragonfly"
                        wire:model="microCtMeta.analytics_software">
                </div>
            </div>

            <hr class="my-4">

            <h6 class="text-muted small text-uppercase">Save as Collection Settings</h6>
            <p class="text-muted small">
                Records these as this collection's standard settings, so they pre-fill
                automatically for future imports. Safe to set before any data is uploaded.
            </p>
            <button type="button" class="btn btn-primary btn-sm"
                wire:click="saveCollectionDefaults"
                wire:loading.attr="disabled" wire:target="saveCollectionDefaults">
                Save Settings
            </button>
            <span wire:loading wire:target="saveCollectionDefaults" class="ms-2 small text-muted">Saving...</span>

            @if(!empty($this->savedDefaults))
                <div class="mt-3 mb-4">
                    <p class="text-muted small mb-1">Currently saved for this collection:</p>
                    <table class="table table-sm mb-0" style="max-width: 32rem;">
                        <tbody>
                            @foreach($this->savedDefaults as $label => $value)
                                <tr>
                                    <th class="fw-normal text-muted" style="width: 55%;">{{ $label }}</th>
                                    <td>{{ ($value === null || $value === '') ? '—' : $value }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted small mt-3 mb-4">Nothing saved for this analysis type yet.</p>
            @endif

            <h6 class="text-muted small text-uppercase">Backfill Existing Uploads</h6>
            <p class="text-muted small">
                Applies the settings above to any microCT uploads in this collection that were
                created before this section existed and have no acquisition metadata on record.
                Uploads that already have it are left untouched.
            </p>

            @if($backfillError)
                <div class="alert alert-danger py-2 small">{{ $backfillError }}</div>
            @endif

            @if($backfillResult)
                <div class="alert alert-success py-2 small">
                    Backfilled <strong>{{ $backfillResult['backfilled'] }}</strong> of
                    <strong>{{ $backfillResult['total'] }}</strong> microCT uploads.
                    <strong>{{ $backfillResult['already_had'] }}</strong> already had it.
                </div>
            @endif

            <button type="button" class="btn btn-outline-secondary btn-sm"
                wire:click="backfillAcquisitionMetadata"
                wire:loading.attr="disabled" wire:target="backfillAcquisitionMetadata"
                wire:confirm="Apply these settings to every existing microCT upload in this collection that's missing them?">
                Backfill Existing Uploads
            </button>
            <span wire:loading wire:target="backfillAcquisitionMetadata" class="ms-2 small text-muted">Backfilling...</span>
        </div>
    </div>

    @elseif($this->selectedAnalysisTypeCode === 'dexa')
    <div class="card mb-3" wire:key="acquisition-dexa">
        <div class="card-body">
            <h6 class="card-title">Acquisition Metadata</h6>
            <p class="text-muted small">Applied to every DEXA upload in this batch.</p>

            <div class="mb-3">
                <label class="form-label required">Body Weight</label>
                <div class="form-text mb-2">Are body weight measurements included in the files you are about to upload?</div>
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" id="bw-yes-dexa" wire:model="bodyWeightIncluded" value="1">
                    <label class="btn btn-outline-primary" for="bw-yes-dexa">Yes</label>
                    <input type="radio" class="btn-check" id="bw-no-dexa" wire:model="bodyWeightIncluded" value="0">
                    <label class="btn btn-outline-primary" for="bw-no-dexa">No</label>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label required">DEXA Instrument</label>
                    <input type="text" class="form-control" placeholder="e.g. Lunar PIXImus"
                        wire:model="dexaMeta.dexa_instrument">
                </div>
            </div>

            <hr class="my-4">

            <h6 class="text-muted small text-uppercase">Save as Collection Settings</h6>
            <p class="text-muted small">
                Records these as this collection's standard settings, so they pre-fill
                automatically for future imports. Safe to set before any data is uploaded.
            </p>
            <button type="button" class="btn btn-primary btn-sm"
                wire:click="saveCollectionDefaults"
                wire:loading.attr="disabled" wire:target="saveCollectionDefaults">
                Save Settings
            </button>
            <span wire:loading wire:target="saveCollectionDefaults" class="ms-2 small text-muted">Saving...</span>

            @if(!empty($this->savedDefaults))
                <div class="mt-3 mb-4">
                    <p class="text-muted small mb-1">Currently saved for this collection:</p>
                    <table class="table table-sm mb-0" style="max-width: 32rem;">
                        <tbody>
                            @foreach($this->savedDefaults as $label => $value)
                                <tr>
                                    <th class="fw-normal text-muted" style="width: 55%;">{{ $label }}</th>
                                    <td>{{ ($value === null || $value === '') ? '—' : $value }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted small mt-3 mb-4">Nothing saved for this analysis type yet.</p>
            @endif

            <h6 class="text-muted small text-uppercase">Backfill Existing Uploads</h6>
            <p class="text-muted small">
                Applies the settings above to any DEXA uploads in this collection that were
                created before this section existed and have no acquisition metadata on record.
                Uploads that already have it are left untouched.
            </p>

            @if($backfillError)
                <div class="alert alert-danger py-2 small">{{ $backfillError }}</div>
            @endif

            @if($backfillResult)
                <div class="alert alert-success py-2 small">
                    Backfilled <strong>{{ $backfillResult['backfilled'] }}</strong> of
                    <strong>{{ $backfillResult['total'] }}</strong> DEXA uploads.
                    <strong>{{ $backfillResult['already_had'] }}</strong> already had it.
                </div>
            @endif

            <button type="button" class="btn btn-outline-secondary btn-sm"
                wire:click="backfillAcquisitionMetadata"
                wire:loading.attr="disabled" wire:target="backfillAcquisitionMetadata"
                wire:confirm="Apply these settings to every existing DEXA upload in this collection that's missing them?">
                Backfill Existing Uploads
            </button>
            <span wire:loading wire:target="backfillAcquisitionMetadata" class="ms-2 small text-muted">Backfilling...</span>
        </div>
    </div>

    @elseif($this->selectedAnalysisTypeCode === 'bone_histo')
    <div class="card mb-3" wire:key="acquisition-bone-histo">
        <div class="card-body">
            <h6 class="card-title">Acquisition Metadata</h6>
            <p class="text-muted small">Applied to every bone histomorphometry upload in this batch.</p>

            <div class="mb-3">
                <label class="form-label required">Body Weight</label>
                <div class="form-text mb-2">Are body weight measurements included in the files you are about to upload?</div>
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" id="bw-yes-bh" wire:model="bodyWeightIncluded" value="1">
                    <label class="btn btn-outline-primary" for="bw-yes-bh">Yes</label>
                    <input type="radio" class="btn-check" id="bw-no-bh" wire:model="bodyWeightIncluded" value="0">
                    <label class="btn btn-outline-primary" for="bw-no-bh">No</label>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label required">Measurement Software</label>
                    <input type="text" class="form-control" placeholder="e.g. OsteoMeasure"
                        wire:model="boneHistoMeta.measurement_software">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Histological Approach</label>
                    <input type="text" class="form-control"
                        wire:model="boneHistoMeta.histological_approach">
                </div>
            </div>

            <hr class="my-4">

            <h6 class="text-muted small text-uppercase">Save as Collection Settings</h6>
            <p class="text-muted small">
                Records these as this collection's standard settings, so they pre-fill
                automatically for future imports. Safe to set before any data is uploaded.
            </p>
            <button type="button" class="btn btn-primary btn-sm"
                wire:click="saveCollectionDefaults"
                wire:loading.attr="disabled" wire:target="saveCollectionDefaults">
                Save Settings
            </button>
            <span wire:loading wire:target="saveCollectionDefaults" class="ms-2 small text-muted">Saving...</span>

            @if(!empty($this->savedDefaults))
                <div class="mt-3 mb-4">
                    <p class="text-muted small mb-1">Currently saved for this collection:</p>
                    <table class="table table-sm mb-0" style="max-width: 32rem;">
                        <tbody>
                            @foreach($this->savedDefaults as $label => $value)
                                <tr>
                                    <th class="fw-normal text-muted" style="width: 55%;">{{ $label }}</th>
                                    <td>{{ ($value === null || $value === '') ? '—' : $value }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted small mt-3 mb-4">Nothing saved for this analysis type yet.</p>
            @endif

            <h6 class="text-muted small text-uppercase">Backfill Existing Uploads</h6>
            <p class="text-muted small">
                Applies the settings above to any bone histomorphometry uploads in this
                collection that were created before this section existed and have no
                acquisition metadata on record. Uploads that already have it are left untouched.
            </p>

            @if($backfillError)
                <div class="alert alert-danger py-2 small">{{ $backfillError }}</div>
            @endif

            @if($backfillResult)
                <div class="alert alert-success py-2 small">
                    Backfilled <strong>{{ $backfillResult['backfilled'] }}</strong> of
                    <strong>{{ $backfillResult['total'] }}</strong> bone histomorphometry uploads.
                    <strong>{{ $backfillResult['already_had'] }}</strong> already had it.
                </div>
            @endif

            <button type="button" class="btn btn-outline-secondary btn-sm"
                wire:click="backfillAcquisitionMetadata"
                wire:loading.attr="disabled" wire:target="backfillAcquisitionMetadata"
                wire:confirm="Apply these settings to every existing bone histomorphometry upload in this collection that's missing them?">
                Backfill Existing Uploads
            </button>
            <span wire:loading wire:target="backfillAcquisitionMetadata" class="ms-2 small text-muted">Backfilling...</span>
        </div>
    </div>

    @elseif($this->context['analysis_type_id'])
    <div class="alert alert-light border small mb-3">
        No additional acquisition metadata is configured for this analysis type yet.
    </div>
    @endif

    @unless($metadataOnly)
    {{-- Phase one: map --}}
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">1. Map columns (representative file)</h5>
            <div class="row g-2 align-items-end">
                <div class="col-md-8">
                    <input type="file" class="form-control @error('file') is-invalid @enderror"
                        wire:model="file" accept=".csv,text/csv">
                    @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div wire:loading wire:target="file" class="form-text">Uploading...</div>
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-outline-primary"
                        wire:click="runMapping" wire:loading.attr="disabled" wire:target="runMapping">
                        Run Matching
                    </button>
                    <span wire:loading wire:target="runMapping" class="ms-2 small text-muted">Matching...</span>
                </div>
            </div>
        </div>
    </div>

    @if($error)
        <div class="alert alert-danger">
            <div class="small" style="word-break: break-word;">{{ $error }}</div>
        </div>
    @endif

    {{-- Mapping verification table --}}
    @if($mapped)
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title">Verify the mapping</h6>
                <p class="text-muted small">
                    Each column matched to a ROSSA measurement, or left unmapped (metadata like
                    Group_Type, Sex, DOB should stay unmapped). Correct any that are wrong before importing.
                </p>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Source Column</th>
                            <th>Sample Values</th>
                            <th>Mapped Measurement</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($columnSummary as $col)
                            <tr>
                                <td>
                                    <strong>{{ $col['header'] ?: '(blank)' }}</strong>
                                    <span class="d-block text-muted small">col {{ $col['index'] }} · {{ $col['type'] }}</span>
                                </td>
                                <td class="small text-muted">{{ $col['samples'] }}</td>
                                <td>
                                    <select class="form-select form-select-sm" wire:model="mapping.{{ $col['index'] }}">
                                        <option value="">— Unmapped —</option>
                                        @foreach($availableMeasurements as $m)
                                            <option value="{{ $m['id'] }}">{{ $m['name'] }}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Phase two: import folder --}}
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">2. Import all files</h5>
                <p class="text-muted small">
                    Select every per-study file. Each filename should start with the gene symbol
                    (e.g. <code>Adora2b_ct_femur_trabecular.csv</code>). The mapping above is applied
                    to all of them.
                </p>
                <div class="row g-2 align-items-end">
                    <div class="col-md-8">
                        <input type="file" class="form-control @error('dataFiles') is-invalid @enderror"
                            wire:model.live="dataFiles" accept=".csv,text/csv" multiple>
                        @error('dataFiles') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @error('dataFiles.*') <div class="text-danger small">{{ $message }}</div> @enderror
                        <div wire:loading wire:target="dataFiles" class="form-text">Uploading files...</div>
                        @if(is_array($dataFiles) && count($dataFiles))
                            <div class="form-text">{{ count($dataFiles) }} file(s) selected.</div>
                        @endif
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Files per batch</label>
                        <input type="number" min="1" max="25" class="form-control" wire:model="chunkSize">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-primary w-100"
                            wire:click="runImport"
                            @disabled($importing || !is_array($dataFiles) || count($dataFiles) === 0)
                            wire:confirm="Import all selected files into this collection?">
                            Import Files
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Progress --}}
    @if($importing)
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-1 small">
                    <span>Importing files...</span>
                    <span>{{ $processedCount }} / {{ $totalToProcess }}</span>
                </div>
                <div class="progress" role="progressbar" aria-valuenow="{{ $this->progressPercent }}"
                    aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                        style="width: {{ $this->progressPercent }}%">
                        {{ $this->progressPercent }}%
                    </div>
                </div>
                @if($runningResult)
                    <div class="small text-muted mt-2">
                        {{ $runningResult['files_imported'] }} imported,
                        {{ $runningResult['files_skipped'] }} skipped so far.
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Import result --}}
    @if($importResult)
        <div class="alert {{ empty($importResult['problems']) ? 'alert-success' : 'alert-warning' }}">
            Imported <strong>{{ $importResult['files_imported'] }}</strong> of
            <strong>{{ $importResult['files_total'] }}</strong> files.
            <strong>{{ $importResult['samples_created'] }}</strong> samples,
            <strong>{{ $importResult['values_created'] }}</strong> values,
            <strong>{{ $importResult['jobs_dispatched'] }}</strong> statistics jobs dispatched.
            @if($importResult['files_skipped'])
                <strong>{{ $importResult['files_skipped'] }}</strong> skipped.
            @endif

            @if(!empty($importResult['problems']))
                <div class="mt-2">
                    <strong>Problems:</strong>
                    <ul class="mb-0 small">
                        @foreach($importResult['problems'] as $problem)
                            <li>{{ $problem }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="small mt-2">
                Statistics run on the queue. Ensure <code>php artisan queue:work</code> is running.
            </div>
        </div>
    @endif

    @script
    <script>
        // Each chunk runs as its own request. When the server asks for the next
        // chunk, invoke it; the loop ends when the server stops dispatching.
        $wire.on('process-chunk', () => {
            $wire.processChunk();
        });
    </script>
    @endscript
@endunless
</div>
