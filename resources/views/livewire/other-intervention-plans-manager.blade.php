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
            <i class="bi bi-info-circle"></i> No intervention plans defined yet. Click the button below to create your first plan.
        </div>
    @else
        @if ($plans->count() === 1)
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> You have one plan so far. Most comparisons need a matched control plan too, consider adding one.
            </div>
        @endif

        <div class="mb-4" @if($study->isLockedForEditing(auth()->user())) data-input-locked @endif>
            @foreach ($plans as $i => $plan)
                <div class="card mb-3" style="border-left: 4px solid var(--bs-primary);">
                    <div class="card-body">
                        <div class="row align-items-start">
                            <div class="col-md-9">
                                <h5 class="mb-2">
                                    Intervention Plan {{ $i + 1 }}: {{ $plan->plan_name }}
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

                                {{-- Category summary --}}
                                <div class="mt-2">
                                    @foreach ($plan->events->pluck('category_label')->unique()->filter() as $categoryLabel)
                                        <span class="badge bg-secondary">{{ $categoryLabel }}</span>
                                    @endforeach
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
                                    class="btn btn-sm btn-outline-secondary"
                                    wire:click="duplicatePlan({{ $plan->id }})"
                                    data-input-unlock>
                                    <i class="bi bi-copy"></i> Duplicate
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    wire:click="deletePlan({{ $plan->id }})"
                                    wire:confirm="Delete this intervention plan? This cannot be undone.">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
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
        <i class="bi bi-plus-circle"></i> Create New Intervention Plan
    </button>
</div>
