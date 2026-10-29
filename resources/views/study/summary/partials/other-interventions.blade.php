@php
    $otherInterventionPlans = $study->otherInterventionPlans()->with('events', 'sampleGroups')->get();
@endphp

@foreach ($otherInterventionPlans as $i => $plan)
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-3">
                Intervention Plan {{ $i + 1 }}: {{ $plan->plan_name }}
            </h5>

            <p class="mb-1"><strong>Starting Age (weeks):</strong> {{ $plan->animal_age_at_day_zero }}</p>
            <p class="mb-1"><strong>End Age (weeks):</strong> {{ $plan->end_age ? '~' . $plan->end_age : 'N/A' }}</p>
            <p class="mb-1"><strong>Description:</strong> {{ $plan->description ?: 'N/A' }}</p>
            @if ($plan->sampleGroups->count() > 0)
                <p class="mb-2"><strong>Used By:</strong> {{ $plan->sampleGroups->count() }} sample {{ Str::plural('group', $plan->sampleGroups->count()) }}</p>
            @endif

            <div class="bg-light p-3 rounded mt-3 mb-3">
                <strong class="d-block mb-2">Timeline:</strong>
                @foreach ($plan->events as $event)
                    <div class="mb-1 ps-2" style="border-left: 3px solid var(--bs-primary);">
                        {{ $event->getTimelineString($plan->animal_age_at_day_zero) }}
                    </div>
                @endforeach
            </div>

            @foreach ($plan->events as $event)
                <hr>
                <h6 class="mb-2">{{ $event->name }}</h6>
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Category:</strong> {{ $event->category_label }}</p>
                        <p class="mb-1"><strong>Specific Type:</strong> {{ $event->specific_type_label }}</p>
                        <p class="mb-1"><strong>Frequency:</strong> {{ $event->frequency }} {{ $event->frequency_unit_label }}</p>
                        <p class="mb-1"><strong>Start Day:</strong> {{ $event->start_day }}</p>
                        <p class="mb-1"><strong>End Day:</strong> {{ $event->end_day ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Source Details:</strong> {{ $event->source_details ?: 'N/A' }}</p>
                    </div>
                </div>
                <p class="mb-0"><strong>Description of Method:</strong> {{ $event->method_description }}</p>
            @endforeach
        </div>
    </div>
@endforeach
