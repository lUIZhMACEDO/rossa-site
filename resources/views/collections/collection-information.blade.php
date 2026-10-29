@extends('layout')
@section('title', 'Collection Information')
@section('content')
<div class="container">
<div class="pt-4 pb-5">

    @if($collection->exists)
        @include('collections.partials.collection-nav', ['collection' => $collection, 'navContext' => 'admin'])
    @endif

    <div class="card mb-4" style="border-left: 4px solid var(--bs-primary);">
        <div class="card-body">
            <div class="card-title">
                <h3 class="mb-0">Collection Information</h3>
                @if($collection->exists && $collection->identifier)
                    <p class="text-muted small mb-0">Collection ID: {{ $collection->identifier }}</p>
                @endif
            </div>

            <form id="collection-information-form"
                method="POST"
                action="{{ $collection->exists
                    ? route('admin.collections.update', $collection)
                    : route('admin.collections.store') }}">
                @csrf
                @if($collection->exists)
                    @method('PUT')
                @endif

                @include('collections.partials.collection-form-fields', ['collection' => $collection])
            </form>
        </div>
    </div>

    <div class="d-flex justify-content-end mb-3">
        <button type="submit" class="btn btn-primary" form="collection-information-form">
            Save Changes
        </button>
    </div>

    @if($collection->exists)
        <div class="card mb-4" style="border-left: 4px solid var(--bs-primary);">
            <div class="card-body">
                <div class="card-title">
                    <h3 class="mb-0">Publications</h3>
                </div>
                <p class="text-muted small">Optional. Add a publication if one or more lines from this collection have been published.</p>
                @livewire('collections.publication-form', ['collection' => $collection])
            </div>
        </div>
        
        <div class="card mb-4" style="border-left: 4px solid var(--bs-primary);">
            <div class="card-body">
                <div class="card-title">
                    <h3 class="mb-0">Investigators</h3>
                </div>
                    @livewire('collections.collection-investigator-form', ['collection' => $collection])
            </div>
        </div>
       <div class="d-flex justify-content-end mb-3">
        <button type="submit" class="btn btn-primary" form="collection-information-form">
            Save Changes
        </button>
        </div>
        <div class="card mb-4" style="border-left: 4px solid var(--bs-primary);">
            <div class="card-body">
                <div class="card-title">
                    <h3 class="mb-0">Animal Models</h3>
                </div>
                @livewire('collections.animal-model-importer', ['collection' => $collection])
            </div>
        </div>
        <div class="card mb-4" style="border-left: 4px solid var(--bs-primary);">
            <div class="card-body">
                <div class="card-title">
                    <h3 class="mb-0">Sample Groups</h3>
                </div>
                @livewire('collections.sample-group-generator-button', ['collection' => $collection])
            </div>
        </div>
        <div class="card mb-4" style="border-left: 4px solid var(--bs-primary);">
            <div class="card-body">
                <div class="card-title">
                    <h3 class="mb-0">Comparison Groups</h3>
                </div>
                @livewire('collections.comparison-group-generator-button', ['collection' => $collection])
            </div>
        </div>
       
    @endif
    

    <div class="card mb-4" style="border-left: 4px solid var(--bs-primary);">
        <div class="card-body">
            <div class="card-title">
                <h3 class="mb-0">Measurement Data</h3>
            </div>
            @livewire('collections.measurement-importer', ['collection' => $collection])
        </div>
    </div>
    <x-session-alerts />

    <div class="mt-4 d-flex justify-content-end gap-2">
    <button type="submit" class="btn btn-primary btn-lg" form="collection-information-form">
        Save Changes
    </button>
    @if($collection->exists && $collection->studies()->exists())
        <a href="{{ route('admin.collections.studies', $collection) }}" class="btn btn-outline-primary btn-lg">
            Studies <i class="bi-arrow-right"></i>
        </a>
    @endif
    @if($collection->exists)
        <a href="{{ route('collections.show', ['collection' => $collection, 'from' => 'admin']) }}" class="btn btn-outline-primary btn-lg">
            Public report <i class="bi-arrow-right"></i>
        </a>
    @endif
</div>
</div>
@endsection
