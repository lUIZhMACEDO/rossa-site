@extends('layout')
@section('content')

@section('title', 'Supported Measurement Parameters')
@section('description', 'Measurement parameters accepted by ROSSA, including auto-detection support and units.')

<style>
    /* Force blue background and white text on ALL table headers */
    .measurement-table thead {
        background-color: #176fb7 !important;
    }

    .measurement-table thead th {
        background-color: #5b5f63ff!important;
        color: white !important;
        font-weight: 600 !important;
        padding: 12px !important;
    }

    .nav-pills .nav-link {
        border-radius: 0.5rem;
        font-weight: 500;
        background-color: #e9ecef;
        color: #495057;
    }

    .nav-pills .nav-link.active {
        background-color: #6c757d;
        color: white;
    }


    .card-header.bg-primary {
        background-color: #176fb7 !important;
    }
</style>

<div class="measurement-params-page py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="mb-4">Supported Measurement Parameters</h1>
            <p class="text-muted">You are also welcome to add custom measurements on upload.</p>
        </div>

        @foreach($measurements->groupBy('analysisType.name') as $analysisTypeName => $category_measurements)
            @if($analysisTypeName !== '') {{-- Skip empty/general measurements --}}
            <div class="card mb-5" style="border-left: 4px solid var(--bs-primary);">
                <div class="card-body">
                    <div class="card-title mb-3">
                        <h3 id="{{ \Illuminate\Support\Str::slug($analysisTypeName) }}">{{ $analysisTypeName ?: 'General Measurements' }}</h3>
                    </div>
                    {{-- MicroCT with Region Grouping --}}
                    @if($analysisTypeName === 'Microcomputed Tomography (µCT)')
                        @php
                            $trabecularMeasurements = $category_measurements->filter(function($m) {
                                return $m->regions->contains('code', 'trabecular');
                            });
                            $corticalMeasurements = $category_measurements->filter(function($m) {
                                return $m->regions->contains('code', 'cortical');
                            });
                        @endphp

                        <ul class="nav nav-pills mb-4" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#uct-trabecular-tab" type="button">
                                    Trabecular
                                    <span class="badge bg-light text-dark ms-1">{{ $trabecularMeasurements->count() }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#uct-cortical-tab" type="button">
                                    Cortical
                                    <span class="badge bg-light text-dark ms-1">{{ $corticalMeasurements->count() }}</span>
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="uct-trabecular-tab">
                                <div class="table-responsive">
                                    <table class="table table-bordered measurement-table">
                                        <thead>
                                            <tr>
                                                <th>Measurement</th>
                                                <th>Region</th>
                                                <th class="d-none d-md-table-cell">Supported Auto-Detection</th>
                                                <th>Unit</th>
                                            </tr>
                                        </thead>
                                        <tbody style="background-color: white;">
                                            @foreach ($trabecularMeasurements->merge($staticMeasurements) as $measurement)
                                                <tr>
                                                    <td><strong>{{ $measurement->display_name }}</strong></td>
                                                    <td>
                                                        @php
                                                            $regionCodes = $measurement->regions->pluck('code')->toArray();
                                                            $isBoth = in_array('trabecular', $regionCodes) && in_array('cortical', $regionCodes);
                                                            $isNeither = !in_array('trabecular', $regionCodes) && !in_array('cortical', $regionCodes);
                                                        @endphp
                                                        @if($isBoth || $isNeither)
                                                            <span class="badge rounded-pill" style="background-color: #6c757d; color: white;">Both</span>
                                                        @else
                                                            <span class="badge rounded-pill" style="background-color: #5a9fd4; color: white;">Trabecular</span>
                                                        @endif
                                                    </td>
                                                    <td class="d-none d-md-table-cell">
                                                        @if(count($measurement->synonyms ?? []) > 5)
                                                            <details>
                                                                <summary style="cursor: pointer; user-select: none; color: #4a90e2;">
                                                                    <small>Show {{ count($measurement->synonyms) }} variants</small>
                                                                </summary>
                                                                <div class="mt-2 p-2 bg-light rounded">
                                                                    @foreach(collect($measurement->synonyms)->chunk(4) as $chunk)
                                                                        <div class="mb-1">
                                                                            @foreach($chunk as $synonym)
                                                                                <code class="me-2 small" style="background-color: #e8f4f8; color: #000;">{{ $synonym }}</code>
                                                                            @endforeach
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </details>
                                                        @else
                                                            <div>
                                                                @foreach($measurement->synonyms ?? [] as $synonym)
                                                                    <code class="me-1 small" style="background-color: #e8f4f8; color: #000;">{{ $synonym }}</code>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td><span class="badge" style="background-color: #6c757d;">{{ $measurement->unit }}</span></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="uct-cortical-tab">
                                <div class="table-responsive">
                                    <table class="table table-bordered measurement-table">
                                        <thead>
                                            <tr>
                                                <th>Measurement</th>
                                                <th>Region</th>
                                                <th class="d-none d-md-table-cell">Supported Auto-Detection</th>
                                                <th>Unit</th>
                                            </tr>
                                        </thead>
                                        <tbody style="background-color: white;">
                                            @foreach ($corticalMeasurements as $measurement)
                                                <tr>
                                                    <td><strong>{{ $measurement->display_name }}</strong></td>
                                                    <td>
                                                        @php
                                                            $regionCodes = $measurement->regions->pluck('code')->toArray();
                                                            $isBoth = in_array('trabecular', $regionCodes) && in_array('cortical', $regionCodes);
                                                        @endphp
                                                        @if($isBoth)
                                                            <span class="badge rounded-pill" style="background-color: #6c757d; color: white;">Both</span>
                                                        @else
                                                            <span class="badge rounded-pill" style="background-color: #4a90e2; color: white;">Cortical</span>
                                                        @endif
                                                    </td>
                                                    <td class="d-none d-md-table-cell">
                                                        @if(count($measurement->synonyms ?? []) > 5)
                                                            <details>
                                                                <summary style="cursor: pointer; user-select: none; color: #4a90e2;">
                                                                    <small>Show {{ count($measurement->synonyms) }} variants</small>
                                                                </summary>
                                                                <div class="mt-2 p-2 bg-light rounded">
                                                                    @foreach(collect($measurement->synonyms)->chunk(4) as $chunk)
                                                                        <div class="mb-1">
                                                                            @foreach($chunk as $synonym)
                                                                                <code class="me-2 small" style="background-color: #e8f4f8; color: #000;">{{ $synonym }}</code>
                                                                            @endforeach
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </details>
                                                        @else
                                                            <div>
                                                                @foreach($measurement->synonyms ?? [] as $synonym)
                                                                    <code class="me-1 small" style="background-color: #e8f4f8; color: #000;">{{ $synonym }}</code>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td><span class="badge" style="background-color: #6c757d;">{{ $measurement->unit }}</span></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    @elseif($analysisTypeName === 'Clinical Chemistry')



                        @php
                            // filter out non-public measurements
                            $category_measurements = $category_measurements->filter(function($m) {
                                return $m->public !== false;
                            });

                            // dedeuplicate by name, collect speciment types
                            $deduplicatedMeasurements = $category_measurements->groupBy('name')->map(function($group) {
                                $first = $group->first();
                                $first->specimenTypes = $group->map(function($m) {
                                    return $m->specimenType;
                                })->filter()->unique('id')->values();
                                return $first;
                            })->values();

                            $groupedMeasurements = $deduplicatedMeasurements->groupBy(function($m) {
                                return $m->grouping ?? 'Other';
                            });
                        @endphp

                        <ul class="nav nav-pills mb-4" role="tablist">
                            @foreach($groupedMeasurements as $groupName => $groupMeasurements)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link @if($loop->first) active @endif"
                                            data-bs-toggle="tab"
                                            data-bs-target="#{{ Str::slug($analysisTypeName) }}-{{ Str::slug($groupName) }}-tab"
                                            type="button">
                                        {{ $groupName }}
                                        <span class="badge bg-light text-dark ms-1">{{ $groupMeasurements->count() }}</span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>

                        <div class="tab-content">
                            @foreach($groupedMeasurements as $groupName => $groupMeasurements)
                                <div class="tab-pane fade @if($loop->first) show active @endif"
                                     id="{{ Str::slug($analysisTypeName) }}-{{ Str::slug($groupName) }}-tab">
                                    <div class="table-responsive">
                                        <table class="table table-bordered measurement-table">
                                            <thead>
                                                <tr>
                                                    <th>Measurement</th>
                                                    <th>Specimen Type</th>
                                                    <th class="d-none d-md-table-cell">Supported Auto-Detection</th>
                                                    <th>Unit</th>
                                                </tr>
                                            </thead>
                                            <tbody style="background-color: white;">
                                                @php if ($loop->first) $groupMeasurements = $groupMeasurements->merge($staticMeasurements); @endphp
                                                @foreach ($groupMeasurements as $measurement)
                                                    <tr>
                                                        <td><strong>{{ $measurement->display_name }}</strong></td>
                                                        <td>
                                                            @if ($measurement->specimenTypes)
                                                                @foreach($measurement->specimenTypes as $specimenType)
                                                                    <span class="badge me-1" style="background-color: #5a9fd4;">{{ $specimenType->name }}</span>
                                                                @endforeach
                                                            @else
                                                                <span class="text-muted">All</span>
                                                            @endif
                                                        </td>
                                                        <td class="d-none d-md-table-cell">
                                                            @if(count($measurement->synonyms ?? []) > 5)
                                                                <details>
                                                                    <summary style="cursor: pointer; user-select: none; color: #4a90e2;">
                                                                        <small>Show {{ count($measurement->synonyms) }} variants</small>
                                                                    </summary>
                                                                    <div class="mt-2 p-2 bg-light rounded">
                                                                        @foreach(collect($measurement->synonyms)->chunk(4) as $chunk)
                                                                            <div class="mb-1">
                                                                                @foreach($chunk as $synonym)
                                                                                    <code class="me-2 small" style="background-color: #e8f4f8; color: #000;">{{ $synonym }}</code>
                                                                                @endforeach
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                </details>
                                                            @else
                                                                <div>
                                                                    @foreach($measurement->synonyms ?? [] as $synonym)
                                                                        <code class="me-1 small" style="background-color: #e8f4f8; color: #000;">{{ $synonym }}</code>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td><span class="badge" style="background-color: #6c757d;">{{ $measurement->unit }}</span></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    {{-- Bone Histomorphometry, Mechanical Strength with Grouping --}}
                    @elseif(in_array($analysisTypeName, ['Bone Histomorphometry', 'Mechanical Strength Testing']))
                        @php
                            // Filter out non-public measurements (like KOMP screen)
                            $category_measurements = $category_measurements->filter(function($m) {
                                return $m->public !== false;
                            });
                            $groupedMeasurements = $category_measurements->groupBy(function($m) {
                                return $m->grouping ?? 'Other';
                            });
                        @endphp

                        <ul class="nav nav-pills mb-4" role="tablist">
                            @foreach($groupedMeasurements as $groupName => $groupMeasurements)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link @if($loop->first) active @endif"
                                            data-bs-toggle="tab"
                                            data-bs-target="#{{ Str::slug($analysisTypeName) }}-{{ Str::slug($groupName) }}-tab"
                                            type="button">
                                        {{ $groupName }}
                                        <span class="badge bg-light text-dark ms-1">{{ $groupMeasurements->count() }}</span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>

                        <div class="tab-content">
                            @foreach($groupedMeasurements as $groupName => $groupMeasurements)
                                <div class="tab-pane fade @if($loop->first) show active @endif"
                                     id="{{ Str::slug($analysisTypeName) }}-{{ Str::slug($groupName) }}-tab">
                                    <div class="table-responsive">
                                        <table class="table table-bordered measurement-table">
                                            <thead>
                                                <tr>
                                                    <th>Measurement</th>
                                                    <th class="d-none d-md-table-cell">Supported Auto-Detection</th>
                                                    <th>Unit</th>
                                                </tr>
                                            </thead>
                                            <tbody style="background-color: white;">
                                                @php if ($loop->first) $groupMeasurements = $groupMeasurements->merge($staticMeasurements); @endphp
                                                @foreach ($groupMeasurements as $measurement)
                                                    <tr>
                                                        <td><strong>{{ $measurement->display_name }}</strong></td>
                                                        <td class="d-none d-md-table-cell">
                                                            @if(count($measurement->synonyms ?? []) > 5)
                                                                <details>
                                                                    <summary style="cursor: pointer; user-select: none; color: #4a90e2;">
                                                                        <small>Show {{ count($measurement->synonyms) }} variants</small>
                                                                    </summary>
                                                                    <div class="mt-2 p-2 bg-light rounded">
                                                                        @foreach(collect($measurement->synonyms)->chunk(4) as $chunk)
                                                                            <div class="mb-1">
                                                                                @foreach($chunk as $synonym)
                                                                                    <code class="me-2 small" style="background-color: #e8f4f8; color: #000;">{{ $synonym }}</code>
                                                                                @endforeach
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                </details>
                                                            @else
                                                                <div>
                                                                    @foreach($measurement->synonyms ?? [] as $synonym)
                                                                        <code class="me-1 small" style="background-color: #e8f4f8; color: #000;">{{ $synonym }}</code>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td><span class="badge" style="background-color: #6c757d;">{{ $measurement->unit }}</span></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    {{-- All other analysis types (DEXA, etc.) - Simple table --}}
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered measurement-table">
                                <thead>
                                    <tr>
                                        <th>Measurement</th>
                                        <th class="d-none d-md-table-cell">Supported Auto-Detection</th>
                                        <th>Unit</th>
                                    </tr>
                                </thead>
                                <tbody style="background-color: white;">
                                    @foreach ($category_measurements->merge($staticMeasurements) as $measurement)
                                        <tr>
                                            <td><strong>{{ $measurement->display_name }}</strong></td>
                                            <td class="d-none d-md-table-cell">
                                                <div>
                                                    @foreach($measurement->synonyms ?? [] as $synonym)
                                                        <code class="me-1 small" style="background-color: #e8f4f8; color: #000;">{{ $synonym }}</code>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td><span class="badge" style="background-color: #6c757d;">{{ $measurement->unit }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
            @endif {{-- Close the new if statement --}}
        @endforeach
    </div>
</div>
@endsection
