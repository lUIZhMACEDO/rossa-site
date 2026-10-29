
<div class="mb-3">
    <label class="form-label">Drug Name</label>
    <livewire:molecule-search :molecule-id="$animalModel->inducibleDrugTreatment->molecule_id ?? null" />
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="dose" class="form-label">Dose</label>
        <input type="number" 
            class="form-control @error('dose') is-invalid @enderror" 
            id="dose" 
            name="dose" 
            value="{{ old('dose', $animalModel->inducibleDrugTreatment->dose ?? '') }}"
            placeholder="1">
        @error('dose')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="dose_unit" class="form-label">Dose Unit</label>
        <select class="form-select @error('dose_unit') is-invalid @enderror" 
                id="dose_unit" 
                name="dose_unit">
            <option value="">Select unit</option>
            @foreach(\App\Models\InducibleDrugTreatment::$drug_unit_options as $key => $value)
                <option value="{{ $key }}" @selected(old('dose_unit', $animalModel->inducibleDrugTreatment->dose_unit ?? '') === $key)>{{ $value }}</option>
            @endforeach
        </select>
        @error('dose_unit')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="duration" class="form-label">Duration</label>
        <input type="number" 
            class="form-control @error('duration') is-invalid @enderror" 
            id="duration" 
            name="duration" 
            value="{{ old('duration', $animalModel->inducibleDrugTreatment->duration ?? '') }}"
            placeholder="1">
        @error('duration')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="duration_unit" class="form-label">Duration Unit</label>
        <select class="form-select @error('duration_unit') is-invalid @enderror" 
                id="duration_unit" 
                name="duration_unit">
            <option value="">Select unit</option>
            @foreach(\App\Models\InducibleDrugTreatment::$drug_duration_unit_options as $key => $value)
                <option value="{{ $key }}" @selected(old('duration_unit', $animalModel->inducibleDrugTreatment->duration_unit ?? '') === $key)>{{ $value }}</option>
            @endforeach
        </select>
        @error('duration_unit')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="frequency" class="form-label">Frequency</label>
        <select class="form-select @error('frequency') is-invalid @enderror" 
                id="frequency" 
                name="frequency">
            <option value="">Select frequency</option>
            @foreach(\App\Models\InducibleDrugTreatment::$drug_frequency_unit_options as $key => $value)
                <option value="{{ $key }}" @selected(old('frequency', $animalModel->inducibleDrugTreatment->frequency ?? '') === $key)>{{ $value }}</option>
            @endforeach
        </select>
        @error('frequency')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3" x-show="frequency === 'other'">
        <label for="frequency_other" class="form-label">Other Frequency</label>
        <input type="text" 
            class="form-control @error('frequency_other') is-invalid @enderror" 
            id="frequency_other" 
            name="frequency_other"
            value="{{ old('frequency_other', $animalModel->inducibleDrugTreatment->frequency_other ?? '') }}"
            placeholder="Specify frequency">
        @error('frequency_other')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="drug_route" class="form-label">Drug Route</label>
        <select class="form-select @error('drug_route') is-invalid @enderror" 
                id="drug_route" 
                name="drug_route">
            <option value="">Select route</option>
            @foreach(\App\Models\InducibleDrugTreatment::$drug_route_options as $key => $value)
                <option value="{{ $key }}" @selected(old('drug_route', $animalModel->inducibleDrugTreatment->drug_route ?? '') === $key)>{{ $value }}</option>
            @endforeach
        </select>
        @error('drug_route')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3" x-show="drug_route === 'other'">
        <label for="drug_route_other" class="form-label">Other Drug Route</label>
        <input type="text" 
            class="form-control @error('drug_route_other') is-invalid @enderror" 
            id="drug_route_other" 
            name="drug_route_other" 
            value="{{ old('drug_route_other', $animalModel->inducibleDrugTreatment->drug_route_other ?? '') }}"
            placeholder="Specify route">
        @error('drug_route_other')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-0">
        <label for="impact" class="form-label">Impact</label>
        <select class="form-select @error('impact') is-invalid @enderror" 
                id="impact" 
                name="impact">
            <option value="">Select impact</option>
            @foreach(\App\Models\InducibleDrugTreatment::$impact_options as $key => $value)
                <option value="{{ $key }}" @selected(old('impact', $animalModel->inducibleDrugTreatment->impact ?? '') === $key)>{{ $value }}</option>
            @endforeach
        </select>
        @error('impact')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>