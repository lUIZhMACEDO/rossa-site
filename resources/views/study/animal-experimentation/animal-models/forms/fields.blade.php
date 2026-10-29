<div id="animal-model-form">
    <input type="hidden" name="genetic_category_id" value="{{ $selectedGeneticCategory?->id ?: '' }}">

    @if ( $animalModel->intercrossed_from_id )
        <input type="hidden" name="intercrossed_from_id" value="{{ $animalModel->intercrossed_from_id ?? '' }}">
        <div class="alert alert-warning">
            This animal model is an intercross animal derived from another transgenic animal model.
            <br>
            <a href="{{ route('study.animal-experimentation.animal-models.edit', [$study, $animalModel->intercrossed_from_id]) }}">
                Click here to view the original animal model from which this intercross animal was derived.
            </a>  
        </div>
    @endif


    <div class="card mb-4" style="border-radius: 0.375rem; overflow: hidden; border-left: 4px solid #0D3B52;">
        <div class="card-title text-white p-3 mb-0" style="background-color: #0D3B52;">
            <h5 class="mb-0">Animal Details</h5>
        </div>
        <div class="card-body">
            <!-- First we ask for animal species -->
            <div class="row">
                <div class="col-md-6">
                    <div class="col-12 mb-3">
                        <label for="species" class="required form-label fw-semibold">Species</label>
                        <select class="form-select @error('species') is-invalid @enderror" 
                        x-data
                            id="species"
                            name="species"
                            @readonly($animalModel->intercrossed_from_id)
                            x-on:change="$dispatch('speciesChanged', [$event.target.value])">
                            <option value="">Select species</option>
                            @foreach(App\Enums\Species::cases() as $species)
                                <option value="{{ $species->value }}" @selected($species->value === old('species', $animalModel->species))>{{ $species->print() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Animal Line Lookup -->
                    @if ($selectedGeneticCategory?->template !== 'double_knockout')
                    <div class="col-12 mb-0">
                        <label class="required form-label fw-semibold">Animal Strain</label>
                        <livewire:animal-experimentation.animal-models-form.animal-line-search 
                            :studyId="$study->id"
                            :animalLineId="old('animal_line_id', $animalModel->animal_line_id)"
                            :initialQuery="old('animal_line')"
                            :species="old('species', $animalModel->species)"
                            :hasError="$errors->has('animal_line_id')"
                            :wire:key="'strain-' . ($animalModel->id ?: 'new')"
                        />
                        @error('animal_line_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- Get fields for the appropriate genetic category -->
    @if ( $selectedGeneticCategory && $selectedGeneticCategory->template )
        {{-- Genetic Modification Details Card --}}
        <div class="card mb-4" style="border-radius: 0.375rem; overflow: hidden; border-left: 4px solid #0D3B52;">
            <div class="card-title text-white p-3 mb-0" style="background-color: #0D3B52;">
                <h5 class="mb-0">Genetic Modification Details</h5>
            </div>
            <div class="card-body">
                @includeIf('study.animal-experimentation.animal-models.forms.' . $selectedGeneticCategory->template, [
                    'animalModel' => $animalModel,
                    'geneticCategory' => $selectedGeneticCategory,
                    'gene_id' => $animalModel->gene_id,
                    'study' => $study,
                ])
            </div>
        </div>

        @if ($selectedGeneticCategory->template === 'custom_animal_model')
            @include('study.animal-experimentation.animal-models.forms.genotype-options', [
                'animalModel' => $animalModel,
            ])
        @endif

    @else

    {{-- Animal Model Label Card --}}
    <div class="card mb-4" style="border-radius: 0.375rem; overflow: hidden; border-left: 4px solid #0D3B52;">
        <div class="card-title text-white p-3 mb-0" style="background-color: #0D3B52;">
            <h5 class="mb-0">Animal Model Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-6 mb-0">
                    <label for="animalModelName" class="form-label">Animal Model Label</label>
                    
                    <input type="text" class="form-control" id="animalModelName" name="animal_model_name" value="{{ old('animal_model_name', $animalModel->animal_model_name) }}">
                    <small class="d-block mt-2 fst-italic">Optionally provide a name for the animal model.</small>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>