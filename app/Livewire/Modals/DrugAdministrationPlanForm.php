<?php

namespace App\Livewire\Modals;

use App\Exceptions\StudyLockedException;
use App\Models\DrugAdministrationEvent;
use App\Models\DrugAdministrationPlan;
use App\Models\Study;
use Livewire\Component;

class DrugAdministrationPlanForm extends Component
{
    public Study $study;

    public $studyId;

    public $planId;

    public $plan;

    // Plan fields
    public $plan_name = '';

    public $description = '';

    public $animal_age_at_day_zero = '';

    // Events array
    public $events = [];

    public function mount($studyId, $planId = null)
    {
        $this->studyId = $studyId;
        $this->planId = $planId;
        $this->study = Study::findOrFail($studyId);

        if ($planId) {
            $this->plan = DrugAdministrationPlan::with('events')
                ->where('study_id', $this->studyId)
                ->findOrFail($planId);
            $this->plan_name = $this->plan->plan_name;
            $this->description = $this->plan->description;
            $this->animal_age_at_day_zero = $this->plan->animal_age_at_day_zero;

            // Load existing events
            foreach ($this->plan->events as $event) {
                $this->events[] = [
                    'id' => $event->id,
                    'molecule_id' => $event->molecule_id,
                    'drug_source_type' => $event->drug_source_type,
                    'drug_source_details' => $event->drug_source_details,
                    'dose' => $event->dose,
                    'dose_unit' => $event->dose_unit,
                    'route' => $event->route,
                    'route_other' => $event->route_other,
                    'start_day' => $event->start_day,
                    'end_day' => $event->end_day,
                    'frequency' => $event->frequency,
                    'frequency_unit' => $event->frequency_unit,
                    'frequency_unit_other' => $event->frequency_unit_other,
                    'vehicle_id' => $event->vehicle_id ? (string) $event->vehicle_id : ($event->vehicle_other ? 'other' : null),
                    'vehicle_other' => $event->vehicle_other,
                    'administration_volume' => $event->administration_volume,
                    'administration_volume_unit' => $event->administration_volume_unit ?? 'ml/kg',
                    'is_vehicle' => (bool) $event->is_vehicle,
                ];
            }
        } else {
            // Initialize with one empty event
            $this->addEvent();
        }
    }

    public function addEvent()
    {
        $this->ensureStudyUnlocked(); // this addEvent method doesn't actually write to db, but should still prevent this behavior

        $this->events[] = [
            'id' => null,
            'molecule_id' => null,
            'drug_source_type' => 'commercial',
            'drug_source_details' => '',
            'dose' => '',
            'dose_unit' => 'mg/kg',
            'route' => 'intraperitoneal_injection',
            'route_other' => '',
            'start_day' => 0,
            'end_day' => null,
            'vehicle_id' => null,
            'vehicle_other' => '',
            'administration_volume' => '',
            'administration_volume_unit' => 'ml/kg',
            'is_vehicle' => false,
            'frequency' => 1,
            'frequency_unit' => 'per day',
            'frequency_unit_other' => '',
        ];
    }

    public function removeEvent($index)
    {
        $this->ensureStudyUnlocked(); // this removeEvent method doesn't actually write to db, but should still prevent this behavior

        unset($this->events[$index]);
        $this->events = array_values($this->events); // Re-index array
    }

