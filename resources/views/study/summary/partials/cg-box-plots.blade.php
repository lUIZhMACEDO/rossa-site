@php
    // convert measurements to array
    $measurementsArray = collect($measurements)->sortBy('display_name')->map(function ($m) {
        if (is_array($m)) {
            return $m;
        }
        return [
            'id' => $m->id,
            'name' => $m->name,
            'display_name' => $m->display_name,
            'unit' => $m->unit,
        ];
    })->values()->all();

    // batch fetch all plot payloads
    $summaryDataService = app(\App\Services\StudySummaryDataService::class);
    $allPlotPayloads = $summaryDataService->buildMultipleMeasurementPlotSeriesPayloads(
        $comparisonGroup,
        $measurementsArray,
        collect($analysisUploadIds),
    );

    $allPlotPayloads = collect($allPlotPayloads)->filter(function (array $payload): bool {
        return collect($payload['plotSeries'])->contains(function (array $series) use ($payload): bool {
            return in_array($series['sample_group_id'], $payload['baseSampleGroupIds'])
                && collect($series['points'])->contains(fn (array $point): bool => is_numeric($point['value']));
        });
    })->all();

    if (empty($allPlotPayloads)) {
        echo view('study.summary.partials.no-comparison-measurements', [
            'study' => $study,
            'publicAccess' => $publicAccess ?? false,
            'modality' => $modality ?? 'measurement',
        ])->render();
        return;
    }

    // Comparison group title, built the same way the Report tab bar does it
    // (see resources/views/livewire/data-exploration.blade.php).
    $comparisonGroupTitle = $comparisonGroup->sampleGroups()
        ->map(fn ($sg) => $sg->label)
        ->join(' vs ');

    // Grid density scales down as the comparison group has more sample
    // groups, so each plot keeps enough width per box regardless of how
    // many groups it has to render. Adjust the thresholds/columns below
    // as real studies show what actually reads well.
    $sampleGroupCount = $comparisonGroup->sampleGroups()->count();

    $targetColumns = match(true) {
        $sampleGroupCount <= 2 => 4,
        $sampleGroupCount <= 4 => 3,
        $sampleGroupCount <= 6 => 2,
        default => 1,
    };

    $rowColsByTarget = [
        4 => 'row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4',
        3 => 'row-cols-1 row-cols-md-2 row-cols-xl-3',
        2 => 'row-cols-1 row-cols-lg-2',
        1 => 'row-cols-1',
    ];

    $rowColsClasses = $rowColsByTarget[$targetColumns];

    // Changes whenever the plotted data changes (tab switch, radio buttons, filters),
    // forcing Livewire to rebuild the plot grid instead of patching it in place.
    // Without this, Livewire morphs the existing DOM, Alpine keeps its old
    // plotSeries state, and init() (which draws the Plotly chart) never re-runs.
    $plotRenderKey = $comparisonGroup->id . '-' . md5(json_encode($allPlotPayloads));
@endphp

<div class="border rounded bg-light p-3" style="max-height: 800px; overflow-y: scroll;">
    <div class="bg-white rounded shadow-sm p-3">
        <h4 class="fs-5 text-center fw-bold mb-3">{{ $comparisonGroupTitle }}</h4>

        <div class="row {{ $rowColsClasses }} g-3" wire:key="plot-grid-{{ $plotRenderKey }}">
            @foreach($measurementsArray as $measurement)
                @php
                    $plotPayload = $allPlotPayloads[$measurement['id']] ?? null;
                @endphp
                @if($plotPayload)
                    <div class="col" wire:key="plot-col-{{ $plotRenderKey }}-{{ $measurement['id'] }}">
                        <div x-data="{ plotVisible: false }" x-intersect="plotVisible = true">
                            <template x-if="plotVisible">
                                @include('study.summary.partials.measurement-plot', [
                                    'measurement' => $measurement,
                                    'study' => $study,
                                    'analysisUploadIds' => $analysisUploadIds,
                                    'plotPayload' => $plotPayload,
                                ])
                            </template>
                            <template x-if="!plotVisible">
                                <div style="height: 380px;" class="d-flex align-items-center justify-content-center bg-light rounded">
                                    <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
