@php
    $boneHisto = $upload->boneHistoDetails ?? null;
    $bodyWeightDefault = $upload->body_weight_included !== null ? (int) $upload->body_weight_included : null;
    $bodyWeightRaw = old('body_weight_included', $bodyWeightDefault);
    $bodyWeightCurrent = $bodyWeightRaw === null || $bodyWeightRaw === '' ? null : (int) $bodyWeightRaw;
@endphp

<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label required">Select Bone</label>
        <select class="form-select" name="specimen_type_id">
            <option value="">-- Select Bone --</option>
            @foreach(App\Models\SpecimenType::where('specimen_category', 'bone')->whereNot('name', 'Whole Body')->get() as $specimenType)
                <option value="{{ $specimenType->id }}" @selected(old('specimen_type_id', $upload->specimen_type_id ?? '') == $specimenType->id)>{{ $specimenType->name }}</option>
            @endforeach
        </select>
        @error('specimen_type_id')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label for="region_id" class="form-label required">Select Region</label>
        <select class="form-select" name="region_id" id="region_id">
            <option value="">-- Select Region --</option>
            @foreach(App\Models\Region::all() as $region)
                <option value="{{ $region->id }}" @selected(old('region_id', $upload->region_id ?? '') == $region->id)>{{ $region->label }}</option>
            @endforeach
        </select>
        @error('region_id')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="alert alert-info d-flex align-items-center">
            <i class="bi bi-info-circle me-2"></i>
            <span>Not sure which measurements are supported? 
                <a href="{{ route('measurement-parameters') }}#bone-histomorphometry" target="_blank" class="alert-link">
                    View Measurement Parameters Here!
                </a>
            </span>
        </div>
    </div>
</div>

<hr class="mb-0">

<div class="col-md-12 mb-4" x-data="{ hasBodyWeight: @js($bodyWeightCurrent === null ? null : (bool) $bodyWeightCurrent) }">
    <div class="col-12">
        <h5 class="mt-4 text-muted required">Body Weight</h5>
        <p>Are body weight measurements included in the spreadsheet you are about to upload?</p>
    </div>
    <div class="form-check ps-0">
        <input @click="hasBodyWeight = true" class="btn-check" type="radio" name="body_weight_included" id="body_weight_yes" value="1" @checked($bodyWeightCurrent === 1)>
        <label class="btn btn-outline-primary" for="body_weight_yes">
            Yes
        </label>
        <input @click="hasBodyWeight = false" class="btn-check" type="radio" name="body_weight_included" id="body_weight_no" value="0" @checked($bodyWeightCurrent === 0)>
        <label class="btn btn-outline-primary" for="body_weight_no">
            No
        </label>
    </div>

    <template x-if="hasBodyWeight === true">
        <div class="alert alert-success mt-3">
            <p class="m-0 p-0"><i class="bi bi-check-circle text-success"></i> Great - body weight data will be ingested along with your other measurements.</p>
        </div>
    </template>

    <template x-if="hasBodyWeight === false">
        <div class="alert alert-warning mt-3">
            <p class="m-0 p-0"><i class="bi bi-info-circle text-warning"></i> Body weight is useful context for interpreting skeletal measurements. Consider adding it to your spreadsheet before uploading if it is available, under "<strong>Body Weight</strong>" or "<strong>BW</strong>".</p>
        </div>
    </template>


    @error('body_weight_included')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

<hr class="mb-0">

<div class="row mb-4">
    <div class="col-12">
        <h5 class="mt-4 text-muted">Methodology - Required</h5>
    </div>
    <div class="col-12">
        <label for="measurement_software" class="form-label required">Measurement Software</label>
        <input type="text" class="form-control" name="measurement_software" id="measurement_software" value="{{ old('measurement_software', $boneHisto->measurement_software ?? '') }}" placeholder="e.g. Osteomeasure, Bioquant Osteo, ImageJ">
        @error('measurement_software')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>

<hr class="mb-0">

<div class="row">
    <div class="col-12">
        <h5 class="mt-4 text-muted">Histological Approach - Optional</h5>
    </div>
    <div class="col-12">
        <label for="histological_approach" class="form-label">Describe your sectioning and staining approach</label>
        <textarea class="form-control" name="histological_approach" id="histological_approach" rows="4" placeholder="e.g. Undecalcified MMA sections for dynamic parameters (calcein/alizarin double label); decalcified paraffin sections for TRAP staining and osteoclast quantification.">{{ old('histological_approach', $boneHisto->histological_approach ?? '') }}</textarea>
        @error('histological_approach')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror

        <small class="text-muted">Include section type, stains used, and any labeling agents if applicable.</small>
    </div>
</div>
