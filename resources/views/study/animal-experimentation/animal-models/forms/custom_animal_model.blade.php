@php
    $savedGeneIds = $animalModel->exists
        ? $animalModel->targetGenes()->pluck('genes.id')->values()->all()
        : [];
    $oldGeneIds = old('gene_ids', $savedGeneIds);
    if (!is_array($oldGeneIds)) {
        $oldGeneIds = [];
    }
    $oldGeneIds = array_values(array_filter($oldGeneIds));
    $initialGeneCount = max(1, min(3, count($oldGeneIds) ?: (int) old('gene_row_count', 1)));
    $identityType = old('locus_identity_type', $animalModel->locus_identity_type);
    $speciesAssemblies = [];
    $speciesChromosomes = [];
    foreach (\App\Enums\Species::cases() as $speciesCase) {
        $speciesChromosomes[$speciesCase->value] = $speciesCase->chromosomes();
        $speciesAssemblies[$speciesCase->value] = $speciesCase->genomeAssemblies();
    }
@endphp

<div class="row"
    x-data="{
        identityType: @js($identityType ?: ''),
        geneCount: {{ $initialGeneCount }},
        species: document.getElementById('species')?.value || @js(old('species', $animalModel->species)),
        chromosome: @js(old('genomic_chromosome', $animalModel->genomic_chromosome)),
        assembly: @js(old('genomic_assembly', $animalModel->genomic_assembly)),
        chromosomesBySpecies: @js($speciesChromosomes),
        assembliesBySpecies: @js($speciesAssemblies),
        init() {
            const speciesSelect = document.getElementById('species');
            if (speciesSelect) {
                speciesSelect.addEventListener('change', (e) => {
                    this.species = e.target.value;
                    if (!this.chromosomes.includes(this.chromosome)) {
                        this.chromosome = '';
                    }
                    if (!this.assemblies.includes(this.assembly)) {
                        this.assembly = '';
                    }
                });
            }
        },
        get chromosomes() {
            return this.chromosomesBySpecies[this.species] || [];
        },
        get assemblies() {
            return this.assembliesBySpecies[this.species] || [];
        }
    }">

    <div class="col-md-6 mb-3">
        <label for="animalModelName" class="required form-label fw-semibold">Name of Animal Model</label>
        <input type="text" class="form-control @error('animal_model_name') is-invalid @enderror" id="animalModelName" name="animal_model_name" placeholder="e.g. XX Sry-transgenic" value="{{ old('animal_model_name', $animalModel->animal_model_name) }}">
        @error('animal_model_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="investigator" class="form-label fw-semibold">Source</label>
        <input type="text" class="form-control" id="investigator" name="investigator" placeholder="Investigator or repository" value="{{ old('investigator', $animalModel->investigator) }}">
    </div>

    <div class="col-12 mb-3">
        <label for="animalModelDescription" class="required form-label fw-semibold">Describe Genetic Modification</label>
        <input
            type="text"
            class="form-control @error('animal_model_description') is-invalid @enderror"
            id="animalModelDescription"
            name="animal_model_description"
            placeholder="In 2-3 sentences, provide a brief description of the modification"
            value="{{ old('animal_model_description', $animalModel->animal_model_description) }}"
        >
        @error('animal_model_description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 mb-3">
        <label for="functionalOutcomeDescription" class="form-label fw-semibold">Describe the Functional Outcome or Mechanism</label>
        <textarea
            class="form-control @error('functional_outcome_description') is-invalid @enderror"
            id="functionalOutcomeDescription"
            name="functional_outcome_description"
            rows="4"
            placeholder="Resulting from this genetic modification"
        >{{ old('functional_outcome_description', $animalModel->functional_outcome_description) }}</textarea>
        @error('functional_outcome_description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 mb-2">
        <label class="required form-label fw-semibold">Define the genetic modification by selecting one or more genes, or a genomic region for larger changes (select one) </label>
        <div class="@error('locus_identity_type') is-invalid @enderror">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="locus_identity_type" id="identityGenes" value="genes" x-model="identityType" @checked($identityType === 'genes')>
                <label class="form-check-label" for="identityGenes">Specific gene(s), up to 3</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="locus_identity_type" id="identityRegion" value="genomic_region" x-model="identityType" @checked($identityType === 'genomic_region')>
                <label class="form-check-label" for="identityRegion">Genomic region</label>
            </div>
        </div>
        @error('locus_identity_type')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 mb-3" x-show="identityType === 'genes'" x-cloak>
        <input type="hidden" name="gene_row_count" :value="geneCount">
        @error('gene_ids')
            <div class="invalid-feedback d-block mb-2">{{ $message }}</div>
        @enderror
        @error('gene_ids.*')
            <div class="invalid-feedback d-block mb-2">{{ $message }}</div>
        @enderror

        <div class="row align-items-stretch">
            @for ($i = 0; $i < 3; $i++)
                <div class="col-md-4 mb-3" x-show="geneCount > {{ $i }}">
                    @livewire('animal-experimentation.animal-models-form.gene-search', [
                        'formField' => 'gene_ids[' . $i . ']',
                        'geneId' => $oldGeneIds[$i] ?? null,
                        'lockSpecies' => true,
                        'animalSpecies' => old('species', $animalModel->species),
                        'instanceId' => 'custom-gene-' . $i,
                    ], key('custom-gene-' . ($animalModel->id ?: 'new') . '-' . $i))
                    @if ($i > 0)
                        <button type="button" class="btn btn-sm btn-outline-danger mt-2" @click="geneCount = Math.max(1, geneCount - 1)">
                            Remove gene
                        </button>
                    @endif
                </div>
            @endfor
            <div class="col-md-4 mb-3" x-show="geneCount < 3" x-cloak>
                <div class="h-100 d-flex align-items-center justify-content-center">
                    <button
                        type="button"
                        class="btn p-0 border-0 bg-transparent d-flex flex-column align-items-center gap-2 text-primary"
                        @click="geneCount = Math.min(3, geneCount + 1)"
                    >
                        <span class="small fw-semibold">Add another gene</span>
                        <span
                            class="d-flex align-items-center justify-content-center border border-primary rounded"
                            style="width: 4rem; height: 4rem;"
                        >
                            <i class="bi bi-plus-lg" style="font-size: 1.75rem; line-height: 1;"></i>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 mb-0" x-show="identityType === 'genomic_region'" x-cloak>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="genomicChromosome" class="required form-label fw-semibold">Chromosome</label>
                <select class="form-select @error('genomic_chromosome') is-invalid @enderror" id="genomicChromosome" name="genomic_chromosome" x-model="chromosome">
                    <option value="">e.g. 7</option>
                    @foreach (['1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','X','Y','MT'] as $chromosome)
                        <option value="{{ $chromosome }}" x-show="chromosomes.includes('{{ $chromosome }}')">{{ $chromosome }}</option>
                    @endforeach
                </select>
                @error('genomic_chromosome')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-4 mb-3">
                <label for="genomicStart" class="form-label fw-semibold">Start</label>
                <input type="number" min="1" step="1" class="form-control @error('genomic_start') is-invalid @enderror" id="genomicStart" name="genomic_start" placeholder="Optional" value="{{ old('genomic_start', $animalModel->genomic_start) }}">
                @error('genomic_start')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-4 mb-3">
                <label for="genomicEnd" class="form-label fw-semibold">End</label>
                <input type="number" min="1" step="1" class="form-control @error('genomic_end') is-invalid @enderror" id="genomicEnd" name="genomic_end" placeholder="Optional" value="{{ old('genomic_end', $animalModel->genomic_end) }}">
                @error('genomic_end')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="genomicAssembly" class="form-label fw-semibold">Genome Assembly</label>
                <select class="form-select @error('genomic_assembly') is-invalid @enderror" id="genomicAssembly" name="genomic_assembly" x-model="assembly">
                    <option value="">Required if start/end entered</option>
                    @foreach (\App\Enums\Species::cases() as $speciesCase)
                        @foreach ($speciesCase->genomeAssemblies() as $assemblyOption)
                            <option value="{{ $assemblyOption }}" x-show="assemblies.includes('{{ $assemblyOption }}')">{{ $assemblyOption }}</option>
                        @endforeach
                    @endforeach
                </select>
                @error('genomic_assembly')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-12 mb-0">
                <label for="genomicRegionDescription" class="required form-label fw-semibold">Description</label>
                <textarea
                    class="form-control @error('genomic_region_description') is-invalid @enderror"
                    id="genomicRegionDescription"
                    name="genomic_region_description"
                    rows="3"
                    placeholder="e.g. ~12 Mb deletion, removes the Xyz gene cluster"
                >{{ old('genomic_region_description', $animalModel->genomic_region_description) }}</textarea>
                @error('genomic_region_description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>
