@section('title', 'Animal Models - Animal Experimentation')
@section('guide-content')
    @include('guides.data-ingestion._04_1animalexp-models')
@endsection
<x-layouts.submission :$study>
<div class="py-4" @if($study->isLockedForEditing(auth()->user())) data-input-locked @endif>
    <div class="mb-4">
        <nav aria-label="breadcrumb" style="--bs-breadcrumb-divider: '>';">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item active" >Animal Experimentation</li>
                <li class="breadcrumb-item active" aria-current="page">Animal Models</li>
            </ol>
        </nav>
        <h1>Genetic Modifications to Animal Models</h1>
        <p class="text-muted fst-italic">If your study involves genetically modified animals, please select from the appropriate category below.
             If your genetic modification does not fit any of the preconfigured categories, select "Custom Animal Model."
             If you your animal model was not genetically modified, select "No Genetic Modification."</p>
    </div>

    <form method="GET" action="{{ route('study.animal-experimentation.animal-models.create', $study) }}" class="d-inline">
        <button class="btn btn-primary mx-auto" type="submit">
            <i class="bi-plus-lg"></i> Create New Animal Model
        </button>
    </form>

    <h2 class="mb-2 mt-5">Animal Models</h2>
    <div class="table-responsive">
        <table class="table small">
            <thead>
                <tr>
                    <th>Animal ID</th>
                    <th>Species</th>
                    <th>Animal Model Name</th>
                    <th>Genetic Modification Category</th>
                    <th>Gene Modified / Description</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($animalModels as $animalModel)
                    <tr>
                        <td>{{ $animalModel->id }}</td>
                        <td>{{ $animalModel->species }}</td>
                        <td>
                            <span>{{ $animalModel->experimentalStrainLabel() }}</span>
                            @if ($animalModel->animalLine?->iri)
                                <a class="d-block text-muted small" href="{{ $animalModel->animalLine->iri }}" target="_blank">
                                    <span class="d-block text-muted small">{{ $animalModel->animalLine->iri }}</span>
                                </a>
                            @endif
                        </td>
                        <td>{{ $animalModel->geneticCategory?->name ?: 'Wild Type (None)' }}</td>
                        <td>
                            @if ($animalModel->isCustomAnimalModel())
                                @if ($animalModel->locus_identity_type === 'genes' && $animalModel->targetGenes->isNotEmpty())
                                    {{ $animalModel->targetGenes->pluck('symbol')->join(', ') }}
                                @elseif ($animalModel->genomicRegionSummary())
                                    {{ $animalModel->genomicRegionSummary() }}
                                @else
                                    {{ $animalModel->animal_model_description ?: '-No Description-' }}
                                @endif
                            @elseif ($animalModel->isDoubleKnockout())
                                {{ $animalModel->attachedGenes()->pluck('symbol')->filter()->join(' ; ') ?: '-None Selected-' }}
                            @else
                                {{ $animalModel->gene?->symbol ?? '-None Selected-' }}
                            @endif
                        </td>
                        <td data-input-unlock>
                            <form method="GET" action="{{ route('study.animal-experimentation.animal-models.edit', ['study' => $study->id, 'animalModel' => $animalModel->id]) }}" class="d-inline">
                                <button class="btn btn-sm btn-secondary" type="submit" title="Edit Animal Model">
                                    <i class="bi-pencil"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                @if ( $animalModels->isEmpty() )
                    <tr>
                        <td colspan="100%" class="text-center text-muted fst-italic">No animal models added yet.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    {{-- <h2 class="mt-5 mb-2">CRE Animal Models</h2>
    <div class="table-responsive">
        <table class="table small">
            <thead>
                <tr>
                    <th>Animal ID</th>
                    <th>Animal Line</th>
                    <th>Tissue/Cell Lineage</th>
                    <th>Gene</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($creAnimalModels as $animalModel)
                    <tr>
                        <td>{{ $animalModel->id }}</td>
                        <td>
                            <span>{{ $animalModel->animalLine?->label ?? '' }}</span>
                            @if ($animalModel->animalLine?->iri)
                                <a class="d-block text-muted small" href="{{ $animalModel->animalLine->iri }}" target="_blank">
                                    <span class="d-block text-muted small">{{ $animalModel->animalLine->iri }}</span>
                                </a>
                            @endif
                        </td>
                        <td>{{ $animalModel->tissue_lineage_specificity }}</td>
                        <td>{{ $animalModel->gene?->symbol }}</td>
                        <td>
                            <a class="btn btn-sm btn-secondary" href="{{ route('study.animal-experimentation.animal-models.edit', ['study' => $study->id, 'animalModel' => $animalModel->id]) }}" title="Edit Animal Model">
                                <i class="bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
                @if ( $creAnimalModels->isEmpty() )
                    <tr>
                        <td colspan="100%" class="text-center text-muted fst-italic">No CRE animal models added yet.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div> --}}


    <div class="d-flex justify-content-end mt-4" @if($study->isLockedForEditing(auth()->user())) data-input-locked @endif>
        <form method="GET" action="{{ $study->nextAnimalExperimentationStepAfter('study.animal-experimentation.animal-models') }}">
            <button type="submit" class="btn btn-lg btn-primary">Save &amp; Continue <i class="bi-arrow-right"></i></button>
        </form>
    </div>

</div>

</x-layouts.submission>
