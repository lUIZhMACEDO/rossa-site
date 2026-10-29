@php
    $parentA = $animalModel->parentA;
    $parentB = $animalModel->parentB;
    $defaultZyg = ['+/+', '-/-'];
    $zygA = old('parent_a_zygosities', $parentA?->included_zygosities ?: $defaultZyg);
    $zygB = old('parent_b_zygosities', $parentB?->included_zygosities ?: $defaultZyg);
    $selectedCombinations = old('genotype_options', $animalModel->exists ? $animalModel->genotypeOptionList() : []);
    if (!is_array($zygA)) { $zygA = $defaultZyg; }
    if (!is_array($zygB)) { $zygB = $defaultZyg; }
    if (!is_array($selectedCombinations)) { $selectedCombinations = []; }
    $dkoZygosities = \App\Models\AnimalModel::DKO_ZYGOSITIES;
    $oldGeneAId = old('parent_a_gene_id', $parentA?->gene_id);
    $oldGeneBId = old('parent_b_gene_id', $parentB?->gene_id);
    $resolveDkoGeneSymbol = function ($geneId, $parent) {
        if (!$geneId) {
            return '';
        }
        if ((int) $geneId === (int) $parent?->gene_id && $parent?->gene) {
            return $parent->gene->symbol;
        }

        return \App\Models\Gene::find($geneId)?->symbol ?? '';
    };
    $geneASymbol = $resolveDkoGeneSymbol($oldGeneAId, $parentA);
    $geneBSymbol = $resolveDkoGeneSymbol($oldGeneBId, $parentB);
@endphp

<div
    x-data="{
        geneA: @js($geneASymbol),
        geneB: @js($geneBSymbol),
        zygA: @js(array_values($zygA)),
        zygB: @js(array_values($zygB)),
        selected: @js(array_values($selectedCombinations)),
        init() {
            this.$watch('zygA', () => this.pruneSelected());
            this.$watch('zygB', () => this.pruneSelected());
            this.$watch('geneA', () => this.pruneSelected());
            this.$watch('geneB', () => this.pruneSelected());
            if (window.Livewire) {
                Livewire.on('geneSelected', (payload) => {
                    const data = Array.isArray(payload) ? (payload[0] || {}) : (payload || {});
                    if (data.instanceId === 'dko-parent-a') {
                        this.geneA = data.symbol || '';
                    }
                    if (data.instanceId === 'dko-parent-b') {
                        this.geneB = data.symbol || '';
                    }
                });
            }
        },
        labelFor(zygA, zygB) {
            const geneA = this.geneA || 'GeneA';
            const geneB = this.geneB || 'GeneB';
            return geneA + zygA + ' ; ' + geneB + zygB;
        },
        hintFor(zygA, zygB) {
            if (zygA === '-/-' && zygB === '-/-') return 'Double Mutant';
            if (zygA === '+/+' && zygB === '+/+') return 'Double WT Control';
            if (zygA === '-/-' && zygB === '+/+') return 'Gene A Single Mutant';
            if (zygA === '+/+' && zygB === '-/-') return 'Gene B Single Mutant';
            return '';
        },
        get combinations() {
            const rows = [];
            this.zygA.forEach((zygA) => {
                this.zygB.forEach((zygB) => {
                    rows.push({
                        label: this.labelFor(zygA, zygB),
                        hint: this.hintFor(zygA, zygB),
                    });
                });
            });
            return rows;
        },
        pruneSelected() {
            const valid = this.combinations.map((row) => row.label);
            this.selected = this.selected.filter((label) => valid.includes(label));
        }
    }"
