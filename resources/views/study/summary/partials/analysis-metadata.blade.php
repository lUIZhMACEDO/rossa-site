@php
    $uploads = $study->analysisUploads()
        ->with(['analysisType', 'specimenType', 'analyteCategory', 'region',
                'mechanicalTestingDetails', 'microCtDetails', 'dexaDetails', 'boneHistoDetails'])
        ->get()
        ->sortBy(fn($u) => $u->getContextDescription(withAnalysisType: true))
        ->sortBy(fn($u) => $u->analysisType->name);

    $storageMediumLabels = [
        'pbs'      => 'Wrapped in PBS Soaked Gauze',
        'saline'      => 'Wrapped in Saline Soaked Gauze',
        'ethanol'  => 'In 70% ethanol',
        'formalin' => 'In 10% formalin',
    ];
    $storageTemperatureLabels = [
        'fresh' => 'Fresh (never frozen)',
        '-20'   => '-20°C',
        '-80'   => '-80°C',
        '4'     => '4°C',
        'room'  => 'Room temperature',
    ];
    $testConditioningLabels = [
        'hyrdrated'    => 'Kept hydrated with storage medium before test, then tested in air',
        'submerged'     => 'Testing Submerged (in fluid bath)',
        'other'         => 'Other (specify)',
    ];
    $testingTemperatureLabels = [
        'room' => 'Room temperature (~20–23°C)',
        '37'   => '37°C (body temperature)',
        '4'    => '4°C',
    ];
    $scanModeLabels = [
        'in_vivo' => 'In Vivo',
        'ex_vivo' => 'Ex Vivo',
    ];

    // match colors of upload type selection page
    $typeColors = [
        'micro_ct'           => 'primary',
        'dexa'               => 'success',
        'bone_histo'         => 'danger',
        'mechanical_testing' => 'warning',
        'clinical_chemistry' => 'secondary',
    ];

    // contextual messages for each incomplete step
    $stepMessages = [
        \App\Enums\UploadSteps::PREPARE_UPLOAD->value   => 'Upload configuration is not yet complete — analysis details are unavailable.',
        \App\Enums\UploadSteps::MAP_MEASUREMENTS->value => 'Measurement mapping is not yet complete.',
        \App\Enums\UploadSteps::GROUP_SAMPLES->value    => 'Sample grouping is not yet complete.',
        \App\Enums\UploadSteps::REVIEW->value           => 'Upload is pending final review.',
    ];
@endphp

@if($uploads->isEmpty())
    <p class="text-muted mb-0">No analysis uploads found for this study.</p>
