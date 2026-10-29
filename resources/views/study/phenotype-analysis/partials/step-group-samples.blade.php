@php 
    // Find the column index for sample_identifier
    $sampleIdentifierMeasurement = \App\Models\Measurement::where('name', 'sample_identifier')->first();
    $sampleIdentifierColIndex = collect($upload->column_measurement_mappings)->search($sampleIdentifierMeasurement?->id) ?? null;
@endphp

@if ( $upload->samples()->count() > 0 && $upload->group_cache )
    <div class="alert alert-warning">
        <strong>Note:</strong> You are previewing adjustments to this upload's sample group assignments. These changes have not yet applied to the samples.
    </div>
@endif

<div class="alert mb-4 d-flex align-items-center" role="alert" style="background-color: rgba(26, 125, 181, 0.08); border-left: 4px solid #1A7DB5; color: #14273f;">
    <i class="bi bi-info-circle-fill me-2" style="color: #1A7DB5; font-size: 1.5rem;"></i>
    <span><strong>Note:</strong> Match your sample to the appropriate sample group by clicking on the button and leave any samples that do not belong to a group unselected.</span>
</div>

<div class="position-sticky px-3" style="top: 0; z-index: 10; background-color: #0D3B52; left: 0; right: 0;">
    <div class="row py-2 align-items-end">
        <div class="col"></div>
        @foreach($study->sampleGroups as $sampleGroup)
            <div class="col small" style="color: #E6F1FB;">
                <p class="mb-0">Animal: {{ $sampleGroup->animalModel?->experimentalStrainLabel() ?? '' }}</p>
                <p class="mb-0">Genotype: {{ $sampleGroup->genotypeWithAllele() }}</p>
                <p class="mb-0">Sex: {{ $sampleGroup->sex }}</p>
                <p class="mb-0">Age: {{ $sampleGroup->age }}wk</p>
            </div>
        @endforeach
        <div class="col d-flex align-items-end justify-content-start" style="margin-left: -20px;">
            <button type="button" class="btn btn-primary btn-sm fw-bold" onclick="document.getElementById('saveGroupingsBtn').click();" style="line-height: 1.2; border: 2px solid #fff !important;">
                Save<br>Groupings
            </button>
        </div>
    </div>

    <div class="row py-2 mb-3 align-items-center" style="border-bottom: 2px solid #0D3B52;">
        <div class="col fw-bold" style="color: #fff; font-size: 1rem;">Sample Name</div>
        @foreach($study->sampleGroups as $sampleGroup)
            <div class="col fw-bold" style="color: #fff; font-size: 1rem;">
                {{ $sampleGroup->label ?? '-No Label-' }}
            </div>
        @endforeach
        <div class="col"></div>
    </div>
</div>

@foreach($samples as $sample)
    <div class="row align-items-center equ-height-columns border-bottom">
        <div class="col">
            <strong style="color: #14273f;">
                {{ $sample->label ?? $sample->raw_upload_data[$sampleIdentifierColIndex] ?? 'Row ' . ($sample->row_index + 1) }}
            </strong>
        </div>
        @foreach($study->sampleGroups as $sampleGroup)
            <div class="col text-center py-2">
                <input type="radio" 
                        name="sample_group_id[{{ $sample->row_index }}]" 
                        id="sample_group_{{ $sampleGroup->id }}_{{ $sample->row_index }}" 
                        value="{{ $sampleGroup->id }}" 
                        class="btn-check"
                        @checked(old('sample_group_id.' . $sample->row_index, $sample->sample_group_id == $sampleGroup->id))>
                <label for="sample_group_{{ $sampleGroup->id }}_{{ $sample->row_index }}" class="btn btn-sm btn-outline-primary">
                    {{ $sampleGroup->label ?? '-No Label-' }}
                </label>
            </div>
        @endforeach
        <div class="col">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearRowSelection({{ $sample->row_index }})">
                Clear
            </button>
        </div>
    </div>
@endforeach

<script>
    function clearRowSelection(rowIndex) {
        const radios = document.querySelectorAll(`input[name="sample_group_id[${rowIndex}]"]`);
        radios.forEach(radio => radio.checked = false);
    }
</script>