    public function save()
    {
        $this->ensureStudyUnlocked();

        $this->validate([
            'plan_name' => 'required|string|max:255',
            'animal_age_at_day_zero' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'events' => 'required|array|min:1',
            'events.*.molecule_id' => 'required|exists:molecules,id',
            'events.*.dose' => 'required|numeric|min:0',
            'events.*.dose_unit' => 'required|string',
            'events.*.route' => 'required|string',
            'events.*.start_day' => 'required|integer|min:0',
            'events.*.end_day' => 'nullable|integer|min:0',
            'events.*.frequency' => 'required|numeric|min:0',
            'events.*.frequency_unit' => 'required|string',
            'events.*.frequency_unit_other' => 'nullable|string|max:255',
            'events.*.vehicle_id' => 'required',
            'events.*.vehicle_other' => 'required_if:events.*.vehicle_id,other|nullable|string|max:255',
            'events.*.administration_volume' => 'nullable|numeric|min:0',
            'events.*.administration_volume_unit' => 'nullable|string',
        ]);

        foreach ($this->events as $index => $event) {
            if (($event['frequency_unit'] ?? null) === 'every X days') {
                $this->validate([
                    "events.{$index}.frequency_unit_other" => 'required|integer|min:1',
                ]);
            }

            if (($event['frequency_unit'] ?? null) === 'other') {
                $this->validate([
                    "events.{$index}.frequency_unit_other" => 'required|string|max:255',
                ]);
            }
        }

        if ($this->planId) {
            // Update existing plan
            $plan = DrugAdministrationPlan::where('study_id', $this->studyId)->findOrFail($this->planId);
            $plan->update([
                'plan_name' => $this->plan_name,
                'description' => $this->description,
                'animal_age_at_day_zero' => $this->animal_age_at_day_zero,
            ]);

            // Delete removed events
            $existingEventIds = collect($this->events)->pluck('id')->filter()->toArray();
            $plan->events()->whereNotIn('id', $existingEventIds)->delete();
        } else {
            // Create new plan
            $plan = DrugAdministrationPlan::create([
                'study_id' => $this->studyId,
                'plan_name' => $this->plan_name,
                'description' => $this->description,
                'animal_age_at_day_zero' => $this->animal_age_at_day_zero,
            ]);
        }

        // Save events
        foreach ($this->events as $index => $eventData) {
            $eventData['event_order'] = $index;
            $eventData['plan_id'] = $plan->id;

            if (($eventData['vehicle_id'] ?? null) === 'other') {
                $eventData['vehicle_id'] = null;
            } else {
                $eventData['vehicle_other'] = null;
            }

            if (! in_array($eventData['frequency_unit'] ?? null, ['every X days', 'other'], true)) {
                $eventData['frequency_unit_other'] = null;
            }

            if (isset($eventData['id']) && $eventData['id']) {
                // Update existing event
                $event = DrugAdministrationEvent::where('plan_id', $plan->id)->findOrFail($eventData['id']);
                $event->update($eventData);
            } else {
                // Create new event
                unset($eventData['id']);
                DrugAdministrationEvent::create($eventData);
            }
        }

        return redirect()->route('study.animal-experimentation.drug-treatment', $this->studyId)
            ->with('success', 'Drug administration plan saved successfully.');
    }

    public function cancel()
    {
        return redirect()->route('study.animal-experimentation.drug-treatment', $this->studyId);
    }

    public function render()
    {
        return view('livewire.modals.drug-administration-plan-form');
    }

    protected function validationAttributes(): array
    {
        $attributes = [
            'plan_name' => 'plan name',
            'animal_age_at_day_zero' => 'animal age at day zero',
            'description' => 'description',
            'events' => 'events',
        ];

        foreach ($this->events as $index => $event) {
            $label = 'Event '.($index + 1);
            $attributes["events.{$index}.molecule_id"] = "{$label} molecule";
            $attributes["events.{$index}.dose"] = "{$label} dose";
            $attributes["events.{$index}.dose_unit"] = "{$label} dose unit";
            $attributes["events.{$index}.route"] = "{$label} route";
            $attributes["events.{$index}.start_day"] = "{$label} start day";
            $attributes["events.{$index}.end_day"] = "{$label} end day";
            $attributes["events.{$index}.frequency"] = "{$label} frequency";
            $attributes["events.{$index}.frequency_unit"] = "{$label} frequency unit";
            $attributes["events.{$index}.frequency_unit_other"] = ($event['frequency_unit'] ?? null) === 'every X days'
                ? "{$label} every how many days"
                : "{$label} frequency details";
            $attributes["events.{$index}.vehicle_id"] = "{$label} vehicle";
            $attributes["events.{$index}.vehicle_other"] = "{$label} vehicle (other)";
            $attributes["events.{$index}.administration_volume"] = "{$label} administration volume";
            $attributes["events.{$index}.administration_volume_unit"] = "{$label} administration volume unit";
        }

        return $attributes;
    }

    private function ensureStudyUnlocked(): void
    {
        if ($this->study->isLockedForEditing(auth()->user())) {
            throw new StudyLockedException;
        }
    }
}
