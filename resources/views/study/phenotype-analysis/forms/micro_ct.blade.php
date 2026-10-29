@php
    $microCt = $upload->microCtDetails ?? null;
    $bodyWeightDefault = $upload->body_weight_included !== null ? (int) $upload->body_weight_included : null;
    $bodyWeightRaw = old('body_weight_included', $bodyWeightDefault);
    $bodyWeightCurrent = $bodyWeightRaw === null || $bodyWeightRaw === '' ? null : (int) $bodyWeightRaw;
@endphp

<div class="row">
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
    <div class="col-md-6 mb-4">
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
            <h5 class="mt-4 text-muted">Acquisition - Required</h5>
        </div>
        <div class="col-md-4">
            <label for="voxel_size" class="form-label required">Voxel Size (μm)</label>
            <input type="number" class="form-control" name="voxel_size" id="voxel_size" value="{{ old('voxel_size', $microCt->voxel_size ?? '') }}" step="any" placeholder="e.g. 10.4">
            <small class="text-muted">Isotropic voxel edge length</small>
            @error('voxel_size')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="scan_mode" class="form-label required">Scan Mode</label>
            <select class="form-control form-select" name="scan_mode" id="scan_mode">
                <option value="">-- Select Scan Mode -- </option>
                <option value="in_vivo" @selected(old('scan_mode', $microCt->scan_mode ?? '') === 'in_vivo')>In Vivo</option>
                <option value="ex_vivo" @selected(old('scan_mode', $microCt->scan_mode ?? '') === 'ex_vivo')>Ex Vivo</option>
            </select>
            @error('scan_mode')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="scanner" class="form-label required">Scanner</label>
            <input type="text" class="form-control" name="scanner" id="scanner" value="{{ old('scanner', $microCt->scanner ?? '') }}" placeholder="e.g. Scanco μCT 35">
            @error('scanner')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <hr class="mb-0">

    <div class="row">
        <div class="col-12">
            <h5 class="mt-4 text-muted">Acquisition Parameters - Optional</h5>
        </div>
        <div class="col-md-4">
            <label for="xray_tube_potential" class="form-label">X-ray Tube Potential (kV)</label>
            <input type="number" class="form-control" name="xray_tube_potential" id="xray_tube_potential" value="{{ old('xray_tube_potential', $microCt->xray_tube_potential ?? '') }}" step="any" placeholder="e.g. 70">
            @error('xray_tube_potential')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="integration_time" class="form-label">Integration Time (ms)</label>
            <input type="number" class="form-control" name="integration_time" id="integration_time" value="{{ old('integration_time', $microCt->integration_time ?? '') }}" step="any" placeholder="e.g. 400">
            @error('integration_time')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="analytics_software" class="form-label">Analytics Software</label>
            <input type="text" class="form-control" name="analytics_software" id="analytics_software" value="{{ old('analytics_software', $microCt->analytics_software ?? '') }}" placeholder="e.g. CTAn 1.18, Dragonfly">
            @error('analytics_software')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>
