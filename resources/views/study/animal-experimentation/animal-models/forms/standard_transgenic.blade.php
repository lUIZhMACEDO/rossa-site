<div class="row" x-data="{ hasInducibleDrugTreatment: {{ $animalModel->inducibleDrugTreatment ? 'true' : 'false' }} }">
    <div class="row row-md-6">
        <div class="col-md-6 mb-3">
            <label for="animalModelName" class="form-label">Name of Transgenic Animal Model</label>
            <input type="text" class="form-control" id="animalModelName" name="animal_model_name" value="{{ old('animal_model_name', $animalModel->animal_model_name) }}">
        </div>

        <div class="col-md-6 mb-3">
            <label for="investigator" class="form-label">Provide the source where animal model obtained (investigator or repository)</label>
            <input type="text" class="form-control" id="investigator" name="investigator" value="{{ old('investigator', $animalModel->investigator) }}">
        </div>

        <div class="col-md-6 mb-3">
            <label for="transgene_abbreviation" class="form-label">Abbreviation of Randomly Integrated Transgene</label>
            <input type="text" class="form-control" id="transgene_abbreviation" name="transgene_abbreviation" value="{{ old('transgene_abbreviation', $animalModel->transgene_abbreviation) }}">
        </div>


        <div class="col-md-6 mb-3">
            <label for="tissue_lineage_specificity" class="form-label">Tissue/Cell Lineage Specificity of Transgenic Line</label>
            <input type="text" class="form-control" id="tissue_lineage_specificity" name="tissue_lineage_specificity" value="{{ old('tissue_lineage_specificity', $animalModel->tissue_lineage_specificity) }}">
        </div>


        <div class="col-md-6 mb-3">
            <label for="transgene_biotype" class="form-label">Type of Coding Sequence</label>
            <select class="form-select" id="transgene_biotype" name="transgene_biotype">
                <option value="">- Select -</option>
                @foreach(\App\Enums\BioType::cases() as $bioType)
                    <option value="{{ $bioType->value }}" @selected($bioType->value === old('transgene_biotype', $animalModel->transgene_biotype))>{{ $bioType->print() }}</option>
                @endforeach
            </select>
        </div>
        {{-- End Custom Transgene Details --}}

        <div class="col-md-6 mb-3">
            <label for="gene_product" class="form-label">Name of the Gene Product being expressed by the transgene</label>
            <input type="text" class="form-control" id="gene_product" name="gene_product" value="{{ old('gene_product', $animalModel->gene_product) }}">
        </div>
        
        {{-- <div class="col-md-6">
            <label for="geneticMethod" class="form-label">Type of Genetic Modification</label>
            <select class="form-select" id="geneticMethod" name="genetic_method_id">
                <option value="">- Select Genetic Method -</option>
                @foreach($geneticCategory->geneticMethods as $method)
                    <option value="{{ $method->id }}" @selected($method->id === old('genetic_method_id', $animalModel->genetic_method_id))>{{ $method->name }} ({{ $method->allele_abbreviation }})</option>
                @endforeach
            </select>
        </div> --}}

        <div class="col-md-6 mb-3">
            <label for="geneSelection" class="form-label">Name of Gene from which Regulatory Sequences were derived to Drive the Expression of the Desired Product</label>
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
                @foreach(\App\Enums\FunctionalChange::getTransgeneOptions() as $change)
                    <option value="{{ $change->value }}" @selected($change->value === old('functional_change', $animalModel->functional_change))>{{ $change->print() }}</option>
                @endforeach
            </select>
        </div>

    </div>
</div>

{{-- Drug Inducible Fields --}}
@if ( $selectedGeneticCategory->can_drug_induce )
<div class="card mb-4 mt-4" style="border-left: 4px solid var(--bs-primary);">
    <div class="card-body">
        <h5 class="card-title mb-4">Inducible Drug Treatment</h5>

        <div class="form-check form-control-lg mb-4">
            <input class="form-check-input" type="checkbox"
                name="inducible_drug_treatment"
                value="1"
                @checked($animalModel->inducibleDrugTreatment)
                x-model="hasInducibleDrugTreatment"
            >
            <label class="form-check-label" for="inducibleDrugTreatmentCheckbox">
                Does the transgene utilize a drug inducible mechanism?
            </label>
        </div>

        <div x-show="hasInducibleDrugTreatment">
            @include('study.animal-experimentation.animal-models.forms.fields-inducible-treatment', [
                'animalModel' => $animalModel,
                'study' => $study,
            ])
        </div>
    </div>
</div>
@endif

    {{-- @if ( !$animalModel->intercrossed_from_id )
    <div class="col-12 mb-5">
        <hr class="bg-light mt-4" >
        <h3 class="mb-3">Intercross Animal</h3>
       
        <p>Was a second transgenic animal line utilized in this study which was intercrossed with the first transgenic animal line?</p>


        @include('study.animal-experimentation.animal-models.partials.intercross', [
            'animalModel' => $animalModel,
            'study' => $study,
        ])
    </div>
    @endif --}}