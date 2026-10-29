<div class="col-md-6">
    <label class="form-label required">Specimen Type</label>
    <select class="form-select" name="specimen_type_id">
        <option value="">-- Select Specimen Type --</option>
        @foreach(App\Models\SpecimenType::where('specimen_category', 'fluid')->get() as $specimenType)
            <option value="{{ $specimenType->id }}" @selected(old('specimen_type_id', $upload->specimen_type_id ?? '') == $specimenType->id)>
                {{ $specimenType->name }}
            </option>
        @endforeach
    </select>
    @error('specimen_type_id')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
