@extends('layout')
@section('title', 'Your Collections')
@section('content')
<div class="container">
    <div class="py-4">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <h2 class="mb-0">Your Collections</h2>
            <form action="{{ route('admin.collections.store') }}" method="POST" class="mt-3">
                @csrf
                <button type="submit" class="btn btn-primary">+ Create New Collection</button>
            </form>
        </div>

        @if($collections->count() > 0)
            <table class="table">
                <thead>
                    <tr>
                        <th>Collection Identifier</th>
                        <th>Status</th>
                        <th>Investigator</th>
                        <th class="d-none d-md-table-cell">Collection Title</th>
                        <th>Created On</th>
                        <th>Studies</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($collections as $collection)
                        <tr>
                            <td class="text-nowrap">{{ $collection->identifier }}</td>
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
                            <td class="text-nowrap">
                                @php
                                    // Prefer the corresponding investigator, otherwise show whoever is first
                                    $lead = $collection->investigators->firstWhere('corresponding', true)
                                        ?? $collection->investigators->first();
                                @endphp
                                @if($lead)
                                    {{ $lead->first_name }} {{ $lead->last_name }}
                                    @if($collection->investigators->count() > 1)
                                        <span class="text-muted small">
                                            +{{ $collection->investigators->count() - 1 }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="d-none d-md-table-cell">{{ $collection->title ?? '-' }}</td>
                            <td class="text-nowrap">{{ $collection->created_at->format('Y-m-d') }}</td>
                            <td>
                                @include('collections.partials.collection-status-counts', ['collection' => $collection])
                            </td>
                            <td class="text-nowrap">
                                <div class="btn-group gap-2">
                                    <a href="{{ route('admin.collections.edit', $collection) }}" class="btn btn-warning btn-sm">
                                        Edit Form
                                    </a>
                                    <a href="{{ route('admin.collections.studies', $collection) }}"
                                        class="btn btn-sm {{ $collection->status === 'submitted' ? 'btn-success' : 'btn-outline-primary' }}">
                                        {{ $collection->status === 'submitted' ? 'Review & Approve' : 'View Studies' }}
                                    </a>
                                    <a href="{{ route('collections.show', ['collection' => $collection, 'from' => 'admin']) }}" class="btn btn-outline-primary btn-sm">
                                        View Report
                                    </a>
                                    @if($collection->studies_count === 0)
                                        <form action="{{ route('admin.collections.destroy', $collection) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this collection? This can only be done while it has no studies attached.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="alert alert-info">
                No collections found. Click "Create New Collection" to get started.
            </div>
        @endif
    </div>
</div>
@endsection
