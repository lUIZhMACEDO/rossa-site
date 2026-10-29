<div class="row row-md-6">
    <div class="col-md-6 mb-3">
        <label for="animalModelName" class="form-label">Name of Animal Model</label>
        <input type="text" class="form-control" id="animalModelName" name="animal_model_name" value="{{ old('animal_model_name', $animalModel->animal_model_name) }}">
    </div>

    <div class="col-md-6 mb-3">
        <label for="investigator" class="form-label">Provide the source where animal model obtained (investigator or repository)</label>
        <input type="text" class="form-control" id="investigator" name="investigator" value="{{ old('investigator', $animalModel->investigator) }}">
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
        <label for="geneSelection" class="form-label">Genetically Modified Gene</label>
        @livewire('animal-experimentation.animal-models-form.gene-search',
            [
                'geneId' => $animalModel->gene_id,
            ]
        )
    </div>
</div>
