<?php

namespace App\Livewire;

use App\Models\OtherInterventionPlan;
use App\Models\Study;
use Livewire\Attributes\On;
use Livewire\Component;

class OtherInterventionPlansManager extends Component
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
        $this->plans = $this->study->otherInterventionPlans()->with('events')->get();
    }

    public function createPlan()
    {
        return redirect()->route('study.animal-experimentation.other-intervention.plan.create', $this->study);
    }

    public function editPlan($planId)
    {
        return redirect()->route('study.animal-experimentation.other-intervention.plan.edit', [$this->study, $planId]);
    }

    public function deletePlan($planId)
    {
        $plan = OtherInterventionPlan::find($planId);

        if (! $plan || $plan->study_id !== $this->study->id) {
            session()->flash('error', 'Plan not found or access denied.');

            return;
        }

        // Same guard as DrugAdministrationPlansManager: a plan in active use
        // by a sample group can't be deleted out from under it.
        if ($plan->sampleGroups()->count() > 0) {
            session()->flash('error', 'Cannot delete this plan because it is being used by one or more sample groups.');

            return;
        }

        $plan->delete();
        $this->loadPlans();
        session()->flash('success', 'Intervention plan deleted successfully.');
    }

    // NOTE: DrugAdministrationPlansManager also has createVehicleControl() here,
    // which auto-generates a control plan by collapsing events that share a
    // vehicle/route. There's no equivalent for Other Interventions: no field
    // here plays the role vehicle does (a shared axis every event has, that
    // can be zeroed out into a control). Method Description is free text with
    // no universal structure to collapse. Control plans for Other
    // Interventions are created manually, using the "This is a Control Plan"
    // / "Control for Plan" fields already on the create/edit form.

    public function duplicatePlan($planId)
    {
        $source = OtherInterventionPlan::with('events')->find($planId);

        if (! $source || $source->study_id !== $this->study->id) {
            session()->flash('error', 'Plan not found or access denied.');

            return;
        }

        $copy = OtherInterventionPlan::create([
            'study_id' => $this->study->id,
            'plan_name' => $source->plan_name.' (Copy)',
            'description' => $source->description,
            'animal_age_at_day_zero' => $source->animal_age_at_day_zero,
        ]);

        foreach ($source->events as $event) {
            $copy->events()->create([
                'intervention_category_id' => $event->intervention_category_id,
                'intervention_specific_type_id' => $event->intervention_specific_type_id,
                'category_other' => $event->category_other,
                'specific_type_other' => $event->specific_type_other,
                'name' => $event->name,
                'method_description' => $event->method_description,
                'source_details' => $event->source_details,
                'frequency' => $event->frequency,
                'frequency_unit' => $event->frequency_unit,
                'frequency_unit_other' => $event->frequency_unit_other,
                'start_day' => $event->start_day,
                'end_day' => $event->end_day,
                'event_order' => $event->event_order,
            ]);
        }

        // Straight into editing the copy, since the whole point is "autofill
        // then let the user tweak a couple fields" — not back to the list.
        return redirect()->route('study.animal-experimentation.other-intervention.plan.edit', [$this->study, $copy->id]);
    }

    public function render()
    {
        return view('livewire.other-intervention-plans-manager');
    }

    #[On('refresh-plans')]
    public function refreshPlans()
    {
        $this->loadPlans();
    }
}
