@extends('layout')

@section('content')
<div class="py-5">
    <div class="container mt-4">
        <h1>Studies Admin</h1>
        <p class="text-muted">Manage and review research studies</p>
        
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Search and Filter Controls -->

        <form method="GET" action="{{ route('admin.studies') }}" class="" id="searchForm">
        </form>
        <div class="row mb-4">
            <div class="col-md-6 d-flex align-items-end mb-2">
                <input type="text" name="search" class="form-control" form="searchForm"
                        placeholder="Search studies by title, identifier, summary, user, or investigators..." 
                        value="{{ request('search') }}">
                <button type="submit" class="btn btn-outline-primary ms-2" form="searchForm">Search</button>
                @if(request('search') || (request('status') && request('status') !== 'all') || (request('embargo') && request('embargo') !== 'all'))
                    <a href="{{ route('admin.studies') }}" class="btn btn-outline-secondary ms-2">Clear</a>
                @endif
            </div>
            <div class="col-lg-3 mb-2">
                <div class="d-flex flex-column gap-2">
                    <label class="form-label">Status</label>
                    <select class="form-select w-auto" 
                            name="status" 
                            form="searchForm"
                            onchange="this.form.submit()">
                        <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>All Studies</option>
                        <option value="{{ $abandonedFilterValue }}" {{ request('status') === $abandonedFilterValue ? 'selected' : '' }}>
                            Abandoned Drafts ({{ $abandonedDays }}+ days)
                        </option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-3 mb-2">
                <div class="d-flex flex-column gap-2 mt-3 mt-lg-0">
                    <label class="form-label">Embargo</label>
                    <select class="form-select w-auto"
                            name="embargo"
                            form="searchForm"
                            onchange="this.form.submit()">
                        <option value="all" {{ (request('embargo', 'all') === 'all') ? 'selected' : '' }}>All Studies</option>
                        <option value="embargoed" {{ request('embargo') === 'embargoed' ? 'selected' : '' }}>Embargoed Only</option>
                        <option value="not_embargoed" {{ request('embargo') === 'not_embargoed' ? 'selected' : '' }}>Not Embargoed</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Results Summary -->
        <div class="row mb-3">
            <div class="col-12">
                @if(request('search') || (request('status') && request('status') !== 'all') || (request('embargo') && request('embargo') !== 'all'))
                    <small class="text-muted">
                        Showing {{ $studies->total() }} result(s)
                        @if(request('search'))
                            for "{{ request('search') }}"
                        @endif
                        @if(request('status') && request('status') !== 'all')
                            @php
                                $statusValue = request('status');
                                $statusEnum = collect($statuses)->firstWhere('value', $statusValue);
                            @endphp
                            with status "{{ $statusEnum ? $statusEnum->label() : ucfirst(str_replace('_', ' ', request('status'))) }}"
                        @endif
                        @if(request('embargo') === 'embargoed')
                            (embargoed only)
                        @elseif(request('embargo') === 'not_embargoed')
                            (not embargoed)
                        @endif
                    </small>
                @else
                    <small class="text-muted">
                        Showing {{ $studies->total() }} total studies
                    </small>
                @endif
            </div>
        </div>

        @if(request('status') === $abandonedFilterValue)
            <div class="alert alert-warning">
                Showing in-progress studies with no recorded study activity for at least {{ $abandonedDays }} days. Delete actions are available on the study review page.
            </div>
        @endif

        <!-- Studies Content -->
        <div id="studies">
            @include('admin.partials.studies', ['studies' => $studies])
        </div>
    </div>
</div>
@endsection