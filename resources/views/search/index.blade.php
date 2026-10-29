@extends('layout')

@section('title', 'Search Studies')
@section('description', 'Search published rodent skeletal phenotyping studies in ROSSA by title, investigator, gene, disease, or identifier.')

@section('content')

<div class="container py-5">

    {{-- Search header --}}
    <div class="text-center p-4 p-md-5 mb-4" style="background: linear-gradient(135deg, #0D3B52 0%, #1A6C8C 55%, #2E9BB5 100%); border-radius: 0.5rem;">
        <h1 class="mb-2" style="color: #fff; font-weight: 600; font-size: 2rem;">Search ROSSA Studies</h1>
        <p class="mb-4" style="color: #DCEEF3; font-size: 0.9375rem;">
            Search by identifier, title, investigators, genes, diseases, molecules, or tags
        </p>
        <form method="GET" action="{{ route('search.index') }}" class="mx-auto" style="max-width: 620px;">
            <div class="input-group input-group-lg">
                <input
                    type="text"
                    name="q"
                    class="form-control"
                    placeholder="e.g. Runx2, ROSSA-20260724-A7DF, microCT"
                    value="{{ $query ?? '' }}"
                    style="border: none;"
                >
                <button type="submit" class="btn fw-semibold" style="background-color: #E8C46A; color: #000; border: none;">
                    Search
                </button>
            </div>
        </form>
    </div>

    {{-- Result count / recent submissions heading --}}
    <div class="d-flex align-items-baseline gap-2 mb-3">
        @if(isset($query) && $query)
            <h2 class="h5 mb-0" style="color: #2C2C2A;">
                {{ $studies->count() }} {{ Str::plural('result', $studies->count()) }} for &ldquo;{{ $query }}&rdquo;
            </h2>
        @else
            <h2 class="h5 mb-0" style="color: #2C2C2A;">Recent Submissions</h2>
            <span style="font-size: 0.8125rem; color: #5F5E5A;">{{ $studies->count() }} most recent approved {{ Str::plural('study', $studies->count()) }}</span>
        @endif
    </div>

    {{-- Results --}}
    @if($studies->count() > 0)
        <div class="d-flex flex-column gap-2">
            @foreach($studies as $study)
                @php
                    $modalities = $study->analysisUploads
                        ->pluck('analysisType.name')
                        ->filter()
                        ->unique();
                    $sexes = $study->sampleGroups->pluck('sex')->filter()->unique()->sort();
                    $ages = $study->sampleGroups->pluck('age')->filter()->unique()->sort();
                    $strains = $study->animalModels
                        ->map(fn ($model) => $model->experimentalStrainLabel())
                        ->filter()
                        ->unique();
                    $genes = $study->animalModels->pluck('gene.symbol')->filter()->unique();
                @endphp

                <div class="search-result-card">
                    <div class="d-flex justify-content-between align-items-baseline flex-wrap gap-2 mb-2">
                        <div>
                            <a href="{{ !$study->isEmbargoed() ? route('search.show', $study) : '#' }}" class="title">
                                {{ $study->title }}
                            </a>
                            @if($study->isEmbargoed())
                                <span class="badge bg-warning text-dark ms-2">Embargoed</span>
                            @endif
                        </div>
                        <span class="identifier">{{ $study->identifier }}</span>
                    </div>

                    @if($study->summary)
                        <p class="mb-2" style="font-size: 0.875rem;">{{ Str::limit($study->summary, 150) }}</p>
                    @endif

                    <div class="facts">
                        @if($study->investigators->isNotEmpty())
                            <span>
                                <strong>Investigators</strong>
                                {{ $study->investigators->map(fn($i) => $i->first_name . ' ' . $i->last_name)->take(3)->join(', ') }}
                                @if($study->investigators->count() > 3) (+{{ $study->investigators->count() - 3 }} more) @endif
                            </span>
                        @endif

                        @if(!$study->isEmbargoed())
                            @if($genes->isNotEmpty())
                                <span><strong>Gene</strong> {{ $genes->take(3)->join(', ') }}@if($genes->count() > 3) (+{{ $genes->count() - 3 }}) @endif</span>
                            @endif

                            @if($modalities->isNotEmpty())
                                <span><strong>Modality</strong> {{ $modalities->join(', ') }}</span>
                            @endif

                            @if($sexes->isNotEmpty() || $ages->isNotEmpty())
                                <span>
                                    <strong>Sex/Age</strong>
                                    {{ $sexes->join('/') }}
                                    @if($ages->isNotEmpty()) &middot; {{ $ages->join(', ') }}wk @endif
                                </span>
                            @endif

                            @if($study->diseaseLines->isNotEmpty())
                                <span><strong>Disease</strong> {{ $study->diseaseLines->pluck('label')->take(2)->join(', ') }}</span>
                            @endif
                        @endif
                    </div>

                    @if($study->doi || $study->pubmed_id)
                        <div class="mt-2" style="font-size: 0.75rem;">
                            @if($study->doi)
                                DOI: <a href="{{ $study->doiLink() }}" target="_blank">{{ $study->doi }}</a>
                            @endif
                            @if($study->pubmed_id)
                                &nbsp;&middot;&nbsp;PMID: <a href="{{ $study->pubmedLink() }}" target="_blank">{{ $study->pubmed_id }}</a>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
        <div class="mt-4">
        {{ $studies->links() }}
        </div>
    @else
        <div class="alert alert-info">
            No studies found matching your search criteria.
        </div>
    @endif

</div>
@endsection