@else
    <div class="border rounded bg-light p-3" style="max-height: 500px; overflow-y: auto;">
        @foreach($uploads as $upload)
            @php
                $isComplete  = $upload->isComplete();
                $stepValue   = $upload->current_step->value;
                $contextDesc = $upload->getContextDescription();
                $borderColor = $typeColors[$upload->analysisType->code] ?? 'primary';
                $detailsReady = $stepValue >= \App\Enums\UploadSteps::MAP_MEASUREMENTS->value;
                $analytesReady = $stepValue >= \App\Enums\UploadSteps::GROUP_SAMPLES->value;
            @endphp
            <div class="card mb-3" style="border-left: 4px solid var(--bs-{{ $borderColor }});">
                <div class="card-body">

                    {{-- header --}}
                    <div class="d-flex align-items-start justify-content-between mb-1">
                        <h6 class="mb-0 fw-semibold">
                            {{ $upload->analysisType->name }}
                        </h6>
                        @if(!$isComplete)
                            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>Incomplete
                                </span>
                            </div>
                        @endif
                    </div>
                    @if($contextDesc)
                        <p class="text-muted mb-1"><small>{{ $contextDesc }}</small></p>
                    @endif
                    <hr class="mt-0 mb-2">

                    {{-- details --}}
                    @if($detailsReady)
                        {{-- microct --}}
                        @if($upload->microCtDetails)
                            @php
                                $ct = $upload->microCtDetails;
                            @endphp
                            <div class="row g-3 mb-0">
                                <div class="col-sm-4">
                                    <small class="text-muted d-block">Bone</small>
                                    @if($upload->specimenType)
                                        {{ $upload->specimenType->name }}
                                    @else
                                        <small class="text-muted">N/A</small>
                                    @endif
                                </div>
                                <div class="col-sm-4">
                                    <small class="text-muted d-block">Region</small>
                                    @if($upload->region)
                                        {{ $upload->region->label }}
                                    @else
                                        <small class="text-muted">N/A</small>
                                    @endif
                                </div>
                                <div class="col-sm-4">
                                    <small class="text-muted d-block">Scan mode</small>
                                    @if($ct->scan_mode)
                                        {{ $scanModeLabels[$ct->scan_mode] ?? ucfirst(str_replace('_', ' ', $ct->scan_mode)) }}
                                    @else
                                        <small class="text-muted">N/A</small>
                                    @endif
                                </div>
                                <div class="col-sm-4">
                                    <small class="text-muted d-block">Voxel size</small>
                                    @if($ct->voxel_size !== null)
                                        {{ $ct->voxel_size }} μm
                                    @else
                                        <small class="text-muted">N/A</small>
                                    @endif
                                </div>
                                <div class="col-sm-4">
                                    <small class="text-muted d-block">Scanner</small>
                                    @if($ct->scanner)
                                        {{ $ct->scanner }}
                                    @else
                                        <small class="text-muted">N/A</small>
                                    @endif
                                </div>
                                <div class="col-sm-4">
                                    <small class="text-muted d-block">Body weight included</small>
                                    @if($upload->body_weight_included !== null)
                                        @if($upload->body_weight_included)
                                            <span class="badge bg-success">Yes</span>
                                        @else
                                            <span class="badge bg-secondary">No</span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="fw-semibold d-block mb-2">Optional parameters</span>
                                    @if($ct->xray_tube_potential !== null || $ct->integration_time !== null || $ct->analytics_software)
                                        <div class="row g-3">
                                            <div class="col-sm-4">
                                                <small class="text-muted d-block">X-ray tube potential</small>
                                                @if($ct->xray_tube_potential !== null)
                                                    {{ $ct->xray_tube_potential }} kVp
                                                @else
                                                    <small class="text-muted">N/A</small>
                                                @endif
                                            </div>
                                            <div class="col-sm-4">
                                                <small class="text-muted d-block">Integration time</small>
                                                @if($ct->integration_time !== null)
                                                    {{ $ct->integration_time }} ms
                                                @else
                                                    <small class="text-muted">—</small>
                                                @endif
                                            </div>
                                            <div class="col-sm-4">
                                                <small class="text-muted d-block">Analysis software</small>
                                                @if($ct->analytics_software)
                                                    {{ $ct->analytics_software }}
                                                @else
                                                    <small class="text-muted">—</small>
                                                @endif
                                            </div>
                                        </div>
                                @else
                                    <p class="text-muted mb-0 ms-3">No details available.</p>
                                @endif
                            </div>

                        {{-- dexa --}}
                        @elseif($upload->dexaDetails)
                            @php $dexa = $upload->dexaDetails; @endphp
                            <div class="row g-3 mb-0">
                                <div class="col-sm-4">
                                    <small class="text-muted d-block">Bone</small>
                                    @if($upload->specimenType)
                                        {{ $upload->specimenType->name }}
                                    @else
                                        <small class="text-muted">N/A</small>
                                    @endif
                                </div>
                                <div class="col-sm-4">
                                    <small class="text-muted d-block">Instrument</small>
                                    @if($dexa->dexa_instrument)
                                        {{ $dexa->dexa_instrument }}
                                    @else
                                        <small class="text-muted">N/A</small>
                                    @endif
                                </div>
                                <div class="col-sm-4">
                                    <small class="text-muted d-block">Body weight included</small>
                                    @if($upload->body_weight_included !== null)
                                        @if($upload->body_weight_included)
                                            <span class="badge bg-success">Yes</span>
                                        @else
                                            <span class="badge bg-secondary">No</span>
                                        @endif
                                    @endif
                                </div>
                            </div>

                        {{-- bone histomorphometry --}}
                        @elseif($upload->boneHistoDetails)
                            @php $histo = $upload->boneHistoDetails; @endphp
                            <div class="row g-3 mb-0">
                                <div class="col-sm-4">
                                    <small class="text-muted d-block">Bone</small>
                                    @if($upload->specimenType)
                                        {{ $upload->specimenType->name }}
                                    @else
                                        <small class="text-muted">N/A</small>
                                    @endif
                                </div>
                                <div class="col-sm-4">
                                    <small class="text-muted d-block">Region</small>
                                    @if($upload->region)
                                        {{ $upload->region->label }}
                                    @else
                                        <small class="text-muted">N/A</small>
                                    @endif
                                </div>
                                <div class="col-sm-4">
                                    <small class="text-muted d-block">Measurement software</small>
                                    @if($histo->measurement_software)
                                        {{ $histo->measurement_software }}
                                    @else
                                        <small class="text-muted">N/A</small>
                                    @endif
                                </div>
                                <div class="col-sm-4">
                                    <small class="text-muted d-block">Body weight included</small>
                                    @if($upload->body_weight_included !== null)
                                        @if($upload->body_weight_included)
                                            <span class="badge bg-success">Yes</span>
                                        @else
                                            <span class="badge bg-secondary">No</span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            <div class="mt-3">
                                <small class="text-muted d-block">Histological approach</small>
                                @if($histo->histological_approach)
                                    {{ $histo->histological_approach }}
                                @else
                                    <small class="text-muted">N/A</small>
                                @endif
                            </div>

                        {{-- mst --}}
                        @elseif($upload->mechanicalTestingDetails)
                            @php
                                $mech = $upload->mechanicalTestingDetails;

                                // support span/geometry
                                $spanLabel = null;
                                $spanValue = null;
                                if ($mech->test_method === 'bending_3pt' && $mech->support_span_3pt !== null) {
                                    $spanLabel = 'Support span';
                                    $spanValue = $mech->support_span_3pt . ' mm';
                                } elseif ($mech->test_method === 'bending_4pt' && $mech->support_span_4pt !== null) {
                                    $spanLabel = 'Support span';
                                    $spanValue = $mech->support_span_4pt . ' mm';
                                } elseif ($mech->test_method === 'torsion' && $mech->gage_length_torsion !== null) {
                                    $spanLabel = 'Gage length';
                                    $spanValue = $mech->gage_length_torsion . ' mm';
                                } elseif ($mech->test_method === 'compression' && $mech->gage_length_compression !== null) {
                                    $spanLabel = 'Gage/Original length';
                                    $spanValue = $mech->gage_length_compression . ' mm';
                                }

                                // loading rate
                                $loadingRateValue = null;
                                if (in_array($mech->test_method, ['bending_3pt', 'bending_4pt']) && $mech->loading_rate_bending !== null) {
                                    $loadingRateValue = $mech->loading_rate_bending . ' mm/s';
                                } elseif ($mech->test_method === 'compression' && $mech->loading_rate_compression !== null) {
                                    $loadingRateValue = $mech->loading_rate_compression . ' ' . ($mech->loading_rate_compression_unit ?? 'mm/s');
                                } elseif ($mech->test_method === 'torsion' && $mech->loading_rate_torsion !== null) {
                                    $loadingRateValue = $mech->loading_rate_torsion . ' ' . ($mech->loading_rate_torsion_unit ?? 'degrees/s');
                                }
                            @endphp
                            <div class="row g-3">
                                <div class="col-sm-4">
                                    <small class="text-muted d-block">Test method</small>
                                    @if($mech->test_method)
                                        {{ $upload->formatTestMethod($mech->test_method) }}
                                    @else
                                        <small class="text-muted">N/A</small>
                                    @endif
                                </div>
                                <div class="col-sm-4">
                                    <small class="text-muted d-block">Bone tested</small>
                                    @if($mech->bone_tested)
                                        {{ $upload->formatBoneTested($mech->bone_tested) }}
                                    @else
                                        <small class="text-muted">N/A</small>
                                    @endif
                                </div>
                                @if($mech->test_method !== 'torsion')
                                    <div class="col-sm-4">
                                        <small class="text-muted d-block">
                                            {{ in_array($mech->test_method, ['bending_3pt', 'bending_4pt']) ? 'Tension side' : 'Load orientation' }}
                                        </small>
                                        @if($mech->orientation)
                                            {{ $upload->formatOrientation($mech) }}
                                        @else
                                            <small class="text-muted">N/A</small>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            @if($spanValue || $mech->loading_span_4pt !== null || $loadingRateValue || $mech->use_preload)
                                <div class="mt-3">
                                    <span class="fw-semibold d-block mb-2">Testing Parameters</span>
                                    <div class="row g-3">
                                        @php
                                            $oneRow = $mech->test_method === 'bending_4pt' ? 'col-sm-6' : 'col-sm-4';
                                        @endphp
                                        <div class="{{ $oneRow }}">
                                            <small class="text-muted d-block">{{ $spanLabel ?? 'Support span' }}</small>
                                            @if($spanValue)
                                                {{ $spanValue }}
                                            @else
                                                <small class="text-muted">N/A</small>
                                            @endif
                                        </div>
                                        @if($mech->test_method === 'bending_4pt')
                                            <div class="{{ $oneRow }}">
                                                <small class="text-muted d-block">Loading span</small>
                                                @if($mech->loading_span_4pt !== null)
                                                    {{ $mech->loading_span_4pt }} mm
                                                @else
                                                    <small class="text-muted">N/A</small>
                                                @endif
                                            </div>
                                        @endif
                                        <div class="{{ $oneRow }}">
                                            <small class="text-muted d-block">Loading rate</small>
                                            @if($loadingRateValue)
                                                {{ $loadingRateValue }}
                                            @else
                                                <small class="text-muted">N/A</small>
                                            @endif
                                        </div>
                                        <div class="{{ $oneRow }}">
                                            <small class="text-muted d-block">Preload</small>
                                            @if($mech->use_preload)
                                                @if($mech->preload_value)
                                                    {{ $mech->preload_value }} {{ $mech->preload_unit ?? 'N' }}
                                                @else
                                                    <span class="text-success fw-semibold">Yes</span>
                                                @endif
                                            @else
                                                <small class="text-muted">N/A</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if($mech->storage_medium || $mech->storage_temperature || $mech->test_conditioning || $mech->testing_temperature)
                                <div class="mt-3">
                                    <span class="fw-semibold d-block mb-2">Sample preparation</span>
                                    <div class="row g-3">
                                        <div class="col-sm-6">
                                            <small class="text-muted d-block">Storage medium</small>
                                            @if($mech->storage_medium)
                                                {{ $mech->storage_medium === 'other'
                                                    ? ($mech->storage_medium_other ?? 'N/A')
                                                    : ($storageMediumLabels[$mech->storage_medium] ?? $mech->storage_medium) }}
                                            @else
                                                <small class="text-muted">N/A</small>
                                            @endif
                                        </div>
                                        <div class="col-sm-6">
                                            <small class="text-muted d-block">Storage temperature</small>
                                            @if($mech->storage_temperature)
                                                {{ $mech->storage_temperature === 'other'
                                                    ? ($mech->storage_temperature_other ?? 'N/A')
                                                    : ($storageTemperatureLabels[$mech->storage_temperature] ?? $mech->storage_temperature) }}
                                            @else
                                                <small class="text-muted">N/A</small>
                                            @endif
                                        </div>
                                        <div class="col-sm-6">
                                            <small class="text-muted d-block">Test conditioning</small>
                                            @if($mech->test_conditioning)
                                                {{ $mech->test_conditioning === 'other'
                                                    ? ($mech->test_conditioning_other ?? 'N/A')
                                                    : ($testConditioningLabels[$mech->test_conditioning] ?? $mech->test_conditioning) }}
                                            @else
                                                <small class="text-muted">N/A</small>
                                            @endif
                                        </div>
                                        <div class="col-sm-6">
                                            <small class="text-muted d-block">Testing temperature</small>
                                            @if($mech->testing_temperature)
                                                {{ $mech->testing_temperature === 'other'
                                                    ? ($mech->testing_temperature_other ?? 'N/A')
                                                    : ($testingTemperatureLabels[$mech->testing_temperature] ?? $mech->testing_temperature) }}
                                            @else
                                                <small class="text-muted">N/A</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                        {{-- clinical chemistry --}}
                        @elseif($upload->analysisType->code === 'clinical_chemistry')
                            <div class="row g-3 mb-0">
                                <div class="col-sm-4">
                                    <small class="text-muted d-block">Specimen type</small>
                                    @if($upload->specimenType)
                                        {{ $upload->specimenType->name }}
                                    @else
                                        <small class="text-muted">N/A</small>
                                    @endif
                                </div>
                            </div>
                            @if($analytesReady)
                                @php
                                    $analytes = \App\Models\Measurement::whereIn('id',
                                        array_filter($upload->column_measurement_mappings ?? [])
                                    )->whereNotNull('analysis_type_id')->get();
                                @endphp
                                @if($analytes->isNotEmpty())
                                    <div class="mt-3">
                                        <small class="text-muted d-block mb-2">Analytes measured</small>
                                        @foreach($analytes as $analyte)
                                            <span class="badge rounded-pill bg-secondary me-1 mb-1">
                                                {{ $analyte->display_name ?? $analyte->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            @endif
                        @endif
                    @endif

                    {{-- incomplete warning --}}
                    @if(!$isComplete)
                        @php
                            $stepMessage = $stepMessages[$stepValue] ?? 'This upload is incomplete.';
                        @endphp
                        <div class="alert alert-warning d-flex align-items-center justify-content-between mt-3 mb-0 py-2 px-3">
                            <small class="me-3">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $stepMessage }}
                            </small>
                            <a href="{{ route('study.phenotype-analysis.upload', ['study' => $study, 'upload_id' => $upload->id]) }}"
                               class="btn btn-sm btn-warning flex-shrink-0">
                                Continue upload <i class="bi bi-pencil"></i>
                            </a>
                        </div>
                    @endif

                </div>
            </div>
        @endforeach
    </div>
@endif
