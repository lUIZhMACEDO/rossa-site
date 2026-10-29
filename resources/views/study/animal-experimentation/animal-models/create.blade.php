@section('title', 'Animal Models - Animal Experimentation')
@section('guide-content')
    @include('guides.data-ingestion._04_1animalexp-models')
@endsection
<x-layouts.submission :$study>
<div class="pt-0 pb-5" @if($study->isLockedForEditing(auth()->user())) data-input-locked @endif>

@if ( $showGeneticCategorySelection )

    {{-- Page header --}}
    <div class="p-3 mb-4" style="background-color: #0D3B52; border-radius: 0.375rem;">
        <h1 class="mb-1" style="color: #fff;">Genetic Modifications to Animal Models</h1>
        <p class="mb-0" style="color: #E6F1FB;">
            If your study involves genetically modified animals, please select from the appropriate category below.
             If your genetic modification does not fit any of the preconfigured categories, select "Custom Animal Model."
             If you your animal model was not genetically modified, select "No Genetic Modification."
        </p>
    </div>
    {{-- Section 01: add a new animal model --}}
    @if (!$study->performs_genetic_modification)
        <form action="{{ route('study.animal-experimentation.setup.toggle-genetic-modification', $study) }}" method="POST">
            @csrf
            @method('PATCH')
            @include('study.animal-experimentation.animal-models.partials.toggle-genetic-category')
        </form>
    @else
        <form action="{{ route('study.animal-experimentation.setup.toggle-genetic-modification', $study) }}" method="POST">
            @csrf
            @method('PATCH')
            @include('study.animal-experimentation.animal-models.partials.select-genetic-category', [
                'genetic_category_id' => null,
                'availableGeneticCategories' => \App\Models\GeneticCategory::all()
            ])
        </form>
    @endif

    {{-- Section: existing animal models table --}}
    <div class="rounded p-3 p-md-4 mb-5" style="background-color: #0D3B52;">
    <div class="d-flex align-items-baseline gap-2 mb-2">
        <h2 class="h5 mb-0" style="color: #fff;">Animal Models Associated with Your Study</h2>
        @if ($animalModels->isNotEmpty())
            <span style="font-size: .8125rem; color: #E6F1FB;">
                ({{ $animalModels->count() }} {{ Str::plural('model', $animalModels->count()) }})
            </span>
        @endif
    </div>
 
        <div class="bg-white rounded p-3">
            <div class="table-responsive">
                <table class="table small align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Animal ID</th>
                            <th>Species</th>
                            <th>Strain</th>
                            <th>Genetic Modification Category</th>
                            <th>Gene Modified</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($animalModels as $listedAnimalModel)
                            <tr>
                                <td>{{ $listedAnimalModel->id }}</td>
                                <td>{{ $listedAnimalModel->species }}</td>
                                <td>
                                    <span>{{ $listedAnimalModel->experimentalStrainLabel() }}</span>
                                    @if ($listedAnimalModel->animalLine?->iri)
                                        <a class="d-block text-muted small" href="{{ $listedAnimalModel->animalLine->iri }}" target="_blank">
                                            <span class="d-block text-muted small">{{ $listedAnimalModel->animalLine->iri }}</span>
                                        </a>
                                    @endif
                                </td>
                                <td>{{ $listedAnimalModel->geneticCategory?->name ?: 'Wild Type (None)' }}</td>
                                <td>
                                    @if ($listedAnimalModel->isCustomAnimalModel())
                                        @if ($listedAnimalModel->locus_identity_type === 'genes' && $listedAnimalModel->targetGenes->isNotEmpty())
                                            {{ $listedAnimalModel->targetGenes->pluck('symbol')->join(', ') }}
                                        @elseif ($listedAnimalModel->genomicRegionSummary())
                                            {{ $listedAnimalModel->genomicRegionSummary() }}
                                        @else
                                            {{ $listedAnimalModel->animal_model_description ?: '-No Description-' }}
                                        @endif
                                    @elseif ($listedAnimalModel->isDoubleKnockout())
                                        {{ $listedAnimalModel->attachedGenes()->pluck('symbol')->filter()->join(' ; ') ?: '-None Selected-' }}
                                    @else
                                        {{ $listedAnimalModel->gene?->symbol ?? '-None Selected-' }}
                                    @endif
                                </td>
                                <td data-input-unlock>
                                    <div class="d-inline-flex gap-1">
                                        <form method="GET" action="{{ route('study.animal-experimentation.animal-models.edit', ['study' => $study->id, 'animalModel' => $listedAnimalModel->id]) }}" class="d-inline">
                                            <button class="btn btn-sm btn-secondary" type="submit" title="Edit Animal Model">
                                                <i class="bi-pencil"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('study.animal-experimentation.animal-models.destroy', ['study' => $study->id, 'animalModel' => $listedAnimalModel->id]) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this animal model?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit" title="Delete Animal Model">
                                                <i class="bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        @if ( $animalModels->isEmpty() )
                            <tr>
                                <td colspan="100%" class="text-center text-muted fst-italic py-4">No animal models added yet. Use the categories below to add your first one.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

   
    <div class="d-flex justify-content-end mt-4" @if($study->isLockedForEditing(auth()->user())) data-input-locked @endif>
        <form method="GET" action="{{ $study->nextAnimalExperimentationStepAfter('study.animal-experimentation.animal-models') }}">
            <button type="submit" class="btn btn-lg btn-primary">Save &amp; Continue <i class="bi-arrow-right"></i></button>
        </form>
    </div>

@else

    {{-- Create form state --}}
    <div class="p-3 mb-4" style="background-color: #0D3B52; border-radius: 0.375rem;">
    <h1 class="mb-1" style="color: #fff;">Create Animal Model</h1>
        <p class="mb-1" style="color: #E6F1FB; font-size: 1.1rem;">
        <span class="fw-semibold">Selected Genetic Modification:</span>
        <span class="fw-bold" style="color: #E8C46A;">{{ $selectedGeneticCategory ? $selectedGeneticCategory->name : 'No Genetic Modification' }}</span>
    </p>
    @if ( !$animalModel->exists && !$animalModel->intercrossed_from_id )
        <a href="{{ route('study.animal-experimentation.animal-models.create', [$study, $animalModel]) }}" class="btn btn-sm btn-light mt-2">
            <i class="bi-arrow-left"></i> Back to Selection
        </a>
    @endif
    </div>

    <form action="{{ route('study.animal-experimentation.animal-models.store', $study) }}" method="POST" data-disable-onsubmit>
        @csrf

        @include('study.animal-experimentation.animal-models.forms.fields', [
            'animalModel' => $animalModel,
            'selectedGeneticCategory' => $selectedGeneticCategory,
            'study' => $study,
        ])

        <button type="submit" class="btn btn-primary">Create Animal Model</button>

    </form>

@endif

</div>
</x-layouts.submission>