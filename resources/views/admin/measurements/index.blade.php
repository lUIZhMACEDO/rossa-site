@extends('layout')

@section('content')
<div class="container pt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1>ROSSA Measurements</h1>
            <p>View and manage all measurements in the database.</p>
        </div>
        <a href="{{ route('admin.measurements.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Create New Measurement
        </a>
    </div>

    <form action="">
        <div class="card" style="border-left: 4px solid var(--bs-primary);">
            <div class="card-body">
                <h5 class="card-title">Filter Measurements</h5>
                <div class="d-flex g-3 align-items-start mb-3">
                    <div class="flex-grow-1 me-2">
                        <label for="measurementName" class="form-label">Measurement Name</label>
                        <input type="text" class="form-control" id="measurementName" name="measurement_name" value="{{ request('measurement_name') }}" placeholder="Filter by measurement name...">

                        <div class="form-check mt-2">
                            <input 
                                class="form-check-input" 
                                type="checkbox" 
                                value="1" 
                                id="excludeStudyMeasurements" 
                                name="exclude_study_measurements" 
                                @if(request()->has('exclude_study_measurements')) checked @endif
                            >
                            <label class="form-check-label" for="excludeStudyMeasurements">
                                Exclude User-Created (Study-Specific) Measurements
                            </label>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <label for="analysisType" class="form-label">Filter Analysis Type</label>
                        <select class="form-select" id="analysisType" name="analysis_type_id">
                            <option value="" selected>Choose...</option>
                            <option value="null" @selected(request('analysis_type_id') === 'null')>Universal (All Analysis Types)</option>
                            @foreach($analysisTypes as $analysisType)
                                <option value="{{ $analysisType->id }}" @selected(request('analysis_type_id') == $analysisType->id)>{{ $analysisType->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    
                </div>

                
                <div class="d-flex justify-content-between align-items-end">
                    <div>
                        @if ( request()->hasAny(['measurement_name', 'analysis_type_id', 'exclude_study_measurements']) )
                            <a href="{{ route('admin.measurements') }}" class="btn btn-secondary btn-sm">Clear Filters</a>
                        @endif
                    </div>
                    <button class="btn btn-primary ms-3">Filter</button>
                </div>
            </div>
        </div>
    </form>

    <div class="table-responsive mt-5">
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Analysis Type</th>
                    <th>Unit</th>
                    {{-- <th>Synonyms</th> --}}
                    <th>Official</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($measurements as $ms)
                    <tr @class(['table-secondary' => $ms->study_id])>
                        <td>{{ $ms->display_name }}</td>
                        <td>
                            {{ $ms->analysisType?->name ?: 'Universal' }}
                            @if ($ms->specimenType)
                                <br>
                                <span class="text-muted small">{{ $ms->specimenType?->name }}</span>
                            @endif
                        </td>
                        <td>{{ $ms->unit }}</td>
                        {{-- <td>{{ implode(', ', $ms->synonyms ?? []) }}</td> --}}
                        <td>
                            @if (!$ms->study_id)
                                <span class="badge bg-success">Yes</span>
                            @else
                                <span class="badge bg-warning text-dark">No</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.measurements.edit', $ms) }}" class="btn btn-sm btn-primary">
                                @if ($ms->study_id)
                                    <i class="bi bi-lock"></i> View
                                @else
                                    <i class="bi bi-pencil"></i> Edit
                                @endif
                            </a>
                        </td>
                    </tr>
                @endforeach

                @if ($measurements->isEmpty())
                    <tr>
                        <td colspan="100%" class="text-center text-muted">
                            <i class="bi bi-info-circle"></i> No measurements found matching the filter criteria.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>


    

</div>
@endsection