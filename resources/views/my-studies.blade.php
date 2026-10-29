@extends('layout')
@section('content')
<div class="container">
    <div class="py-4">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <h2 class="mb-0">Your Studies</h2>
            <a href="{{ route('data-submission') }}" class="btn btn-primary mt-3">+ Create New Study</a>
        </div>

        @if($studies->count() > 0)
            <div class="table-responsive">
                <table class="table">
                <thead>
                    <tr>
                        <th>Study Identifier</th>
                        <th class="d-none d-md-table-cell">Study Title</th>
                        <th>Initiated On</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($studies as $study)
                        <tr>
                            <td class="text-nowrap">{{ $study->identifier }}</td>
                            <td class="d-none d-md-table-cell">{{ $study->title ?? '-' }}</td>
                            <td class="text-nowrap">{{ $study->created_at->format('Y-m-d') }}</td>
                            <td class="text-nowrap"><span class="fw-normal badge text-bg-{{ $study->status->color() }}">
                                @switch ($study->status)
                                    @case(\App\Enums\StudyStatus::APPROVED)
                                        {{ $study->isEmbargoed() ? 'Approved and Under Embargo' : 'Approved and Publicly Shared' }}
                                        @break
                                    @default
                                         {{ $study->status->label() }}
                                @endswitch
                            </span></td>
                            <td>
                                @php $progress = $study->progressCounts(); @endphp
                                <div class="progress" style="height: 20px; min-width: 150px;">
                                    <div @class([
                                        'progress-bar text-dark',
                                        'bg-success' => $progress['completed'] === $progress['total'],
                                        'bg-warning' => $progress['completed'] > 0 && $progress['completed'] < $progress['total'],
                                    ]) role="progressbar"
                                        style="width: {{ ($progress['completed'] / $progress['total']) * 100 }}%;"
                                        aria-valuenow="{{ ($progress['completed'] / $progress['total']) * 100 }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small>{{ $progress['completed'] }} of {{ $progress['total'] }} sections completed</small>
                            </td>
                            <td class="text-nowrap">
                                <div class="btn-group gap-2">
                                    @if($study->status === \App\Enums\StudyStatus::APPROVED)
                                        @if ($study->isEmbargoed())
                                            <a href="{{ route('study.study-information', ['study' => $study]) }}" class="btn btn-success btn-sm">
                                                Add or Edit Publication Information
                                            </a>
                                        @else
                                            <a href="{{ route('search.show', ['study' => $study]) }}" class="btn btn-success btn-sm">
                                                Completed
                                            </a>
                                        @endif
                                    @elseif ($study->status === \App\Enums\StudyStatus::IN_PROGRESS)
                                        <a href="{{ route('study.study-information', ['study' => $study->id]) }}" class="btn btn-warning btn-sm">
                                            Continue Study
                                        </a>
                                    @else
                                        <a href="{{ route('study.study-information', ['study' => $study->id]) }}" class="btn btn-warning btn-sm">
                                            View Study
                                        </a>
                                    @endif
                                    @include('study.partials.delete-study', ['study' => $study])
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-info">
                No studies found. Click "Create New Study" to get started.
            </div>
        @endif

        @isset($collections)
        @if($collections->isNotEmpty())
            <div id="my-collections" class="mt-5">
                <h2 class="mb-3">Your Collections</h2>
                <div class="table-responsive">
                    <table class="table">
                    <thead>
                        <tr>
                            <th>Collection Identifier</th>
                            <th>Status</th>
                            <th>Title</th>
                            <th>Studies</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($collections as $collection)
                            <tr>
                                <td>{{ $collection->identifier }}</td>
                                <td class="text-nowrap">
                                    @switch($collection->status)
                                        @case('submitted')
                                            <span class="badge text-bg-warning fw-normal">Awaiting Approval</span>
                                            @break
                                        @case('approved')
                                            <span class="badge text-bg-success fw-normal">Approved</span>
                                            @break
                                        @default
                                            <span class="badge text-bg-secondary fw-normal">Draft</span>
                                    @endswitch
                                </td>
                                <td>{{ $collection->title ?? '-' }}</td>
                                <td>
                                    @include('collections.partials.collection-status-counts', ['collection' => $collection])
                                </td>
                                <td class="text-nowrap">
                                    <a href="{{ route('my-collections.studies', $collection) }}"
                                        class="btn btn-warning btn-sm">View Studies</a>
                                    <a href="{{ route('collections.show', ['collection' => $collection, 'from' => 'investigator']) }}"
                                        class="btn btn-outline-primary btn-sm">
                                        View Report
                                    </a>
                                    @if($collection->canUserView(auth()->user()))
                                        <a href="{{ route('my-collections.edit', $collection) }}" class="btn btn-outline-primary btn-sm">
                                            {{ $collection->canUserEditMetadata(auth()->user()) ? 'Edit' : 'Information' }}
                                        </a>
                                    @endif
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endisset
    </div>
</div>
@endsection
