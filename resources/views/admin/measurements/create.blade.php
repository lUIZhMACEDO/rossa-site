@extends('layout')

@section('content')
<div class="container pt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Create New Measurement</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.measurements') }}">Measurements</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.measurements') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Measurements
        </a>
    </div>

    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> 
        <strong>Creating a New Measurement</strong>
        <p class="mb-0 mt-2">
            Add a new measurement parameter to the ROSSA system. Once created, the system name and analysis type cannot be changed, 
            but you can always update the display name, unit, and synonyms.
        </p>
    </div>

    <form action="{{ route('admin.measurements.store') }}" method="POST">
        @csrf

        @include('admin.measurements.partials.form', [
            'measurement' => null,
            'analysisTypes' => $analysisTypes,
            'isEdit' => false
        ])

        <div class="d-flex justify-content-between mt-4">
            <a href="{{ route('admin.measurements') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Create Measurement
            </button>
        </div>
    </form>
</div>
@endsection
