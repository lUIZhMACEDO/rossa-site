
<div x-data="{ cre: '{{ old('cre_system_used', $animalModel->cre_system_used) ?: '' }}' }">
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="animalModelName" class="form-label">Name of Animal Model</label>
        <input type="text" class="form-control" id="animalModelName" name="animal_model_name" value="{{ old('animal_model_name', $animalModel->animal_model_name) }}">
    </div>

    <div class="col-md-6 mb-3">
        <label for="investigator" class="form-label">Provide the source where animal model obtained (investigator or repository)</label>
        <input type="text" class="form-control" id="investigator" name="investigator" value="{{ old('investigator', $animalModel->investigator) }}">
    </div>

    <div class="col-md-6 mb-0">
        <label for="functional_change" class="form-label">Functional Change</label>
        <select class="form-select" id="functional_change" name="functional_change">
            <option value="">- Select Functional Change -</option>
            @foreach(\App\Enums\FunctionalChange::getGeneralOptions() as $change)
                <option value="{{ $change->value }}" @selected($change->value === old('functional_change', $animalModel->functional_change))>{{ $change->print() }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6 mb-3">
        <label for="geneticMethod" class="form-label">Type of Genetic Modification</label>
        <select class="form-select" id="geneticMethod" name="genetic_method_id">
            <option value="">- Select Genetic Method -</option>
            @foreach($geneticCategory->geneticMethods as $method)
                <option value="{{ $method->id }}" @selected($method->id === old('genetic_method_id', $animalModel->genetic_method_id))>{{ $method->name }} ({{ $method->allele_abbreviation }})</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6 mb-3">
        <label for="geneSelection" class="form-label">Name of Gene Engineered for Conditional Targeting</label>
        @livewire('animal-experimentation.animal-models-form.gene-search',
            [
                'geneId' => $animalModel->gene_id,
                ]
                )
    </div>

    <div class="col-md-6 mb-0">
        <label for="functional_change" class="form-label">Functional Change</label>
        <select class="form-select" id="functional_change" name="functional_change">
            <option value="">- Select Functional Change -</option>
            @foreach(\App\Enums\FunctionalChange::getGeneralOptions() as $change)
                <option value="{{ $change->value }}" @selected($change->value === old('functional_change', $animalModel->functional_change))>{{ $change->print() }}</option>
            @endforeach
        </select>
    </div>
</div>

@if ( $selectedGeneticCategory->can_cre )
{{-- Cre Animal Information Card --}}
<div class="card mb-4 mt-4" style="border-left: 4px solid var(--bs-primary);">
    <div class="card-body">
        <h5 class="card-title mb-4">Cre Animal Information</h5>
        
        @include('study.animal-experimentation.animal-models.forms.fields-cre', [
            'animalModel' => $animalModel,
            'study' => $study,
        ])
    </div>
</div>

{{-- Only show inducible drug treatment if cre is inducible --}}
<div x-show="cre === 'tamoxifen' || cre === 'doxycycline'" class="card mb-4" style="border-left: 4px solid var(--bs-primary);">
    <div class="card-body">
        <h5 class="card-title mb-4">Inducible Drug Treatment</h5>

        @include('study.animal-experimentation.animal-models.forms.fields-inducible-treatment', [
            'animalModel' => $animalModel,
            'study' => $study,
        ])
    </div>
</div>
@endif
</div>
