<div class="row">
    <div class="col-md-6 mb-3">
        <label for="cre_system_used" class="form-label">Cre System Used</label>
        <select class="form-select" id="cre_system_used" name="cre_system_used" x-model="cre"
            x-on:change="Livewire.dispatch('assignCreSystem', { creSystem: $event.target.value })"
        >
            <option value="">- No Cre Driver -</option>
            @foreach(\App\Enums\CreSystem::cases() as $type)
                <option value="{{ $type->value }}" @selected($type->value === old('cre_system_used', $animalModel->cre_system_used))>{{ $type->print() }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6 mb-3">
        <label for="creAnimalModelName" class="form-label">Name of Cre Recombinase Model</label>
        <input type="text" class="form-control" id="creAnimalModelName" name="cre_animal_model_name" value="{{ old('cre_animal_model_name', $animalModel->cre_animal_model_name) }}">
    </div>

    <div class="col-md-6 mb-0">
        <label for="tissue_lineage_specificity" class="form-label">Tissue/Lineage Specificity of Cre Line</label>
        <input type="text" class="form-control" id="tissue_lineage_specificity" name="tissue_lineage_specificity" value="{{ old('tissue_lineage_specificity', $animalModel->tissue_lineage_specificity) }}">
    </div>

    <div class="col-md-6 mb-3" x-show="cre === 'tamoxifen' || cre === 'doxycycline'">
        <label for="cre_regulation_type" class="form-label">Cre Regulation Type</label>
        <select name="cre_regulation_type" id="cre_regulation_type" class="form-select"
        x-init="$watch('cre', () => $el.value = '')"
        x-bind:disabled="!(cre === 'tamoxifen' || cre === 'doxycycline')">
        <option value="">- Select Regulation Type -</option>

            @foreach(\App\Enums\CreRegulationType::getOptionsForCreSystem(\App\Enums\CreSystem::TAMOXIFEN) as $type)
                <option value="{{ $type->value }}" @selected($type->value === old('cre_regulation_type', $animalModel->cre_regulation_type)) x-show="cre === 'tamoxifen'">{{ $type->print() }}</option>
            @endforeach

            @foreach(\App\Enums\CreRegulationType::getOptionsForCreSystem(\App\Enums\CreSystem::DOXYCYCLINE) as $type)
                <option value="{{ $type->value }}" @selected($type->value === old('cre_regulation_type', $animalModel->cre_regulation_type)) x-show="cre === 'doxycycline'">{{ $type->print() }}</option>
            @endforeach
        </select>
    </div>
</div>