<div class="container">
    @php
        $availableAnalysisTypes = $scatterData['availableAnalysisTypes'] ?? [];
        $measurementOptions = $scatterData['measurementOptions'] ?? [];
        $testMethodOptions = $scatterData['testMethodOptions'] ?? [];
        $boneSiteOptions = $scatterData['boneSiteOptions'] ?? [];
        $scatterRows = $scatterData['scatterRows'] ?? [];
        $plotPayload = $scatterData['plotPayload'] ?? ['points' => [], 'xAxisTitle' => 'X axis', 'yAxisTitle' => 'Y axis', 'sizeAxisTitle' => null];
        $summary = $scatterData['summary'] ?? [
            'eligibleComparisons' => 0,
            'plottedComparisons' => 0,
            'genesRepresented' => 0,
            'topXIncrease' => null,
            'topYIncrease' => null,
        ];
    @endphp

    <div class="mb-3">
        <a href="{{ route('data.index', ['type' => 'gene']) }}" class="small text-decoration-none">&larr; Back to Data Center</a>
    </div>

    <div class="d-flex flex-column mb-2">
        <h1 class="fw-bold">Gene Data Browser</h1>
        <p class="text-muted">Explore gene data in ROSSA</p>
    </div>

    <ul class="nav nav-tabs mb-4" role="tablist" data-input-unlock="">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'browser-view' ? 'active' : '' }}" wire:click="setActiveTab('browser-view')" type="button">
                Study Browser
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'table-view' ? 'active' : '' }}" wire:click="setActiveTab('table-view')" type="button">
                Scatter / Table View
            </button>
        </li>
    </ul>

    @switch($activeTab)
        @case('browser-view')
            <div class="d-flex">
                <div class="input-group mb-3 w-50 gap-3">
                    <input
                        type="text"
                        class="form-control rounded"
                        placeholder="Search gene symbol, e.g. Sost, Bex4, Lrp5..."
                        aria-label="Search data"
                        aria-describedby="button-search"
                        wire:model.live.debounce.300ms="query"
                    >
                    <button class="btn btn-outline-primary allow-rounded rounded" type="button" id="button-search" wire:click="applySearch">Search</button>
                </div>
                <div class="ms-auto text-muted">
                    {{ $animalModels->total() }} studies found
                </div>
            </div>

            <div class="d-flex gap-2 mb-3">
                <span class="d-flex align-items-center">Data type:</span>
                <button
                    type="button"
                    wire:click="setFilterType('all')"
                    class="btn btn-sm allow-rounded rounded-pill px-3 {{ $filterType === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}"
                >
                    All
                </button>
                <button
                    type="button"
                    wire:click="setFilterType('micro_ct')"
                    class="btn btn-sm allow-rounded rounded-pill px-3 {{ $filterType === 'micro_ct' ? 'btn-primary' : 'btn-outline-secondary' }}"
                >
                    µCT
                </button>
                <button
                    type="button"
                    wire:click="setFilterType('dexa')"
                    class="btn btn-sm allow-rounded rounded-pill px-3 {{ $filterType === 'dexa' ? 'btn-primary' : 'btn-outline-secondary' }}"
                >
                    DEXA
                </button>
                <button
                    type="button"
                    wire:click="setFilterType('bone_histo')"
                    class="btn btn-sm allow-rounded rounded-pill px-3 {{ $filterType === 'bone_histo' ? 'btn-primary' : 'btn-outline-secondary' }}"
                >
                    Histomorphometry
                </button>
                <button
                    type="button"
                    wire:click="setFilterType('mechanical_testing')"
                    class="btn btn-sm allow-rounded rounded-pill px-3 {{ $filterType === 'mechanical_testing' ? 'btn-primary' : 'btn-outline-secondary' }}"
                >
                    Mechanical testing
                </button>
                <span class="d-flex align-items-center" style="margin-left:10px;">Sex:</span>
                <button
                    type="button"
                    wire:click="setSexFilter('all')"
                    class="btn btn-sm allow-rounded rounded-pill px-3 {{ $sexFilter === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}"
                >
                    All
                </button>
                <button
                    type="button"
                    wire:click="setSexFilter('M')"
                    class="btn btn-sm allow-rounded rounded-pill px-3 {{ $sexFilter === 'M' ? 'btn-primary' : 'btn-outline-secondary' }}"
                >
                    Male
                </button>
                <button
                    type="button"
                    wire:click="setSexFilter('F')"
                    class="btn btn-sm allow-rounded rounded-pill px-3 {{ $sexFilter === 'F' ? 'btn-primary' : 'btn-outline-secondary' }}"
                >
                    Female
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-borderless align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-0 pb-2 small text-uppercase text-secondary fw-semibold">Studies</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($animalModels as $animalModel)
                            <tr>
                                <td class="p-0 border-0 @if (!$loop->last) pb-3 @endif">
                                    @include('datacenter.partials.gene_card', ['animalModel' => $animalModel])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td>
                                    <div class="alert alert-light border mb-0">
                                        No studies found.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                <small class="text-muted">
                    Showing {{ $animalModels->firstItem() ?? 0 }}-{{ $animalModels->lastItem() ?? 0 }} of {{ $animalModels->total() }}
                </small>
                {{ $animalModels->links() }}
            </div>
            @break

        @case('table-view')
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex flex-column gap-4">
                        <div>
                            <div class="small text-uppercase text-secondary fw-semibold mb-2">Analysis type</div>
                            <div class="d-flex flex-wrap gap-2">
                                @forelse ($availableAnalysisTypes as $analysisType)
                                    <button
                                        type="button"
                                        wire:click="$set('selectedAnalysisType', '{{ $analysisType['code'] }}')"
                                        class="btn allow-rounded rounded-pill px-3 {{ $selectedAnalysisType === $analysisType['code'] ? 'btn-primary' : 'btn-outline-secondary' }}"
                                    >
                                        {{ $analysisType['name'] }}
                                    </button>
                                @empty
                                    <span class="text-muted small">No analysis types with comparison data are available.</span>
                                @endforelse
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-lg-4">
                                <label for="scatter-x-axis" class="form-label small text-secondary fw-semibold mb-1">X axis</label>
                                <select id="scatter-x-axis" class="form-select" wire:model.live="selectedXAxisMeasurementId">
                                    @forelse ($measurementOptions as $option)
                                        <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
                                    @empty
                                        <option value="">No measurements available</option>
                                    @endforelse
                                </select>
                            </div>
                            <div class="col-12 col-lg-4">
                                <label for="scatter-y-axis" class="form-label small text-secondary fw-semibold mb-1">Y axis</label>
                                <select id="scatter-y-axis" class="form-select" wire:model.live="selectedYAxisMeasurementId">
                                    @forelse ($measurementOptions as $option)
                                        <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
                                    @empty
                                        <option value="">No measurements available</option>
                                    @endforelse
                                </select>
                            </div>
                            <div class="col-12 col-lg-4">
                                <label for="scatter-size-axis" class="form-label small text-secondary fw-semibold mb-1">Bubble size</label>
                                <select id="scatter-size-axis" class="form-select" wire:model.live="selectedSizeMeasurementId">
                                    <option value="">None (uniform size)</option>
                                    @foreach ($measurementOptions as $option)
                                        <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-md-6 col-xl-3">
                                <label for="scatter-test-type" class="form-label small text-secondary fw-semibold mb-1">Test type</label>
                                <select id="scatter-test-type" class="form-select" wire:model.live="selectedTestMethod">
                                    <option value="all">All test types</option>
                                    @foreach ($testMethodOptions as $option)
                                        <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-xl-3">
                                <label for="scatter-bone-site" class="form-label small text-secondary fw-semibold mb-1">Bone site</label>
                                <select id="scatter-bone-site" class="form-select" wire:model.live="selectedBoneSite">
                                    <option value="all">All sites</option>
                                    @foreach ($boneSiteOptions as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-xl-2">
                                <label for="scatter-sex" class="form-label small text-secondary fw-semibold mb-1">Sex</label>
                                <select id="scatter-sex" class="form-select" wire:model.live="selectedSex">
                                    <option value="all">All</option>
                                    <option value="M">Male</option>
                                    <option value="F">Female</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-xl-2">
                                <label for="scatter-age" class="form-label small text-secondary fw-semibold mb-1">Age</label>
                                <select id="scatter-age" class="form-select" wire:model.live="selectedAgeBucket">
                                    <option value="all">All ages</option>
                                    <option value="le_12">&le; 12 wk</option>
                                    <option value="13_24">13-24 wk</option>
                                    <option value="gt_24">&gt; 24 wk</option>
                                    <option value="unknown">Unknown</option>
                                </select>
                            </div>
                            <div class="col-12 col-xl-2">
                                <div class="btn-group w-100" role="group" aria-label="Scatter table toggle">
                                    <button
                                        type="button"
                                        class="btn {{ $scatterDisplayMode === 'scatter' ? 'btn-success' : 'btn-outline-secondary' }}"
                                        wire:click="setScatterDisplayMode('scatter')"
                                    >
                                        Scatter
                                    </button>
                                    <button
                                        type="button"
                                        class="btn {{ $scatterDisplayMode === 'table' ? 'btn-success' : 'btn-outline-secondary' }}"
                                        wire:click="setScatterDisplayMode('table')"
                                    >
                                        Table
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-light border d-flex flex-wrap align-items-center gap-2 mb-3" role="status">
                <span class="small text-uppercase text-secondary fw-semibold">Data Coverage</span>
                <span class="text-muted">{{ $scatterData['coverageMessage'] }}</span>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body">
                            <div class="small text-uppercase text-secondary fw-semibold mb-1">Comparisons plotted</div>
                            <div class="fs-3 fw-bold lh-sm">{{ $summary['plottedComparisons'] }}</div>
                            <div class="small text-muted">of {{ $summary['eligibleComparisons'] }} filtered comparisons</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body">
                            <div class="small text-uppercase text-secondary fw-semibold mb-1">Genes represented</div>
                            <div class="fs-3 fw-bold lh-sm">{{ $summary['genesRepresented'] }}</div>
                            <div class="small text-muted">with data for both axes</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body">
                            <div class="small text-uppercase text-secondary fw-semibold mb-1">Top X increase</div>
                            <div class="fs-3 fw-bold lh-sm text-success">
                                {{ $summary['topXIncrease'] ? sprintf('%+.1f%%', $summary['topXIncrease']['value']) : 'N/A' }}
                            </div>
                            <div class="small text-muted">{{ $summary['topXIncrease']['gene_symbol'] ?? 'No data' }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body">
                            <div class="small text-uppercase text-secondary fw-semibold mb-1">Top Y increase</div>
                            <div class="fs-3 fw-bold lh-sm text-success">
                                {{ $summary['topYIncrease'] ? sprintf('%+.1f%%', $summary['topYIncrease']['value']) : 'N/A' }}
                            </div>
                            <div class="small text-muted">{{ $summary['topYIncrease']['gene_symbol'] ?? 'No data' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($scatterDisplayMode === 'scatter')
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white py-3">
                        <div class="fw-semibold">Scatter view</div>
                        <div class="small text-muted mt-1">
                            Each point is one experimental sample group normalized against the first group in its comparison set.
                            Click a point to open the study.
                        </div>
                    </div>
                    <div class="card-body">
                        @if (count($plotPayload['points']) > 0)
                            <div
                                x-data="geneBrowserScatter({{ Js::from($plotPayload) }})"
                                class="border rounded bg-light-subtle p-4 mb-3"
                                style="min-height: 380px;"
                            >
                                <div x-ref="plot" style="height: 360px; width: 100%;" wire:ignore></div>
                            </div>
                        @else
                            <div class="border rounded bg-light-subtle d-flex align-items-center justify-content-center text-center p-4 mb-3" style="min-height: 380px;">
                                <div class="text-muted">
                                    No scatter points are available for the current axes and filters.
                                </div>
                            </div>
                        @endif

                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge rounded-pill text-bg-success px-3 py-2">Both increased</span>
                            <span class="badge rounded-pill text-bg-danger px-3 py-2">Both decreased</span>
                            <span class="badge rounded-pill text-bg-primary px-3 py-2">X down / Y up</span>
                            <span class="badge rounded-pill text-bg-warning px-3 py-2">X up / Y down</span>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="fw-semibold">Comparisons with data for selected axes</div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="downloadScatterCsv">Download CSV</button>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Gene</th>
                                <th>Model</th>
                                <th>Sex</th>
                                <th>Age</th>
                                <th>Site</th>
                                <th>Context</th>
                                <th class="text-end">X axis</th>
                                <th class="text-end">Y axis</th>
                                <th class="text-end">Size axis</th>
                                <th>Study</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($scatterRows as $row)
                                <tr>
                                    <td>
                                        <a href="{{ $row['study_url'] }}" class="text-decoration-none fw-semibold">{{ $row['gene_symbol'] }}</a>
                                    </td>
                                    <td>{{ $row['model_label'] }}</td>
                                    <td>{{ $row['sex_label'] }}</td>
                                    <td>{{ $row['age'] !== null ? $row['age'] . ' wk' : 'N/A' }}</td>
                                    <td>{{ $row['bone_site_label'] ?: 'N/A' }}</td>
                                    <td>
                                        <div>{{ $row['analysis_type_name'] }}</div>
                                        @if ($row['test_method_label'])
                                            <div class="small text-muted">{{ $row['test_method_label'] }}</div>
                                        @endif
                                    </td>
                                    <td class="text-end {{ $row['x_value'] >= 0 ? 'text-success' : 'text-danger' }} fw-semibold">
                                        {{ sprintf('%+.1f%%', $row['x_value']) }}
                                    </td>
                                    <td class="text-end {{ $row['y_value'] >= 0 ? 'text-success' : 'text-danger' }} fw-semibold">
                                        {{ sprintf('%+.1f%%', $row['y_value']) }}
                                    </td>
                                    <td class="text-end text-muted">
                                        {{ $row['size_value'] !== null ? sprintf('%+.1f%%', $row['size_value']) : 'None' }}
                                    </td>
                                    <td>
                                        <a href="{{ $row['study_url'] }}" class="text-decoration-none">{{ $row['study_identifier'] }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">
                                        No comparisons matched the current filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @break
    @endswitch
</div>

@script
<script>
    Alpine.data('geneBrowserScatter', (payload) => ({
        payload,
        plot: null,
        init() {
            if (!this.$refs.plot || !window.Plotly) {
                return;
            }

            this.render();
            window.addEventListener('resize', () => {
                if (this.plot && window.Plotly.Plots) {
                    window.Plotly.Plots.resize(this.$refs.plot);
                }
            });
        },
        render() {
            const quadrantColors = {
                both_up: '#198754',
                both_down: '#dc3545',
                x_down_y_up: '#0d6efd',
                x_up_y_down: '#ffc107',
            };

            const trace = {
                x: this.payload.points.map((point) => point.x),
                y: this.payload.points.map((point) => point.y),
                mode: 'markers',
                type: 'scatter',
                text: this.payload.points.map((point) =>
                    [
                        `<b>${point.gene_symbol}</b>`,
                        point.model_label,
                        point.study_identifier,
                        `Experimental: ${point.sample_group_label}`,
                        `Control: ${point.control_sample_group_label}`,
                        `Sex: ${point.sex_label}`,
                        `Age: ${point.age_label}`,
                        `Site: ${point.bone_site_label}`,
                        `X: ${point.x.toFixed(1)}%`,
                        `Y: ${point.y.toFixed(1)}%`,
                        point.size_value !== null ? `Size: ${point.size_value.toFixed(1)}%` : null,
                    ].filter(Boolean).join('<br>')
                ),
                hovertemplate: '%{text}<extra></extra>',
                customdata: this.payload.points.map((point) => point.study_url),
                marker: {
                    size: this.payload.points.map((point) => point.r),
                    color: this.payload.points.map((point) => quadrantColors[point.quadrant] ?? '#6c757d'),
                    opacity: 0.85,
                    line: {
                        color: '#ffffff',
                        width: 1,
                    },
                },
            };

            const layout = {
                margin: { t: 20, r: 20, b: 90, l: 95 },
                paper_bgcolor: 'white',
                plot_bgcolor: 'white',
                hovermode: 'closest',
                xaxis: {
                    title: {
                        text: this.payload.xAxisTitle,
                        font: {
                            size: 14,
                        },
                    },
                    automargin: true,
                    zeroline: true,
                    zerolinecolor: '#adb5bd',
                },
                yaxis: {
                    title: {
                        text: this.payload.yAxisTitle,
                        font: {
                            size: 14,
                        },
                    },
                    automargin: true,
                    zeroline: true,
                    zerolinecolor: '#adb5bd',
                },
            };

            const config = {
                responsive: true,
                displaylogo: false,
            };

            window.Plotly.newPlot(this.$refs.plot, [trace], layout, config).then(() => {
                this.plot = this.$refs.plot;
                this.$refs.plot.on('plotly_click', (event) => {
                    const url = event?.points?.[0]?.customdata;
                    if (url) {
                        window.location.href = url;
                    }
                });
            });
        },
    }));
</script>
@endscript
