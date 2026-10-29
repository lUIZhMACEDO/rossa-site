@extends('layout')

@section('content')
<div class="container pt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Edit Measurement</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.measurements') }}">Measurements</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $measurement->display_name }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.measurements') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Measurements
        </a>
    </div>

    {{-- Measurement Usage Info --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-info-circle"></i> Measurement Usage</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Database Usage</h6>
                    <p class="mb-2">
                        <strong>Measurement Values:</strong> 
                        <span class="badge bg-primary fs-6">{{ $measurementValuesCount }}</span>
                    </p>
                    @if($measurementValuesCount > 0)
                        <p class="text-muted small">
                            <i class="bi bi-exclamation-triangle"></i> 
                            This measurement is currently used in {{ $measurementValuesCount }} measurement value(s). 
                            Changes to the display name and unit will affect how this data is presented.
                        </p>
                    @else
                        <p class="text-muted small">
                            <i class="bi bi-check-circle"></i> 
                            This measurement is not currently used in any measurement values.
                        </p>
                    @endif

                    @if ( $measurement->study_id )
                        <p class="text-muted small">
                            <i class="bi bi-info-circle"></i> 
                            This is a study-specific measurement and is not considered an official measurement in the ROSSA system.
                        </p>
                        <p>Associated with Study ID: {{ $measurement->study->identifier }}</p>
                    @endif
                </div>
                <div class="col-md-6">
                    <h6>Measurement Information</h6>
                    <p class="mb-1"><strong>System Name:</strong> <code>{{ $measurement->name }}</code></p>
                    <p class="mb-1">
                        <strong>Analysis Type:</strong> 
                        <span class="badge bg-secondary">{{ $measurement->analysisType?->name ?? 'Universal' }}</span>
                    </p>
                    <p class="mb-1">
                        <strong>Created:</strong> {{ $measurement->created_at->format('M d, Y') }}
                    </p>
                    <p class="mb-1">
                        <strong>Last Updated:</strong> {{ $measurement->updated_at->format('M d, Y') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Form --}}
    <form action="{{ route('admin.measurements.update', $measurement) }}" method="POST">
        @csrf
        @method('PUT')

        @include('admin.measurements.partials.form', [
            'measurement' => $measurement,
            'analysisTypes' => $analysisTypes,
            'isEdit' => true
        ])

        <div class="d-flex justify-content-between mt-4">
            <a href="{{ route('admin.measurements') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Update Measurement
            </button>
        </div>
    </form>

    {{-- Danger Zone --}}
    @if($measurementValuesCount === 0 && !$measurement->study_id)
    <div class="card border-danger mt-5">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Danger Zone</h5>
        </div>
        <div class="card-body">
            <p>Delete this measurement permanently. This action cannot be undone.</p>
            <form action="{{ route('admin.measurements.destroy', $measurement) }}" method="POST" 
                  onsubmit="return confirm('Are you sure you want to delete this measurement? This action cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-trash"></i> Delete Measurement
                </button>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection
