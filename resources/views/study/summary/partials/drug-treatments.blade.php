<div>
    @foreach($study->drugAdministrationPlans as $plan)
        <div class="mb-4 p-3 border rounded">
            <div class="d-flex align-items-center gap-2 mb-2">
                <h5 class="mb-0">{{ $plan->full_display_name }}</h5>
                @if ($plan->is_vehicle_control)
                    <span class="badge bg-primarydark">Vehicle control</span>
                @else
                    <span class="badge bg-secondary">{{ $plan->treatment_type }}</span>
                @endif
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <p class="mb-0"><strong>Starting Age (weeks):</strong> {{ $plan->animal_age_at_day_zero }}</p>
                    @if ($plan->end_age)
                        <p class="mb-0"><strong>End Age (weeks):</strong> ~{{ $plan->end_age }}</p>
                    @endif
                    <p class="mb-0"><strong>Description:</strong> {{ $plan->description ?: 'N/A' }}</p>
                    @if ($plan->is_vehicle_control && $plan->controlForPlan)
                        <p class="mb-0"><strong>Control for:</strong> {{ $plan->controlForPlan->full_display_name }}</p>
                    @elseif (!$plan->is_vehicle_control && $plan->vehicleControl)
                        <p class="mb-0"><strong>Vehicle control:</strong> {{ $plan->vehicleControl->full_display_name }}</p>
                    @endif
                </div>
            </div>

            {{-- Timeline (mirrors the Drug Administration Plans list) --}}
            <div class="bg-light p-3 rounded mt-3">
                <strong class="d-block mb-2">Timeline:</strong>
                @foreach($plan->events as $event)
                    <div class="mb-1 ps-2" style="border-left: 3px solid var(--bs-primary); font-family: 'Courier New', monospace; font-size: 13px;">
                        {{ $event->getTimelineString($plan->animal_age_at_day_zero) }}
                    </div>
                @endforeach
            </div>

            {{-- Per-event detail --}}
            @foreach($plan->events as $event)
            <div class="row mt-3">
                <div class="col-lg-6">
                    <div>
                        <h6>
                            @if ($event->is_vehicle)
                                {{ $event->vehicle?->name ?? $event->vehicle_other ?? 'Vehicle' }} <span class="text-muted">(vehicle)</span>
                            @else
                                {{ $event->drug_display_name }}
                            @endif
                        </h6>
                        @unless ($event->is_vehicle)
                            <p class="mb-0"><strong>Drug Source Type:</strong> {{ $event->drug_source_type }}</p>
                            <p class="mb-0"><strong>Drug Source Details:</strong> {{ $event->drug_source_details ?: 'N/A' }}</p>
                            <p class="mb-0"><strong>Dose:</strong> {{ $event->dose }} {{ $event->dose_unit }}</p>
                        @endunless
                        <p class="mb-0"><strong>Vehicle:</strong> {{ $event->vehicle?->name ?? $event->vehicle_other ?? 'N/A' }}</p>
                        @if ($event->administration_volume)
                            <p class="mb-0"><strong>Administration Volume:</strong> {{ rtrim(rtrim(number_format($event->administration_volume, 2, '.', ''), '0'), '.') }} {{ $event->administration_volume_unit }}</p>
                        @endif
                        <p class="mb-0"><strong>Route:</strong> {{ $event->route_display }}</p>
                        <p class="mb-0"><strong>Duration:</strong> {{ $event->getDuration() }} days</p>
                        <p class="mb-0"><strong>Frequency:</strong> {{ $event->frequency_display }}</p>
                    </div>
                </div>
                @unless ($event->is_vehicle)
                <div class="col-lg-6">
                    <div>
                        <h6>Additional Drug Information</h6>
                        <p class="mb-0"><strong>ChEMBL ID:</strong> {{ $event->molecule?->chembl_id ?? 'N/A' }}</p>
                        <p class="mb-0"><strong>Formula:</strong> {{ $event->molecule?->formula ?? 'N/A' }}</p>
                    </div>
                </div>
                @endunless
            </div>
            @endforeach
        </div>
    @endforeach
</div>