@extends('layout')
@section('title', $collection->title ?? $collection->identifier)
@section('content')
<div class="container">
    <div class="py-4">

        @if($isPreview && $collection->status !== 'approved')
            <div class="alert alert-warning mb-4">
                <strong>Preview.</strong> This collection is not yet approved and is not publicly
                visible. You're seeing this because you have access to manage or edit it.
            </div>
        @endif

        @php
            $viewer = auth()->user();
            $canOpenEditor = $viewer && $collection->canUserView($viewer);
            $editorRoute = ($navContext ?? 'admin') === 'admin'
                ? route('admin.collections.edit', $collection)
                : route('my-collections.edit', $collection);
        @endphp

        @if($canOpenEditor)
            <p class="text-muted small mb-4">
                Public report. Visitors only see this page, with no save, edit, or delete controls.
                <a href="{{ $editorRoute }}">
                    {{ $collection->canUserEditMetadata($viewer) ? 'Open the collection editor' : 'Review collection information' }}
                </a>
            </p>
        @endif

        {{-- Collection Information — mirrors the admin form's layout/order --}}
        <div class="card mb-4" style="border-left: 4px solid var(--bs-primary);">
            <div class="card-body">
                <h3 class="mb-1">Collection Information</h3>
                <p class="text-muted small mb-3">Collection ID: {{ $collection->identifier }}</p>

                <div class="mb-3">
                    <h6 class="mb-1 fw-bold">Collection Title</h6>
                    <p class="mb-0">{{ $collection->title ?? '-' }}</p>
                </div>

                @if($collection->summary)
                    <div class="mb-3">
                        <h6 class="mb-1 fw-bold">Collection Summary</h6>
                        <p class="mb-0">{{ $collection->summary }}</p>
                    </div>
                @endif

                @if($collection->funding_sources)
                    <div class="mb-3">
                        <h6 class="mb-1 fw-bold">Funding Sources</h6>
                        <p class="mb-0">{{ $collection->funding_sources }}</p>
                    </div>
                @endif

                @if($collection->conflicts)
                    <div class="mb-3">
                        <h6 class="mb-1 fw-bold">Conflicts of Interest</h6>
                        <p class="mb-0">{{ $collection->conflicts }}</p>
                    </div>
                @endif

                @if($collection->completion_date)
                    <div class="mb-0">
                        <h6 class="mb-1 fw-bold">Collection Completion Date</h6>
                        <p class="mb-0">{{ $collection->completion_date->format('F j, Y') }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Publications — table, matching the admin form's columns --}}
        @if($collection->publications->isNotEmpty())
            <div class="card mb-4" style="border-left: 4px solid var(--bs-primary);">
                <div class="card-body">
                    <h3 class="mb-3">Publications</h3>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>DOI</th>
                                    <th>PubMed ID</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($collection->publications as $publication)
                                    <tr>
                                        <td>{{ $publication->title ?? '-' }}</td>
                                        <td>
                                            @if($publication->doi)
                                                <a href="https://doi.org/{{ $publication->doi }}" target="_blank">{{ $publication->doi }}</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($publication->pubmed_id)
                                                <a href="https://pubmed.ncbi.nlm.nih.gov/{{ $publication->pubmed_id }}" target="_blank">{{ $publication->pubmed_id }}</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- Investigators — table, matching the admin form's columns minus Email
             (this page is public; omitting addresses by default). --}}
        @if($collection->investigators->isNotEmpty())
            <div class="card mb-4" style="border-left: 4px solid var(--bs-primary);">
                <div class="card-body">
                    <h3 class="mb-3">Investigators</h3>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Organization</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($collection->investigators as $investigator)
                                    <tr>
                                        <td>{{ $investigator->first_name }} {{ $investigator->last_name }}</td>
                                        <td>{{ $investigator->organization ?? '-' }}</td>
                                        <td>
                                            <x-investigator-role-badges :labels="$investigator->roleLabels()" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- Studies in this collection --}}
        <div class="card" style="border-left: 4px solid var(--bs-primary);">
            <div class="card-body">
                <h3 class="mb-3">Studies in this Collection ({{ $studies->count() }})</h3>

                @if($studies->isEmpty())
                    <p class="text-muted mb-0">
                        No studies from this collection have been approved yet.
                    </p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Study Identifier</th>
                                    <th>Gene</th>
                                    <th class="d-none d-lg-table-cell">Animal Model</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($studies as $study)
                                    @php
                                        $ko = $study->animalModels->firstWhere('gene_id', '!=', null);
                                    @endphp
                                    <tr>
                                        <td class="text-nowrap">
                                            {{ $study->identifier }}
                                            <span class="d-block small text-muted">{{ $study->title ?? '-' }}</span>
                                        </td>
                                        <td class="text-nowrap">{{ $ko?->gene?->symbol ?? '-' }}</td>
                                        <td class="d-none d-lg-table-cell small">{{ $ko?->animal_model_name ?? '-' }}</td>
                                        <td class="text-nowrap text-end">
                                            @if($study->isPubliclyVisible())
                                                <a href="{{ route('search.show', ['study' => $study->identifier]) }}"
                                                    class="btn btn-outline-primary btn-sm">
                                                    View
                                                </a>
                                            @elseif($isPreview)
                                                <a href="{{ route('study.summary', $study) }}"
                                                    class="btn btn-outline-primary btn-sm">
                                                    View
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
