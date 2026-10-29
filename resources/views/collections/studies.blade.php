@extends('layout')
@section('title', 'Collection Studies')
@section('content')
<div class="container">
    <div class="py-4">
        <div class="d-flex justify-content-between align-items-end mb-2">
            <div>
                <h2 class="mb-0">Studies in this Collection</h2>
                <p class="text-muted mb-0">
                    {{ $collection->identifier }}
                    @if($collection->title) &middot; {{ $collection->title }} @endif
                </p>
            </div>
        </div>

        @include('collections.partials.collection-nav', ['collection' => $collection, 'navContext' => 'admin'])

        {{-- Collection status / approval --}}
        <div class="alert d-flex justify-content-between align-items-center gap-3
            @switch($collection->status)
                @case('submitted') alert-warning @break
                @case('approved') alert-success @break
                @default alert-secondary
            @endswitch
        ">
            <div>
                @switch($collection->status)
                    @case('draft')
                        <strong>Draft.</strong> Review the studies below, then submit the collection when it is ready for ROSSA review.
                        @break
                    @case('submitted')
                        <strong>Submitted.</strong> This collection is awaiting ROSSA approval.
                        @if(($approvalBlockerCount ?? 0) > 0)
                            <span class="d-block small mt-1">
                                <strong>{{ $approvalBlockerCount }}</strong> studies are not yet submitted and block final approval.
                            </span>
                        @elseif(($pendingStudyCount ?? 0) > 0)
                            <span class="d-block small mt-1">
                                Check pending studies below and click <strong>Approve Selected</strong>,
                                or click <strong>Approve Collection</strong> to approve every pending study and publish the collection.
                            </span>
                        @else
                            <span class="d-block small mt-1">
                                All studies are approved. Click <strong>Approve Collection</strong> to publish it.
                            </span>
                        @endif
                        @break
                    @case('approved')
                        <strong>Approved.</strong> All studies in this collection are public.
                        @break
                    @default
                        <strong>Draft.</strong> Review the studies below, then submit the collection when it is ready for ROSSA review.
                @endswitch
            </div>
            <div class="d-flex flex-shrink-0 gap-2">
                @if($collection->status === 'draft')
                    <form action="{{ route('admin.collections.submit', $collection) }}" method="POST"
                        onsubmit="return confirm('Submit this collection for review? Every study must already be ready.');">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">Submit Collection</button>
                    </form>
                @elseif($collection->status === 'submitted')
                    @if(($pendingStudyCount ?? 0) > 0)
                        <button type="submit" class="btn btn-outline-success btn-sm"
                            form="approve-selected-studies-form">
                            Approve Selected
                        </button>
                    @endif
                    @if($canApproveCollection ?? false)
                        <form action="{{ route('admin.collections.approve', $collection) }}" method="POST"
                            onsubmit="return confirm('Approve this collection? Every submitted study will become publicly accessible.');">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">Approve Collection</button>
                        </form>
                    @else
                        <button type="button" class="btn btn-success btn-sm" disabled
                            title="All studies must be submitted before the collection can be approved">
                            Approve Collection
                        </button>
                    @endif
                @endif
            </div>
        </div>

        <div class="mb-3">
            @include('collections.partials.collection-status-counts', ['collection' => $collection])
        </div>

        {{-- Data coverage --}}
        @php $missing = $totalStudies - $studiesWithData; @endphp
        <div class="alert {{ $missing === 0 ? 'alert-success' : 'alert-warning' }} d-flex justify-content-between align-items-center">
            <div>
                <strong>{{ $studiesWithData }}</strong> of <strong>{{ $totalStudies }}</strong> studies have uploaded data.
                @if($missing > 0)
                    <strong>{{ $missing }}</strong> still have none.
                @endif
            </div>
            <div>
                @if(request('missing_data'))
                    <a href="{{ route('admin.collections.studies', $collection) }}" class="btn btn-sm btn-outline-secondary">
                        Show all
                    </a>
                @elseif($missing > 0)
                    <a href="{{ route('admin.collections.studies', ['collection' => $collection, 'missing_data' => 1]) }}"
                        class="btn btn-sm btn-outline-secondary">
                        Show only those missing data
                    </a>
                @endif
            </div>
        </div>

        @if($studies->count() > 0)
            @if($collection->status === 'submitted' && ($pendingStudyCount ?? 0) > 0)
                <form id="approve-selected-studies-form" action="{{ route('admin.collections.approve-studies', $collection) }}" method="POST"
                    onsubmit="return confirm('Approve the selected pending studies? The collection will remain awaiting final approval.');">
                    @csrf
                </form>
            @endif
            <table class="table">
                <thead>
                    <tr>
                        @if($collection->status === 'submitted')
                            <th>
                                @if(($pendingStudyCount ?? 0) > 0)
                                    <input type="checkbox" class="form-check-input mt-0"
                                        id="select-all-pending"
                                        title="Select all pending studies on this page"
                                        aria-label="Select all pending studies on this page"
                                        style="width: 1.35rem; height: 1.35rem;">
                                @endif
                            </th>
                        @endif
                        <th>Study Identifier</th>
                        <th>Status</th>
                        <th>Gene</th>
                        <th class="d-none d-lg-table-cell">Animal Model</th>
                        <th>Data</th>
                        <th>Groups</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($studies as $study)
                        @php
                            $model = $animalModels[$study->id] ?? null;
                            $uploads = $uploadCounts[$study->id] ?? 0;
                            $groups = $sampleGroups[$study->id] ?? collect();
                        @endphp
                        <tr>
                            @if($collection->status === 'submitted')
                                <td class="text-nowrap">
                                    @if($study->status === \App\Enums\StudyStatus::PENDING)
                                        <input type="checkbox"
                                            class="form-check-input mt-0 js-pending-study"
                                            form="approve-selected-studies-form"
                                            name="study_ids[]"
                                            value="{{ $study->id }}"
                                            style="width: 1.35rem; height: 1.35rem;">
                                    @endif
                                </td>
                            @endif
                            <td class="text-nowrap">
                                {{ $study->identifier }}
                                <span class="d-block small text-muted">{{ $study->title ?? '-' }}</span>
                            </td>
                            <td class="text-nowrap">
                                <span class="badge text-bg-{{ $study->status->color() }} fw-normal">
                                    {{ $study->status->label() }}
                                </span>
                            </td>
                            <td class="text-nowrap">{{ $model->gene_symbol ?? '-' }}</td>
                            <td class="d-none d-lg-table-cell small">{{ $model->animal_model_name ?? '-' }}</td>
                            <td class="text-nowrap">
                                @if($uploads > 0)
                                    <span class="badge text-bg-success fw-normal">{{ $uploads }}</span>
                                @else
                                    <span class="badge text-bg-secondary fw-normal">none</span>
                                @endif
                            </td>
                            <td class="text-nowrap">
                                @if($groups->isNotEmpty())
                                    <button class="btn btn-sm btn-link p-0" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#groups-{{ $study->id }}">
                                        {{ $groups->count() }} <i class="bi bi-chevron-down small"></i>
                                    </button>
                                @else
                                    <span class="badge text-bg-secondary fw-normal">none</span>
                                @endif
                            </td>
                            <td class="text-nowrap">
                                <div class="btn-group gap-2">
                                    <a href="{{ route('study.study-information', ['study' => $study->id]) }}"
                                        class="btn btn-warning btn-sm">
                                        Open
                                    </a>
                                    <form action="{{ route('admin.collections.studies.destroy', ['collection' => $collection, 'study' => $study]) }}"
                                        method="POST"
                                        onsubmit="return confirm('Delete {{ $study->identifier }}? Any data attached to this study goes with it.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @if($groups->isNotEmpty())
                            <tr class="collapse" id="groups-{{ $study->id }}">
                                <td colspan="{{ $collection->status === 'submitted' ? 8 : 7 }}" class="bg-light">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>Sample Group</th>
                                                <th>Animal Model</th>
                                                <th>Sex</th>
                                                <th>Genotype</th>
                                                <th>Age (wk)</th>
                                                <th>Type</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($groups as $g)
                                                <tr>
                                                    <td>{{ $g->label }}</td>
                                                    <td class="small">{{ $g->animal_model_name }}</td>
                                                    <td>{{ $g->sex }}</td>
                                                    <td>{{ $g->genotype }}</td>
                                                    <td>{{ $g->age }}</td>
                                                    <td>
                                                        @if($g->control)
                                                            <span class="badge text-bg-info fw-normal">Control</span>
                                                        @else
                                                            <span class="badge text-bg-dark fw-normal">Experimental</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>

            @if($collection->status === 'submitted' && ($pendingStudyCount ?? 0) > 0)
                <div class="d-flex justify-content-end mb-3">
                    <button type="submit" class="btn btn-outline-success btn-sm"
                        form="approve-selected-studies-form">
                        Approve Selected
                    </button>
                </div>
            @endif

            {{ $studies->links() }}
        @else
            <div class="alert alert-info">
                @if(request('missing_data'))
                    Every study in this collection has data.
                @else
                    No studies in this collection yet. Use the animal model importer to add them.
                @endif
            </div>
        @endif
    </div>
</div>
@if($collection->status === 'submitted' && ($pendingStudyCount ?? 0) > 0)
    <script>
        document.getElementById('select-all-pending')?.addEventListener('change', function () {
            document.querySelectorAll('.js-pending-study').forEach((checkbox) => {
                checkbox.checked = this.checked;
            });
        });
    </script>
@endif
@endsection
