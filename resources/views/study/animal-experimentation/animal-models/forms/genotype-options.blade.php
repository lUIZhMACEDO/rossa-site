@php
    $savedLabels = $animalModel->genotypeOptionList();
    $oldLabels = old('genotype_options', $savedLabels);
    if (!is_array($oldLabels)) {
        $oldLabels = [];
    }
    $oldOriginals = old('genotype_options_original', $savedLabels);
    if (!is_array($oldOriginals)) {
        $oldOriginals = [];
    }
    $labelRows = [];
    foreach ($oldLabels as $index => $label) {
        $labelRows[] = [
            'value' => $label,
            'original' => $oldOriginals[$index] ?? ($savedLabels[$index] ?? ''),
        ];
    }
    if (count($labelRows) === 0) {
        $labelRows[] = ['value' => '', 'original' => ''];
    }
@endphp

<div class="card mb-4" style="border-left: 4px solid var(--bs-primary);"
    x-data="{
        labels: @js($labelRows),
        maxLabels: 6,
        get hasFilledLabel() {
            return this.labels.some((label) => (label.value || '').trim() !== '');
        },
        addLabel() {
            if (this.labels.length >= this.maxLabels) return;
            this.labels.push({ value: '', original: '' });
            this.$nextTick(() => {
                const inputs = this.$refs.list.querySelectorAll('input[data-genotype-label]');
                inputs[inputs.length - 1]?.focus();
            });
        },
        commitAndAdd(index) {
            const current = (this.labels[index]?.value || '').trim();
            if (!current || this.labels.length >= this.maxLabels) return;
            this.addLabel();
        },
        removeLabel(index) {
            this.labels.splice(index, 1);
            if (this.labels.length === 0) {
                this.labels.push({ value: '', original: '' });
            }
        }
    }">
    <div class="card-body">
        <h5 class="required card-title mb-2">Genotype Options</h5>
        <p class="text-muted small mb-3">Enter the genotypes used for this animal model</p>

        @error('genotype_options')
            <div class="alert alert-danger py-2">{{ $message }}</div>
        @enderror

        <div class="row">
            <div class="col-md-6">
                <div class="d-flex flex-column gap-2" x-ref="list">
                    <template x-for="(label, index) in labels" :key="index">
                        <div class="d-flex align-items-center gap-2">
                            <input type="hidden" name="genotype_options_original[]" :value="label.original">
                            <input
                                type="text"
                                class="form-control @error('genotype_options') is-invalid @enderror"
                                name="genotype_options[]"
                                placeholder="Add genotype label..."
                                data-genotype-label
                                x-model="label.value"
                                :required="!hasFilledLabel && index === 0"
                                @keydown.enter.prevent="commitAndAdd(index)"
                            >
                            <button type="button" class="btn btn-outline-secondary" @click="removeLabel(index)" title="Remove genotype label">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </template>
                </div>

                <button type="button" class="btn btn-outline-primary mt-3" x-show="labels.length < maxLabels" @click="addLabel()">
                    <i class="bi bi-plus-lg"></i> Add genotype label
                </button>
                <small class="text-muted d-block mt-2" x-show="labels.length >= maxLabels">Maximum of 6 genotype labels.</small>
            </div>
        </div>
    </div>
</div>
