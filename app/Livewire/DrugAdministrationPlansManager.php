<?php

namespace App\Livewire;

use App\Models\DrugAdministrationEvent;
use App\Models\DrugAdministrationPlan;
use App\Models\Study;
use Livewire\Attributes\On;
use Livewire\Component;

class DrugAdministrationPlansManager extends Component
{
    public Study $study;

    public $plans;

    public function mount(Study $study)
    {
        $this->study = $study;
        $this->loadPlans();
    }

    public function loadPlans()
    {
        $this->plans = $this->study->drugAdministrationPlans()->with('events')->get();
    }

    public function createPlan()
    {
        return redirect()->route('study.animal-experimentation.drug-treatment.plan.create', $this->study);
    }

    public function editPlan($planId)
    {
        return redirect()->route('study.animal-experimentation.drug-treatment.plan.edit', [$this->study, $planId]);
    }

    public function deletePlan($planId)
    {
        $plan = DrugAdministrationPlan::find($planId);

        if (! $plan || $plan->study_id !== $this->study->id) {
            session()->flash('error', 'Plan not found or access denied.');

            return;
        }

        // Check if any sample groups are using this plan
        if ($plan->sampleGroups()->count() > 0) {
            session()->flash('error', 'Cannot delete this plan because it is being used by one or more sample groups.');

            return;
        }

        $plan->delete();
        $this->loadPlans();
        session()->flash('success', 'Drug administration plan deleted successfully.');
    }

    public function createVehicleControl($planId)
    {
        $plan = DrugAdministrationPlan::with('events')->find($planId);

        if (! $plan || $plan->study_id !== $this->study->id) {
            session()->flash('error', 'Plan not found or access denied.');

            return;
        }

        if ($plan->is_vehicle_control) {
            session()->flash('error', 'A vehicle control plan cannot itself have a control.');

            return;
        }

        if ($plan->vehicleControl()->exists()) {
            session()->flash('error', 'A generated vehicle control already exists for this plan. You can still add other controls (e.g. isotype, sham) as separate plans.');

            return;
        }

        // Only events that actually have a vehicle recorded can seed a control.
        $vehicleEvents = $plan->events->filter(function ($event) {
            return $event->vehicle_id !== null || ! empty($event->vehicle_other);
        });

        if ($vehicleEvents->isEmpty()) {
            session()->flash('error', 'No vehicle information recorded on this plan. Edit the plan to add a vehicle before generating a control.');

            return;
        }

        // Collapse events that share the same vehicle AND route into one —
        // co-administered drugs in the same vehicle get a single control injection.
        $collapsed = $vehicleEvents->unique(function ($event) {
            $vehicleKey = $event->vehicle_id ?? 'other:'.$event->vehicle_other;

            return $vehicleKey.'|'.$event->route.'|'.$event->start_day.'|'.$event->end_day;
        });

        $control = DrugAdministrationPlan::create([
            'study_id' => $this->study->id,
            'plan_name' => $plan->plan_name.' — Vehicle Control',
            'description' => 'Auto-generated vehicle control for "'.$plan->plan_name.'".',
            'animal_age_at_day_zero' => $plan->animal_age_at_day_zero,
            'is_vehicle_control' => true,
            'control_for_plan_id' => $plan->id,
        ]);

        $order = 0;
        foreach ($collapsed as $event) {
            DrugAdministrationEvent::create([
                'plan_id' => $control->id,
                'molecule_id' => null,
                'drug_source_type' => $event->drug_source_type,
                'drug_source_details' => $event->drug_source_details,
                'dose' => 0,
                'dose_unit' => $event->dose_unit,
                'route' => $event->route,
                'route_other' => $event->route_other,
                'start_day' => $event->start_day,
                'end_day' => $event->end_day,
                'frequency' => $event->frequency,
                'frequency_unit' => $event->frequency_unit,
                'frequency_unit_other' => $event->frequency_unit_other,
                'vehicle_id' => $event->vehicle_id,
                'vehicle_other' => $event->vehicle_other,
                'administration_volume' => $event->administration_volume,
                'administration_volume_unit' => $event->administration_volume_unit,
                'is_vehicle' => true,
                'event_order' => $order++,
            ]);
        }

        $this->loadPlans();
        session()->flash('success', 'Vehicle control plan generated from "'.$plan->plan_name.'".');
    }

    public function render()
    {
        return view('livewire.drug-administration-plans-manager');
    }

    #[On('refresh-plans')]
    public function refreshPlans()
    {
        $this->loadPlans();
    }
}
