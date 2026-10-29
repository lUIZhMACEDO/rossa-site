<div>

    {{-- Plan Information --}}
    <div class="card mb-4" style="border-radius: 0.375rem; overflow: hidden; border-left: 4px solid #0D3B52;">
        <div class="card-title text-white p-3 mb-0" style="background-color: #0D3B52;">
            <h5 class="mb-0">Plan Information</h5>
        </div>
        <div class="card-body">

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    Intervention Plan Name <span class="text-danger">*</span>
                </label>
                <input type="text" wire:model="planName" class="form-control"
                       placeholder="e.g., High-Fat Diet - 60% kcal (Treatment)">
                @error('planName') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                <div class="form-text">
                    Include a distinguishing detail (dose, percentage, or Treatment/Control). Does not need to
                    name an animal group, one plan can be linked to more than one Sample Group.
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    Animal Age at Start of Intervention (weeks) <span class="text-danger">*</span>
                </label>
                <input type="number" wire:model="animalAgeAtDayZero" class="form-control" placeholder="e.g., 8">
                @error('animalAgeAtDayZero') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                <div class="form-text">Distinct from age at analysis</div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Description (Optional)</label>
            <textarea wire:model="description" rows="2" class="form-control"
                      placeholder="Additional notes about this intervention plan..."></textarea>
        </div>

        </div>
    </div>

    {{-- Intervention Events --}}
    <div class="card mb-4" style="border-radius: 0.375rem; overflow: hidden; border-left: 4px solid #0D3B52;">
        <div class="card-title text-white p-3 mb-0" style="background-color: #0D3B52;">
            <h5 class="mb-0">Intervention Events</h5>
        </div>
        <div class="card-body">

        <p class="text-muted mb-3">
            Add one or more intervention events. Each event represents a single intervention applied at specific timepoints.
        </p>

        @foreach ($events as $index => $event)
            <div wire:key="event-{{ $index }}" class="bg-light border rounded p-4 mb-4">

                <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="badge bg-primary rounded-pill px-3 py-2">Event {{ $index + 1 }}</span>
                @if (count($events) > 1)
                    <button type="button" wire:click="removeEvent({{ $index }})" class="btn btn-sm btn-outline-danger">
                        Remove Event
                    </button>
                @endif
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Intervention Category <span class="text-danger">*</span>
                    </label>
                    <select wire:model="events.{{ $index }}.intervention_category_id"
                            wire:change="categoryChanged({{ $index }})"
                            class="form-select">
                        @foreach ($this->categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error("events.$index.intervention_category_id") <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    <div class="form-text">Broad grouping, used for filtering in the Data Center</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Specific Type <span class="text-danger">*</span>
                    </label>
                    <select wire:model.live="events.{{ $index }}.intervention_specific_type_id" class="form-select">
                        @php
                            $selectedCategory = $this->categories->firstWhere('id', (int) $event['intervention_category_id']);
                        @endphp
                        @foreach ($selectedCategory?->specificTypes ?? [] as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                    @error("events.$index.intervention_specific_type_id") <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    <div class="form-text">Options depend on the selected category</div>
                </div>
            </div>

            @if ($this->categoryIsOther($event))
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Describe Category <span class="text-danger">*</span>
                    </label>
                    <input type="text" wire:model="events.{{ $index }}.category_other" class="form-control"
                           placeholder="e.g., Photobiomodulation / Laser Therapy">
                    @error("events.$index.category_other") <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
            @endif

            @if ($this->specificTypeIsOther($event))
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Describe Specific Type <span class="text-danger">*</span>
                    </label>
                    <input type="text" wire:model="events.{{ $index }}.specific_type_other" class="form-control"
                           placeholder="e.g., Low-level laser therapy">
                    @error("events.$index.specific_type_other") <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
            @endif

            <div class="mb-3">
                <label class="form-label fw-semibold">
                    Intervention Name <span class="text-danger">*</span>
                </label>
                <input type="text" wire:model="events.{{ $index }}.name" class="form-control"
                       placeholder="e.g., Whole-body gamma irradiation, single fraction">
                @error("events.$index.name") <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                <div class="form-text">A descriptive label for this specific intervention</div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">
                    Description of Method <span class="text-danger">*</span>
                </label>
                <textarea wire:model="events.{{ $index }}.method_description" rows="3" class="form-control"
                          placeholder="Describe how this intervention was administered or performed..."></textarea>
                @error("events.$index.method_description") <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                <div class="form-text">
                    Include whatever quantification is meaningful for this intervention (dose, volume, percentage, duration, etc.)
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Source Details</label>
                <input type="text" wire:model="events.{{ $index }}.source_details" class="form-control"
                       placeholder="e.g., Facility, donor colony, or supplier details...">
                @error("events.$index.source_details") <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        Frequency <span class="text-danger">*</span>
                    </label>
                    <input type="number" step="0.01" wire:model="events.{{ $index }}.frequency" class="form-control">
                    @error("events.$index.frequency") <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        Frequency Unit <span class="text-danger">*</span>
                    </label>
                    <select wire:model.live="events.{{ $index }}.frequency_unit" class="form-select">
                        <option value="single_dose">Single dose</option>
                        <option value="per_day">Per day</option>
                        <option value="per_week">Per week</option>
                        <option value="continuous">Continuous</option>
                        <option value="other">Other</option>
                    </select>
                    @error("events.$index.frequency_unit") <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        Start Day <span class="text-danger">*</span>
                    </label>
                    <input type="number" wire:model="events.{{ $index }}.start_day" class="form-control">
                    @error("events.$index.start_day") <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    <div class="form-text">Day relative to start</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">End Day</label>
                    <input type="number" wire:model="events.{{ $index }}.end_day" class="form-control"
                           placeholder="Leave blank if there is no defined end day">
                    @error("events.$index.end_day") <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
            </div>

            @if ($event['frequency_unit'] === 'other')
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Describe Frequency Unit <span class="text-danger">*</span>
                    </label>
                    <input type="text" wire:model="events.{{ $index }}.frequency_unit_other" class="form-control"
                           placeholder="e.g., every 3 days">
                    @error("events.$index.frequency_unit_other") <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
            @endif

            </div>
        @endforeach

        <div class="text-center py-4 bg-light" style="border: 2px dashed #6c757d; border-radius: 6px; cursor: pointer;" wire:click="addEvent">
            <i class="bi bi-plus-circle text-primary" style="font-size: 1.5rem;"></i>
            <div class="mt-2"><strong>Add Another Intervention Event</strong></div>
            <small class="text-muted">For sequential or combination interventions</small>
        </div>

        </div>
    </div>

    <div class="d-flex align-items-center justify-content-between mt-4">
        <div class="ms-auto d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" wire:click="cancel">
                <i class="bi bi-x-circle"></i> Cancel
            </button>
            <button type="button" class="btn btn-primary" wire:click="save">
                <i class="bi bi-save"></i>
                @if ($plan)
                    Update Plan
                @else
                    Create Plan
                @endif
            </button>
        </div>
    </div>
</div>
