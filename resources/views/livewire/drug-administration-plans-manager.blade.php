<div>
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($plans->isEmpty())
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> No drug administration plans defined yet. Click the button below to create your first plan.
        </div>
    @else
        <div class="mb-4" @if($study->isLockedForEditing(auth()->user())) data-input-locked @endif>
            @foreach ($plans as $plan)
                <div class="card mb-3" style="border-left: 4px solid var(--bs-primary);">
                    <div class="card-body">
                        <div class="row align-items-start">
                            <div class="col-md-9">
                                <h5 class="mb-2">
                                    {{ $plan->full_display_name }}
                                </h5>
                                <p class="text-muted mb-2">
                                    <small>
                                        {{ $plan->events->count() }} {{ Str::plural('event', $plan->events->count()) }} • 
                                        Animals start at {{ $plan->animal_age_at_day_zero }} weeks old
                                        @if ($plan->end_age)
                                            • End at ~{{ $plan->end_age }} weeks old
                                        @endif
                                    </small>
                                </p>

                                @if ($plan->description)
                                    <p class="mb-2"><small>{{ $plan->description }}</small></p>
                                @endif

                                {{-- Timeline Preview --}}
                                <div class="bg-light p-3 rounded mt-2">
                                    <strong class="d-block mb-2">Timeline:</strong>
                                    @foreach ($plan->events as $event)
                                        <div class="mb-1 ps-2" style="border-left: 3px solid var(--bs-primary);">
                                            {{ $event->getTimelineString($plan->animal_age_at_day_zero) }}
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Treatment Type Detection --}}
                                <div class="mt-2">
                                    @if ($plan->is_vehicle_control)
                                        <span class="badge text-bg-primarydark">Vehicle control</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $plan->treatment_type }}</span>
                                    @endif
                                    @if ($plan->sampleGroups->count() > 0)
                                        <span class="badge text-bg-primarydark">
                                            Used by {{ $plan->sampleGroups->count() }} sample {{ Str::plural('group', $plan->sampleGroups->count()) }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-3 text-end">
                                <button 
                                    type="button" 
                                    class="btn btn-sm btn-outline-primary"
                                    wire:click="editPlan({{ $plan->id }})"
                                    data-input-unlock>
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <button 
                                    type="button" 
                                    class="btn btn-sm btn-outline-danger"
                                    x-on:click="if (confirm('Are you sure you want to delete this drug administration plan? This action cannot be undone.')) { $wire.deletePlan({{ $plan->id }}) }">
                                    <i class="bi bi-trash"></i> Delete
                                </button>

                                @if (!$plan->is_vehicle_control)
                                    @if ($plan->vehicleControl)
                                        <div class="mt-2">
                                            <span class="badge bg-light text-secondary border">
                                                <i class="bi bi-link-45deg"></i> Vehicle control: Plan {{ $plan->vehicleControl->plan_number }}
                                            </span>
                                        </div>
                                    @else
                                        <button 
                                            type="button" 
                                            class="btn btn-sm btn-outline-success mt-2"
                                            wire:click="createVehicleControl({{ $plan->id }})"
                                            data-input-unlock>
                                            <i class="bi bi-prescription2"></i> Create vehicle control
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <button 
        type="button" 
        class="btn btn-primary btn-lg"
        wire:click="createPlan">
        <i class="bi bi-plus-circle"></i> Create New Drug Administration Plan
    </button>
</div>