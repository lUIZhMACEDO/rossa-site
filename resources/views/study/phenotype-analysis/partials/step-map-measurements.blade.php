@php 
    $columnSummary = $upload->uploadService()->generateColumnSummary();
    $bodyWeightMeasurement = \App\Models\Measurement::where('name', 'weight')->first();
    
    // use old() values if available (for validation error redirects), otherwise use saved values
    $mappingsForView = old('column_measurement_mappings', $upload->column_measurement_mappings);
    // get custom values
    $customCheckboxesForView = old('custom_measurement_checkbox', 
        collect($upload->column_measurement_mappings)->mapWithKeys(function($value, $key) use ($upload) {
            $m = $upload->measurements->firstWhere('id', $value);
            return [$key => $m && $m->study_id ? '1' : null];
        })->toArray()
    );
    $customMeasurementsForView = old('custom_measurements', 
        collect($upload->column_measurement_mappings)->mapWithKeys(function($value, $key) use ($upload) {
            $m = $upload->measurements->firstWhere('id', $value);
            return [$key => $m && $m->study_id ? $m->display_name : ''];
        })->toArray()
    );
    $customUnitsForView = old('custom_units',
        collect($upload->column_measurement_mappings)->mapWithKeys(function($value, $key) use ($upload) {
            $m = $upload->measurements->firstWhere('id', $value);
            return [$key => $m && $m->study_id ? $m->unit : ''];
        })->toArray()
    );

    // pre-compute measurement groups
    // partition by standard and custom measurements          
    $measurementGroups = $upload->measurements->partition(fn($m) => !$m->study_id);
    // group
    $standardMeasurements = $measurementGroups[0]->groupBy('grouping');
    // sort groupings alphabetically
    $standardMeasurements = $standardMeasurements->sortKeysUsing(function ($a, $b) {
        if (empty($a)) return 1;
        if (empty($b)) return -1;
        return strcmp($a, $b);
    });
    // sort measurements by display name
    $standardMeasurements = $standardMeasurements->map(fn($measurements) => $measurements->sortBy('display_name'));
    $customMeasurementsList = $measurementGroups[1]->sortBy('display_name');
@endphp

