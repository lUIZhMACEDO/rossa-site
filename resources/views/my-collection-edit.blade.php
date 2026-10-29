@extends('layout')
@section('title', 'Edit Collection')
@section('content')
<div class="container">
    <div class="py-4">
        @php
            $canEdit = $canEdit ?? $collection->canUserEditMetadata(auth()->user());
        @endphp
        <div class="d-flex justify-content-between align-items-end mb-3">
            <div>
                <h2 class="mb-0">{{ $canEdit ? 'Edit Collection Information' : 'Collection Information' }}</h2>
                <p class="text-muted mb-0">
                    {{ $collection->identifier }}
                    @if($collection->title) &middot; {{ $collection->title }} @endif
                </p>
            </div>

        </div>

        <x-session-alerts />

        @include('collections.partials.collection-nav', ['collection' => $collection, 'navContext' => 'investigator'])

        <p class="text-muted small">
            These details are shared across every study in this collection.
            @if($canEdit)
                Changes here update the information investigators and reviewers see on all of them.
            @endif
        </p>

        <div class="card mb-4" style="border-left: 4px solid var(--bs-primary);">
            <div class="card-body">
                @if($canEdit)
                    <form id="collection-edit-form" method="POST"
                        action="{{ route('my-collections.update', $collection) }}">
                        @csrf
                        @method('PUT')

                        @include('collections.partials.collection-form-fields', ['collection' => $collection])
                    </form>
                @else
                    <fieldset disabled>
                        @include('collections.partials.collection-form-fields', ['collection' => $collection])
                    </fieldset>
                @endif
            </div>
        </div>



        @if($canEdit)
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
                    <p class="text-muted small">
                        Update investigator details and roles for this collection.
                    </p>
                    @livewire('collections.collection-investigator-form', ['collection' => $collection])
                </div>
            </div>

            <div class="card mb-4" style="border-left: 4px solid var(--bs-primary);">
                <div class="card-body">
                    <div class="card-title">
                        <h3 class="mb-0">Measurement Data</h3>
                    </div>
                    @livewire('collections.measurement-importer', ['collection' => $collection, 'metadataOnly' => true])
                </div>
            </div>
        @else
            <div class="card mb-4" style="border-left: 4px solid var(--bs-primary);">
                <div class="card-body">
                    <h3 class="mb-3">Publications</h3>
                    @forelse($collection->publications as $publication)
                        <p class="mb-1">{{ $publication->title ?? $publication->doi ?? $publication->pubmed_id ?? 'Untitled publication' }}</p>
                    @empty
                        <p class="text-muted mb-0">No publications recorded.</p>
                    @endforelse
                </div>
            </div>
            <div class="card mb-4" style="border-left: 4px solid var(--bs-primary);">
                <div class="card-body">
                    <h3 class="mb-3">Investigators</h3>
                    @forelse($collection->investigators as $investigator)
                        <p class="mb-1">
                            {{ $investigator->first_name }} {{ $investigator->last_name }}
                            <x-investigator-role-badges :labels="$investigator->roleLabels()" />
                        </p>
                    @empty
                        <p class="text-muted mb-0">No investigators recorded.</p>
                    @endforelse
                </div>
            </div>
        @endif

        <div class="d-flex justify-content-end gap-2 mb-4">
            @if($canEdit)
                <button type="submit" class="btn btn-primary btn-lg" form="collection-edit-form">
                    Save Changes
                </button>
            @endif
            <a href="{{ route('my-collections.studies', $collection) }}" class="btn btn-outline-secondary btn-lg">
                Back to Studies
            </a>
        </div>
    </div>
</div>
@endsection
