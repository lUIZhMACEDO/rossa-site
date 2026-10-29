@extends('layout')
@section('title', 'My Collection')
@section('content')
<div class="container">
    <div class="py-4">
        <div class="d-flex justify-content-between align-items-end mb-3">
            <div>
                <h2 class="mb-0">{{ $collection->title ?? $collection->identifier }}</h2>
                <p class="text-muted mb-0">
                    {{ $collection->identifier }}
                    @if($collection->title) &middot; {{ $collection->title }} @endif
                </p>
            </div>
        </div>

        @if($collection->summary)
            <p>{{ $collection->summary }}</p>
        @endif

        <x-session-alerts />

        @include('collections.partials.collection-nav', ['collection' => $collection, 'navContext' => 'investigator'])

        {{-- Readiness summary --}}
        @php
            $totalCount = $studies->count();
            $incompleteCount = count($readiness ?? []);
            $readyCount = $totalCount - $incompleteCount;
            $canSubmit = $incompleteCount === 0 && $totalCount > 0 && $collection->status === 'draft';
            $userCanSubmit = $collection->canUserSubmit(auth()->user());
            $approvedCount = $studies->filter(fn ($study) => $study->status === \App\Enums\StudyStatus::APPROVED)->count();
            $pendingCount = $studies->filter(fn ($study) => $study->status === \App\Enums\StudyStatus::PENDING)->count();
            $inProgressCount = $studies->filter(fn ($study) => $study->status === \App\Enums\StudyStatus::IN_PROGRESS)->count();
            $rejectedCount = $studies->filter(fn ($study) => $study->status === \App\Enums\StudyStatus::REJECTED)->count();
        @endphp

        @if($collection->status === 'submitted')
            <div class="alert alert-info">
                This collection has been submitted and is awaiting ROSSA review.
            </div>
        @elseif($collection->status === 'approved')
            <div class="alert alert-success">
                This collection has been approved and is publicly accessible.
            </div>
        @else
            <div class="alert {{ $canSubmit ? 'alert-success' : 'alert-warning' }} d-flex justify-content-between align-items-center">
                <div>
                    @if($canSubmit)
                        All <strong>{{ $totalCount }}</strong> studies are ready to submit.
                    @else
                        <strong>{{ $readyCount }}</strong> of <strong>{{ $totalCount }}</strong> studies are ready.
                        <strong>{{ $incompleteCount }}</strong> need attention before you can submit the collection —
                        see the "Needs" column below.
                    @endif
                </div>
                @if($userCanSubmit)
                    @if($canSubmit)
                        <form action="{{ route('my-collections.submit', $collection) }}" method="POST"
                            onsubmit="return confirm('Submit this collection for review? Every study must already be ready.');">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm">Submit Collection</button>
                        </form>
                    @else
                        <button type="button" class="btn btn-primary btn-sm" disabled
                            title="Complete all study requirements before submitting">
                            Submit Collection
                        </button>
                    @endif
                @endif
            </div>
        @endif

        <p class="text-muted small mb-1">
            {{ $totalCount }} studies in this collection:
            <span class="text-success">{{ $approvedCount }} approved</span>
            · <span class="text-warning">{{ $pendingCount }} pending</span>
            · {{ $inProgressCount }} in progress
            @if($rejectedCount > 0)
                · <span class="text-danger">{{ $rejectedCount }} need revision</span>
            @endif
        </p>
        <p class="text-muted small">
            Select a study to review its data.
        </p>

        <table class="table">
            <thead>
                <tr>
                    <th>Study Identifier</th>
                    <th>Status</th>
                    <th>Gene</th>
                    <th class="d-none d-lg-table-cell">Animal Model</th>
                    <th>Needs</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($studies as $study)
                    @php
                        $ko = $study->animalModels->firstWhere('gene_id', '!=', null);
                        $missing = $readiness[$study->id] ?? [];
                    @endphp
                    <tr>
                        <td class="text-nowrap">
                            {{ $study->identifier }}
                            <span class="d-block small text-muted">{{ $study->title ?? '-' }}</span>
                        </td>
                        <td class="text-nowrap">
                            <span class="badge text-bg-{{ $study->status->color() }} fw-normal">
                                {{ $study->status->label() }}
                            </span>
                        </td>
                        <td class="text-nowrap">{{ $ko?->gene?->symbol ?? '-' }}</td>
                        <td class="d-none d-lg-table-cell small">{{ $ko?->animal_model_name ?? '-' }}</td>
                        <td class="small">
                            @if(empty($missing))
                                <span class="badge text-bg-success fw-normal">Ready</span>
                            @else
                                @foreach($missing as $item)
                                    <a href="{{ $item['route'] }}" class="d-block text-danger small">
                                        {{ \Illuminate\Support\Str::before($item['message'], ':') }}
                                    </a>
                                @endforeach
                            @endif
                        </td>
                        <td class="text-nowrap">
                            <a href="{{ route('study.summary', ['study' => $study->id]) }}"
                                class="btn btn-outline-primary btn-sm">
                                Review
                            </a>
                            @include('study.partials.delete-study', ['study' => $study])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