{{-- This ID is used by the mechanical-testing component to scroll here after submission --}}
<div id="step-map-measurements" x-data="{
    column_measurement_mappings: {{ Js::from($mappingsForView) }},
    custom_measurement_checkbox: {{ Js::from(
        collect($customCheckboxesForView)->map(fn($v) => (bool) $v)
    ) }}
}">
    @if ($upload->body_weight_included === true && $bodyWeightMeasurement)
        <div x-show="!Object.values(column_measurement_mappings).some(v => v == '{{ $bodyWeightMeasurement->id }}')" x-cloak>
            <div class="alert alert-warning d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <span>Body weight is expected in this upload but has not yet been mapped. Please map a column to <strong>Body Weight</strong>.</span>
            </div>
        </div>
    @endif

    <div class="row py-1 align-items-end" style="background-color: #0D3B52;">
        <div class="col-3">
            <p class="small mb-0 fst-italic" style="color: #E6F1FB;">Your CSV file column headers</p>
        </div>
        <div class="col-2"></div>
        <div class="col-5">
            <p class="small mb-0 fst-italic" style="color: #E6F1FB;">If not matched, check the dropdown.</p>
        </div>
        <div class="col-2">
            <p class="small mb-0 fst-italic" style="color: #E6F1FB;">Add a measurement, if not in the dropdown.</p>
        </div>
    </div>

    <div class="row py-2 mb-1 align-items-end" style="background-color: #0D3B52; border-bottom: 2px solid #0D3B52;">
        <div class="col-3">
            <h5 class="mb-0 fw-bold" style="color: #fff; font-size: 1rem;">Source File Header</h5>
        </div>
        <div class="col-2">
            <h5 class="mb-0 fw-bold"style="color: #fff; font-size: 1rem;">Mapping Indicator</h5>
        </div>
        <div class="col-5">
            <h5 class="mb-0 fw-bold" style="color: #fff; font-size: 1rem;">Mapped Measurements</h5>
        </div>
        <div class="col-2">
            <h5 class="mb-0 fw-bold" style="color: #fff; font-size: 1rem;">Custom Measurement</h5>
        </div>
    </div>

    @foreach($upload->column_measurement_mappings as $colIndex => $measurementId)
        @php 
            $hasColumnError = $errors->has("column_measurement_mappings.{$colIndex}");
            $hasCustomError = $errors->has("custom_measurements.{$colIndex}");
            $hasRowError = $errors->has("row_error.{$colIndex}");
            $hasAnyError = $hasColumnError || $hasCustomError || $hasRowError;
            $isMapped = !empty($measurementId);
            $isCustomChecked = !empty($customCheckboxesForView[$colIndex]);
        @endphp
    <div class="row border-bottom equ-height-columns py-2 @if($hasAnyError) bg-danger bg-opacity-10 @endif">
        <div class="col-3 fw-semibold">{{ $columnSummary[$colIndex]['headerContent'] ?? 'Unlabelled Column ' . ($colIndex + 1) }}</div>
        <div class="col-2">
            @if($hasAnyError)
                <i class="bi bi-x-circle-fill text-danger fs-5"></i>
            @else
                <i class="bi fs-5 @if($isMapped) bi-check-circle-fill text-success @else bi-exclamation-triangle-fill text-warning @endif" x-bind:class="{ 'bi-check-circle-fill text-success': column_measurement_mappings[{{ $colIndex }}] !== '', 'bi-exclamation-triangle-fill text-warning': !column_measurement_mappings[{{ $colIndex }}] }"></i>
            @endif
            <i class="bi-arrow-right mx-auto fs-5"></i>
        </div>
        <div class="col-5">
            <div x-show="!custom_measurement_checkbox[{{ $colIndex }}]" @if($isCustomChecked) x-cloak @endif class="w-100">
                <select name="column_measurement_mappings[{{ $colIndex }}]" id="column_measurement_mappings_{{ $colIndex }}" class="form-select form-select-sm mt-1 fw-semibold @if($hasColumnError || $hasRowError) is-invalid @endif" style="field-sizing: content; color: #14273f;" x-model="column_measurement_mappings[{{ $colIndex }}]" data-typeahead>
                    <optgroup label="No Mapping">
                        <option value="">-- Unmapped Column {{ $colIndex + 1 }} --</option>
                    </optgroup>
                    
                    @foreach($standardMeasurements as $groupingTitle => $measurements)
                        <optgroup label="{{ $groupingTitle ?: 'Other Measurements' }}">
                            @foreach($measurements as $measurement)
                                <option
                                    value="{{ $measurement->id }}"
                                    @if($measurementId == $measurement->id) selected @endif
                                    data-synonyms="{{ implode(' ', $measurement->synonyms ?: []) }}"
                                >
                                    {{ $measurement->display_name }}
                                    @if ( $measurement->unit )
                                        ( {{ $measurement->unit }} )
                                    @endif
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                    
                    <optgroup label="Custom Measurements ({{ count($customMeasurementsList) }})">
                        @foreach($customMeasurementsList as $measurement)
                            <option
                                value="{{ $measurement->id }}"
                                @if($measurementId == $measurement->id) selected @endif
                                data-synonyms="{{ implode(' ', $measurement->synonyms ?: []) }}"
                            >
                                {{ $measurement->display_name }}
                                @if ( $measurement->unit )
                                    ( {{ $measurement->unit }} )
                                @endif

                                @if ( $measurement->study_id )
                                    [Custom]
                                @endif
                            </option>
                        @endforeach
                    </optgroup>
                </select>
                @error("column_measurement_mappings.{$colIndex}")
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                @error("row_error.{$colIndex}")
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            
            <div x-show="custom_measurement_checkbox[{{ $colIndex }}]" @if(!$isCustomChecked) x-cloak @endif class="w-100">
                <div class="d-flex w-100">
                    <input type="text" name="custom_measurements[{{ $colIndex }}]" class="form-control form-control-sm @error("custom_measurements.{$colIndex}") is-invalid @enderror" placeholder="Custom Measurement Name" value="{{ $customMeasurementsForView[$colIndex] ?? '' }}" x-bind:disabled="!custom_measurement_checkbox[{{ $colIndex }}]" />
                    <input type="text" name="custom_units[{{ $colIndex }}]" class="ms-2 form-control form-control-sm" placeholder="Custom Unit" value="{{ $customUnitsForView[$colIndex] ?? '' }}" x-bind:disabled="!custom_measurement_checkbox[{{ $colIndex }}]" />
                </div>
                @error("custom_measurements.{$colIndex}")
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            
        </div>
        <div class="col-2">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="custom_measurement_checkbox[{{ $colIndex }}]"  id="checkCustomMeasurement{{ $colIndex }}" value="1" @checked(!empty($customCheckboxesForView[$colIndex])) x-model="custom_measurement_checkbox[{{ $colIndex }}]" />
                <label class="form-check-label fw-semibold" for="checkCustomMeasurement{{ $colIndex }}" style="color: #14273f;">
                    Custom
                </label>
            </div>
        </div>
    </div>
    @endforeach
</div>


<style>
    .equ-height-columns > [class*='col-'] {
        display: flex;
        align-items: center;
    }
</style>