<div x-data="{ cre: '{{ old('cre_system_used', $animalModel->cre_system_used) ?: '' }}' }">
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="locus_name_safe_harbor" class="form-label">Locus Name of Safe Harbor Site</label>
            <input type="text" class="form-control" id="locus_name_safe_harbor" name="locus_name_safe_harbor" value="{{ old('locus_name_safe_harbor', $animalModel->locus_name_safe_harbor) }}">
        </div>

        <div class="col-md-6 mb-3">
            <label for="animalModelName" class="form-label">Name of Animal Model</label>
            <input type="text" class="form-control" id="animalModelName" name="animal_model_name" value="{{ old('animal_model_name', $animalModel->animal_model_name) }}">
        </div>

        <div class="col-md-6 mb-3">
            <label for="investigator" class="form-label">Provide the source where animal model obtained (investigator or repository)</label>
            <input type="text" class="form-control" id="investigator" name="investigator" value="{{ old('investigator', $animalModel->investigator) }}">
        </div>


        <div class="col-md-6 mb-3">
            <label for="transgene_abbreviation" class="form-label">Abbreviation of Knocked In Transgene to Safe Harbor Site</label>
            <input type="text" class="form-control" id="transgene_abbreviation" name="transgene_abbreviation" value="{{ old('transgene_abbreviation', $animalModel->transgene_abbreviation) }}">
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
            <label for="functional_change" class="form-label">Functional Change</label>
            <select class="form-select" id="functional_change" name="functional_change">
                <option value="">- Select Functional Change -</option>
                @foreach(\App\Enums\FunctionalChange::getTransgeneOptions() as $change)
                    <option value="{{ $change->value }}" @selected($change->value === old('functional_change', $animalModel->functional_change))>{{ $change->print() }}</option>
                @endforeach
            </select>
        </div>


        {{-- Custom Transgene Details --}}
        <div class="col-md-6 mb-3">
            <label for="transgeneGeneSelection" class="form-label">Gene from which Regulatory Sequences were derived to Drive the Expression of the <span x-show="cre">Cre Recombinase</span><span x-show="!cre">Desired Product</span></label>
            @livewire('animal-experimentation.animal-models-form.gene-search',
                [
                    'geneId' => $animalModel->gene_id ?? null,
                ]
            )
        </div>

        <div class="col-md-6 mb-3">
            <label for="promoter_origin" class="form-label">Promoter Origin (If Different)</label>
            @livewire('animal-experimentation.animal-models-form.gene-search',
                [
                    'geneId' => $animalModel->promoter_gene_id ?? null,
                    'formField' => 'promoter_gene_id',
                ]
            )
        </div>

        <div class="col-md-6 mb-0">
            <label for="promoter_other" class="form-label">Promoter (If Synthetic/Other)</label>
            <input type="text" class="form-control" id="promoter_other" name="promoter_other" value="{{ old('promoter_other', $animalModel->promoter_other) }}">
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