>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="animalModelName" class="required form-label fw-semibold">Name of Animal Model</label>
            <input type="text" class="form-control @error('animal_model_name') is-invalid @enderror" id="animalModelName" name="animal_model_name" placeholder="Name for crossed double mutant line" value="{{ old('animal_model_name', $animalModel->animal_model_name) }}">
            @error('animal_model_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <h6 class="mt-2 mb-3 fw-bold">Genetically Modified Parents</h6>
    <div class="row">
        @foreach (['a' => 'Parent line A', 'b' => 'Parent line B'] as $slot => $heading)
            @php
                $parent = $slot === 'a' ? $parentA : $parentB;
                $prefix = 'parent_' . $slot;
            @endphp
            <div class="col-md-6 mb-4">
                <div class="border rounded p-3 h-100">
                    <h6 class="mb-3 fw-bold">{{ $heading }}</h6>

                    <div class="mb-3">
                        <label class="required form-label fw-semibold">Animal Strain</label>
                        <livewire:animal-experimentation.animal-models-form.animal-line-search
                            :studyId="$study->id"
                            :animalLineId="old($prefix . '_animal_line_id', $parent?->animal_line_id)"
                            :initialQuery="old($prefix . '_animal_line')"
                            :species="old('species', $animalModel->species)"
                            :formField="$prefix . '_animal_line_id'"
                            :textFieldName="$prefix . '_animal_line'"
                            :hasError="$errors->has($prefix . '_animal_line_id')"
                            :wire:key="'dko-strain-' . $slot . '-' . ($animalModel->id ?: 'new') . '-' . old($prefix . '_animal_line_id', $parent?->animal_line_id) . '-' . old($prefix . '_animal_line')"
                        />
                        @error($prefix . '_animal_line_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="{{ $prefix }}_investigator" class="form-label fw-semibold">Source</label>
                        <input
                            type="text"
                            class="form-control @error($prefix . '_investigator') is-invalid @enderror"
                            id="{{ $prefix }}_investigator"
                            name="{{ $prefix }}_investigator"
                            placeholder="Investigator or repository"
                            value="{{ old($prefix . '_investigator', $parent?->investigator) }}"
                        >
                        @error($prefix . '_investigator')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="required form-label fw-semibold">Gene</label>
                        @livewire('animal-experimentation.animal-models-form.gene-search', [
                            'formField' => $prefix . '_gene_id',
                            'geneId' => $slot === 'a' ? $oldGeneAId : $oldGeneBId,
                            'lockSpecies' => true,
                            'animalSpecies' => old('species', $animalModel->species),
                            'instanceId' => 'dko-parent-' . $slot,
                            'excludeInstanceId' => $slot === 'b' ? 'dko-parent-a' : null,
                            'excludeGeneIds' => $slot === 'b' && $oldGeneAId ? [(int) $oldGeneAId] : [],
                        ], key('dko-gene-' . $slot . '-' . ($animalModel->id ?: 'new') . '-' . ($slot === 'a' ? $oldGeneAId : $oldGeneBId)))
                        @error($prefix . '_gene_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="required form-label fw-semibold">Modification Type</label>
                        <select class="form-select @error($prefix . '_modification_type') is-invalid @enderror" name="{{ $prefix }}_modification_type">
                            <option value="">Select modification</option>
                            @foreach (\App\Enums\DkoParentModification::cases() as $modification)
                                <option value="{{ $modification->value }}" @selected($modification->value === old($prefix . '_modification_type', $parent?->modification_type))>{{ $modification->print() }}</option>
                            @endforeach
                        </select>
                        @error($prefix . '_modification_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="required form-label fw-semibold">Genotypes to Include</label>
                        @foreach ($dkoZygosities as $zygosity)
                            @php
                                $zygLabel = match ($zygosity) {
                                    '+/+' => '+/+ (Wild Type)',
                                    '+/-' => '+/- (Heterozygous)',
                                    '-/-' => '-/- (Homozygous)',
                                    default => $zygosity,
                                };
                                $zygId = $prefix . '_zyg_' . match ($zygosity) {
                                    '+/+' => 'wt',
                                    '+/-' => 'het',
                                    '-/-' => 'hom',
                                    default => $loop->index,
                                };
                            @endphp
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="{{ $prefix }}_zygosities[]"
                                    id="{{ $zygId }}"
                                    value="{{ $zygosity }}"
                                    x-model="{{ $slot === 'a' ? 'zygA' : 'zygB' }}"
                                >
                                <label class="form-check-label" for="{{ $zygId }}">{{ $zygLabel }}</label>
                            </div>
                        @endforeach
                        @error($prefix . '_zygosities')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row mt-2">
        <div class="col-md-6 mb-3">
            <label class="required form-label fw-semibold">Genotype Combinations</label>
            <div class="form-text mb-3">Auto-generated from the checked genotypes above. Check the combinations to include in the sample group genotype dropdown.</div>
            @error('genotype_options')
                <div class="invalid-feedback d-block mb-2">{{ $message }}</div>
            @enderror
            <div class="d-flex flex-column gap-2" x-show="combinations.length > 0">
                <template x-for="row in combinations" :key="row.label">
                    <label
                        class="d-flex align-items-center gap-2 rounded-2 px-3 py-2 mb-0"
                        style="cursor: pointer; border: 1px solid #dee2e6; background: #fff;"
                        :style="selected.includes(row.label)
                            ? 'cursor: pointer; border: 1px solid var(--bs-primary); box-shadow: inset 0 0 0 1px var(--bs-primary); background: rgba(var(--bs-primary-rgb), 0.07);'
                            : 'cursor: pointer; border: 1px solid #dee2e6; background: #fff;'"
                    >
                        <input
                            class="form-check-input flex-shrink-0 m-0"
                            type="checkbox"
                            name="genotype_options[]"
                            :value="row.label"
                            x-model="selected"
                        >
                        <span class="min-w-0">
                            <span class="fw-semibold d-block" x-text="row.label"></span>
                            <span class="text-muted small d-block" x-show="row.hint" x-text="row.hint"></span>
                        </span>
                    </label>
                </template>
            </div>
            <div class="text-muted" x-show="combinations.length === 0">Check at least one genotype on each parent to generate combinations.</div>
        </div>
    </div>

    @foreach ($animalModel->genotypeOptionList() as $originalLabel)
        <input type="hidden" name="genotype_options_original[]" value="{{ $originalLabel }}">
    @endforeach
</div>
