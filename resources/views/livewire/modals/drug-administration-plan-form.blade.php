<div id="drug-administration-plan-form" @if($study->isLockedForEditing(auth()->user())) data-input-locked @endif>
    @php
        use App\Models\DrugAdministrationEvent;
    @endphp

    {{-- Plan Metadata --}}
    <div class="card mb-4" style="border-radius: 0.375rem; overflow: hidden; border-left: 4px solid #0D3B52;">
        <div class="card-title text-white p-3 mb-0" style="background-color: #0D3B52;">
            <h5 class="mb-0">Plan Information</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label required">Drug Administration Plan Name</label>
                    <input 
                        type="text" 
                        class="form-control form-control-lg @error('plan_name') is-invalid @enderror" 
                        wire:model.defer="plan_name"
                        placeholder="e.g., PTH Treament or Vehicle Control Treatment">
                    @error('plan_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Give this drug administration plan a descriptive name</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label required">Animal Age at Start of Treatment (weeks)</label>
                    <input 
                        type="number" 
                        class="form-control form-control-lg @error('animal_age_at_day_zero') is-invalid @enderror"
                        wire:model.defer="animal_age_at_day_zero"
                        placeholder="e.g., 8"
                        min="0">
                    @error('animal_age_at_day_zero')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">How old are animals when treatment starts?</div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <label class="form-label">Description (Optional)</label>
                    <textarea 
                        class="form-control" 
                        rows="2" 
                        wire:model.defer="description"
                        placeholder="Additional notes about this treatment plan..."></textarea>
                    <div class="form-text">Any additional context or notes</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Administration Events --}}
    <div class="card mb-4" style="border-radius: 0.375rem; overflow: hidden; border-left: 4px solid #0D3B52;">
        <div class="card-title text-white p-3 mb-0" style="background-color: #0D3B52;">
            <h5 class="mb-0">Administration Events</h5>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">Add one or more drug administration events. Each event represents a drug given at specific timepoints.</p>

            @error('events')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror

            @foreach ($events as $index => $event)
                @include('livewire.modals.partials.drug-administration-event-form', ['index' => $index, 'event' => $event])
            @endforeach

            {{-- Add Event Button --}}
            @if($study->isLockedForEditing(auth()->user()))
                <div class="text-center py-4 bg-light text-muted" style="border: 2px dashed #6c757d; border-radius: 6px; cursor: not-allowed; opacity: 0.65;" aria-disabled="true" title="This study is locked for editing.">
            @else
                <div class="text-center py-4 bg-light" style="border: 2px dashed #6c757d; border-radius: 6px; cursor: pointer;" wire:click="addEvent">
            @endif
                    <i class="bi bi-plus-circle text-primary" style="font-size: 1.5rem;"></i>
                    <div class="mt-2"><strong>Add Another Administration Event</strong></div>
                    <small class="text-muted">For sequential or combination therapies</small>
                </div>
        </div>
    </div>

    {{-- Timeline Preview --}}
    @if ($plan_name && $animal_age_at_day_zero && count($events) > 0)
        <div class="card mb-4 bg-info bg-opacity-10" style="border: 1px solid #b6d4fe;">
            <div class="card-body">
                <h6 class="mb-3"><i class="bi bi-clock-history"></i> Timeline Preview</h6>
                <p class="text-muted mb-3">This is how your treatment plan will be displayed:</p>
                
                <div class="alert alert-info">
                    <strong>{{ $plan_name }}</strong><br>
                    <small>Animals start treatment at <strong>{{ $animal_age_at_day_zero }} weeks old</strong></small>
                </div>

                @foreach ($events as $event)
                    @php $drugName = App\Models\Molecule::find($event['molecule_id'])->pref_name ?? null; @endphp
                    @if ($drugName && $event['dose'])
                        @php
                            $startAge = $animal_age_at_day_zero + (int)floor($event['start_day'] / 7);
                            $endDay = $event['end_day'] ?? $event['start_day'];
                            $endAge = $animal_age_at_day_zero + (int)ceil($endDay / 7);
                            
                            if ($event['end_day'] === null || $event['end_day'] == $event['start_day']) {
                                $dayRange = "Day {$event['start_day']}";
                                $ageRange = "{$startAge} weeks old";
                            } else {
                                $dayRange = "Day {$event['start_day']}-{$event['end_day']}";
                                $ageRange = "{$startAge}-{$endAge} weeks old";
                            }

                            $frequencyStr = match ($event['frequency_unit']) {
                                'per day' => $event['frequency'] == 1 ? 'daily' : "{$event['frequency']}x/day",
                                'per week' => "{$event['frequency']}x/week",
                                'dose' => 'single dose',
                                'every X days' => filled($event['frequency_unit_other'] ?? null)
                                    ? "{$event['frequency']} every {$event['frequency_unit_other']} days"
                                    : "{$event['frequency']} every X days",
                                'other' => filled($event['frequency_unit_other'] ?? null)
                                    ? "{$event['frequency']} {$event['frequency_unit_other']}"
                                    : (string) $event['frequency'],
                                default => trim("{$event['frequency']} {$event['frequency_unit']}"),
                            };

                            $routeAbbrev = [
                                'intraperitoneal_injection' => 'IP',
                                'subcutaneous_injection' => 'SC',
                                'intramuscular_injection' => 'IM',
                                'intravenous_injection' => 'IV',
                                'oral_gavage' => 'PO',
                                'drinking_water' => 'water',
                                'food' => 'food',
                            ][$event['route']] ?? $event['route'];
                        @endphp
                        
                        <div class="mb-2" style="border-left: 3px solid #0d6efd; padding-left: 10px; font-family: 'Courier New', monospace; font-size: 13px;">
                            @php
                                $molecule = App\Models\Molecule::find($event['molecule_id']) ?? null;
                                $drugName = $molecule ? ($molecule->name ?? $molecule->pref_name ?? 'Unknown') : 'Unknown';
                            @endphp
                            <strong>{{ $dayRange }} ({{ $ageRange }}):</strong> {{ $drugName }} {{ $event['dose'] }} {{ $event['dose_unit'] }} {{ $routeAbbrev }}, {{ $frequencyStr }}
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    <div class="d-flex align-items-center justify-content-between mt-4">
        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show flex-grow-1" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="ms-auto d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" wire:click="cancel">
                <i class="bi bi-x-circle"></i> Cancel
            </button>
            <button type="button" class="btn btn-primary" wire:click="save">
            <i class="bi bi-save"></i> 
            @if ($planId)
                Update Plan
            @else
                Create Plan
            @endif
            </button>
        </div>
    </div>
</div>