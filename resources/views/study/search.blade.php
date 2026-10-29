{{-- TODO: I think this is dead code --}}

@extends('layout')

@section('title', 'Study Search')

@section('content')
<div class="container pt-5">
    <h1 class="mb-4">Search Studies</h1>
    <form action="{{ route('search-study') }}" method="GET">
        <div class="input-group">
            <input type="text" class="form-control" name="id" placeholder="Enter ROSSA ID">
            <button class="btn btn-primary" type="submit">Search</button>
        </div>
    </form>
    <div class="pt-4">
        @if (!isset($study))
            <p>Could not find an approved study with the given ROSSA ID.</p>
        @elseif ( $study && !$study->approved )
            <div class="alert alert-warning">Study was found, but is not yet fully published on ROSSA.  Please check back soon for updates.</div>
        @else
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <div>{{ $study->title }}</div>
                    <small class="fst-italic">{{ $study->identifier }}</small>
                </div>
                <div class="card-body">
                    <div class="mb-3 border-bottom">
                        <div class="mb-3">
                            <h5>Publication Information</h5>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">Publication Details</h6>
                                <div class="mb-3"><strong>Published: </strong>{{ $study->is_published ? 'Yes' : 'No' }}</div>
                                <div class="mb-3"><strong>Publication Plan: </strong>{{ $study->publication_plan ?? '-' }}</div>
                                <div class="mb-3"><strong>Embargo Period: </strong>{{ ($study->embargo_months . ' months') ?? '-' }}</div>
                                <div class="mb-3"><strong>DOI: </strong>{{ $study->doi ?? '-' }}</div>
                                <div class="mb-3"><strong>PubMed ID: </strong>{{ $study->pubmed_id ?? '-' }}</div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">Other</h6>
                                <div class="mb-3"><strong>Funding Sources: </strong>{{ $study->funding_sources ?? '-' }}</div>
                                <div class="mb-3"><strong>Conflicts of Interest: </strong>{{ $study->conflicts ?? '-' }}</div>
                                <div class="mb-3"><strong>Completion Date: </strong>{{ $study->completion_date ? $study->completion_date->format('Y-m-d') : '-' }}</div>
                                <div class="mb-3"><strong>Summary: </strong>{{ $study->summary ?? '-' }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- TODO: What info to show? --}}
                    <div class="mb-3 border-bottom">
                        <div class="mb-3">
                            <h5>General</h5>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">Info</h6>
                                <div class="mb-3"><strong>Animal Strains: </strong>{{ $study->animalLines()->pluck('label')->join(', ') }}</div>
                                <div class="mb-3"><strong>Investigators: </strong>{{ $study->investigators()->pluck('first_name')->join(', ') }}</div>
                                <div class="mb-3"><strong>Subject Areas: </strong>{{ $study->subject_areas ? implode(', ', $study->subject_areas) : '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>



@endsection