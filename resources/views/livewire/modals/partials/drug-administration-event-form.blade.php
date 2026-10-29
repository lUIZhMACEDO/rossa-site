@php
    use App\Models\DrugAdministrationEvent;
@endphp


{{-- Single Event Form Card --}}
<div class="card mb-3 bg-light">
    <span class="fw-bold text-white px-3 py-1" style="position: absolute; top: -12px; left: 15px; background: var(--bs-primary); border-radius: 12px; font-size: 12px;">
        Event {{ $index + 1 }}
    </span>

    <div class="card-body pt-4">
        <div class="d-flex justify-content-end mb-3">
            <button 
                type="button" 
                class="btn btn-sm btn-outline-danger"
                wire:click="removeEvent({{ $index }})"
                @if(count($events) === 1) disabled @endif>
                <i class="bi bi-trash"></i> Remove Event
            </button>
        </div>

        {{-- Drug/Molecule Selection --}}
        <div class="row mb-3">
            <div class="col-md-12">
                <label class="form-label required">Drug/Molecule</label>
                <livewire:molecule-search
                    :molecule-id="$events[$index]['molecule_id']"
                    wire:key="molecule-search-{{ $index }}"
                    wire:model="events.{{ $index }}.molecule_id"
                />
            </div>
        </div>

        {{-- Drug Source --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label required">Drug Source Type</label>
                <select 
                    class="form-select @error('events.' . $index . '.drug_source_type') is-invalid @enderror"
                    wire:model.defer="events.{{ $index }}.drug_source_type">
                    @foreach(DrugAdministrationEvent::$drug_source_type_options as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('events.' . $index . '.drug_source_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Drug Source Details</label>
                <input 
                    type="text" 
                    class="form-control"
                    wire:model.defer="events.{{ $index }}.drug_source_details"
                    placeholder="Company name, catalog number, or details...">
                <small class="form-text text-muted">e.g., Sigma-Aldrich Cat# P1234</small>
            </div>
        </div>

        {{-- Dose and Route --}}
        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label required">Dose</label>
                <input 
                    type="number" 
                    class="form-control @error('events.' . $index . '.dose') is-invalid @enderror"
                    wire:model.defer="events.{{ $index }}.dose"
                    placeholder="80"
                    step="0.01"
                    min="0">
                @error('events.' . $index . '.dose')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-3">
                <label class="form-label required">Dose Unit</label>
                <select 
                    class="form-select @error('events.' . $index . '.dose_unit') is-invalid @enderror"
                    wire:model.defer="events.{{ $index }}.dose_unit">
                    @foreach(DrugAdministrationEvent::$dose_unit_options as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('events.' . $index . '.dose_unit')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label required">Route of Administration</label>
                <select 
                    class="form-select @error('events.' . $index . '.route') is-invalid @enderror"
                    wire:model.defer="events.{{ $index }}.route">
                    @foreach(DrugAdministrationEvent::$route_options as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('events.' . $index . '.route')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Vehicle / Formulation --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label required">Vehicle / Formulation</label>
                <select
                    class="form-select @error('events.' . $index . '.vehicle_id') is-invalid @enderror"
                    wire:model.live="events.{{ $index }}.vehicle_id">
                    <option value="">Select a vehicle...</option>
                    @foreach(\App\Models\Vehicle::orderBy('name')->get() as $vehicle)
                        <option value="{{ $vehicle->id }}">{{ $vehicle->name }}</option>
                    @endforeach
                    <option value="other">Other (specify)...</option>
                </select>
                @error('events.' . $index . '.vehicle_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">What solution was the drug resuspended in?</small>
            </div>

            @if(isset($event['vehicle_id']) && $event['vehicle_id'] === 'other')
                <div class="col-md-6">
                    <label class="form-label required">Specify vehicle:</label>
                    <input
                        type="text"
                        class="form-control @error('events.' . $index . '.vehicle_other') is-invalid @enderror"
                        wire:model.defer="events.{{ $index }}.vehicle_other"
                        placeholder="e.g., 30% propylene glycol in saline">
                    @error('events.' . $index . '.vehicle_other')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            @endif
        </div>

        {{-- Administration Volume --}}
        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label">Administration Volume</label>
                <input
                    type="number"
                    class="form-control @error('events.' . $index . '.administration_volume') is-invalid @enderror"
                    wire:model.defer="events.{{ $index }}.administration_volume"
                    placeholder="5"
                    step="0.001"
                    min="0">
                @error('events.' . $index . '.administration_volume')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Volume given (matches the vehicle control)</small>
            </div>
            <div class="col-md-3">
                <label class="form-label">Volume Unit</label>
                <select
                    class="form-select"
                    wire:model.defer="events.{{ $index }}.administration_volume_unit">
                    <option value="ml/kg">ml/kg</option>
                    <option value="μl">μl</option>
                    <option value="ml">ml</option>
                </select>
            </div>
        </div>

        @if(isset($event['route']) && $event['route'] === 'other')
            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="form-label">Please specify route:</label>
                    <input 
                        type="text" 
                        class="form-control"
                        wire:model.defer="events.{{ $index }}.route_other"
                        placeholder="Specify administration route...">
                </div>
            </div>
        @endif

        {{-- Timing and Frequency - Row 2: Frequency --}}
        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label required">Frequency</label>
                <input 
                    type="number" 
                    class="form-control @error('events.' . $index . '.frequency') is-invalid @enderror"
                    wire:model.defer="events.{{ $index }}.frequency"
                    placeholder="1"
                    step="0.1"
                    min="0">
                @error('events.' . $index . '.frequency')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="col-md-3">
                <label class="form-label required">Frequency Unit</label>
                <select 
                    class="form-select @error('events.' . $index . '.frequency_unit') is-invalid @enderror"
                    wire:model.live="events.{{ $index }}.frequency_unit">
                    @foreach(DrugAdministrationEvent::$frequency_unit_options as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('events.' . $index . '.frequency_unit')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            {{-- Conditional field for "Every X days" (interval is independent of frequency count) --}}
            @if(isset($event['frequency_unit']) && $event['frequency_unit'] === 'every X days')
                <div class="col-md-3">
                    <label class="form-label required">Every how many days?</label>
                    <input 
                        type="number" 
                        class="form-control @error('events.' . $index . '.frequency_unit_other') is-invalid @enderror"
                        wire:model.defer="events.{{ $index }}.frequency_unit_other"
                        placeholder="3"
                        min="1"
                        step="1">
                    @error('events.' . $index . '.frequency_unit_other')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">e.g., frequency 1 + 2 days = 1 every 2 days</small>
                </div>
            @endif
            
            {{-- Conditional field for "Other" --}}
            @if(isset($event['frequency_unit']) && $event['frequency_unit'] === 'other')
                <div class="col-md-6">
                    <label class="form-label">Specify frequency:</label>
                    <input 
                        type="text" 
                        class="form-control"
                        wire:model.defer="events.{{ $index }}.frequency_unit_other"
                        placeholder="e.g., twice monthly, as needed">
                    <small class="form-text text-muted">Describe custom frequency</small>
                </div>
            @endif

            {{-- Timing and Frequency --}}
            
            <div class="col-md-3">
                <label class="form-label required">Start Day</label>
                <input 
                    type="number" 
                    class="form-control @error('events.' . $index . '.start_day') is-invalid @enderror"
                    wire:model.live="events.{{ $index }}.start_day"
                    placeholder="0"
                    min="0">
                @error('events.' . $index . '.start_day')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Day relative to start</small>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">End Day</label>
                <input 
                    type="number" 
                    class="form-control @error('events.' . $index . '.end_day') is-invalid @enderror"
                    wire:model.live="events.{{ $index }}.end_day"
                    placeholder="28"
                    min="0">
                @error('events.' . $index . '.end_day')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Leave blank for single dose</small>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Duration</label>
                @php
                    $startDay = $event['start_day'] ?? 0;
                    $endDay = $event['end_day'] ?? null;
                    
                    if ($endDay === null || $endDay === '' || $endDay == $startDay) {
                        $duration = 'Single dose';
                    } else {
                        $durationDays = $endDay - $startDay;
                        $duration = $durationDays . ' day' . ($durationDays != 1 ? 's' : '');
                    }
                @endphp
                <input 
                    type="text" 
                    class="form-control text-muted fst-italic bg-light" 
                    value="{{ $duration }}"
                    readonly
                    disabled>
                <small class="form-text text-muted">Auto-calculated</small>
            </div>
        </div>
    </div>
</div>
