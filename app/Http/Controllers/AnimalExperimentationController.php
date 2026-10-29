<?php

namespace App\Http\Controllers;

use App\Models\DrugAdministrationPlan;
use App\Models\OtherInterventionPlan;
use App\Models\Study;
use Illuminate\Http\Request;

class AnimalExperimentationController extends Controller
{
    /**
     * At the top of animal experimentation, let's ask the user what categories of animal experimentation their study falls under.
     */
    public function showSetup(Study $study)
    {
        return view('study.animal-experimentation.setup', compact('study'));
    }

    public function saveSetup(Request $request, Study $study)
    {
        $request->validate([
            'performs_genetic_modification' => 'boolean',
            'performs_drug_treatment' => 'boolean',
            'performs_gonadectomy' => 'boolean',
            'performs_other_intervention' => 'boolean',   // new
        ]);

        $study->update([
            'performs_genetic_modification' => $request->input('performs_genetic_modification', false),
            'performs_drug_treatment' => $request->input('performs_drug_treatment', false),
            'performs_gonadectomy' => $request->input('performs_gonadectomy', false),
            'performs_other_intervention' => $request->input('performs_other_intervention', false),  // new
        ]);

        $prefix = 'study.animal-experimentation.';

        return redirect()->route($prefix.'animal-models', ['study' => $study->id])
            ->with('success', 'Animal experimentation setup updated successfully.');
    }

    public function toggleGeneticModification(Request $request, Study $study)
    {
        $request->validate([
            'performs_genetic_modification' => 'boolean',
            'genetic_category_id' => 'nullable|exists:genetic_categories,id',
        ]);

        // handle the enabling of genetic modification
        $performsGeneticModification = $request->boolean('performs_genetic_modification');
        if ($performsGeneticModification) {
            $study->update([
                'performs_genetic_modification' => $performsGeneticModification,
            ]);
        }

        // user selected a category or continued without modification
        if ($request->has('genetic_category_id') || ! $performsGeneticModification) {
            return redirect()->route('study.animal-experimentation.animal-models.create', [
                'study' => $study->id,
                'genetic_category_id' => $request->input('genetic_category_id') ?? '',
            ]);
        }
        // no category selected
        else {
            return redirect()->route('study.animal-experimentation.animal-models.create', ['study' => $study->id]);
        }
    }

    // side bar
    public function showDrugTreatment(Study $study)
    {
        return view('study.animal-experimentation.drug-treatment', compact('study'));
    }

    public function createDrugAdministrationPlan(Study $study)
    {
        $planId = null;

        return view('study.animal-experimentation.drug-administration-plan', compact('study', 'planId'));
    }

    public function editDrugAdministrationPlan(Study $study, DrugAdministrationPlan $plan)
    {
        if ($plan->study_id !== $study->id) {
            abort(404);
        }

        $planId = $plan->id;

        return view('study.animal-experimentation.drug-administration-plan', compact('study', 'planId'));
    }

    public function showGonadectomyProcedure(Study $study)
    {
        return view('study.animal-experimentation.gonadectomy-procedure', compact('study'));
    }

    public function showOtherIntervention(Study $study)
    {
        return view('study.animal-experimentation.other-intervention', compact('study'));
    }

    public function createOtherInterventionPlan(Study $study)
    {
        $planId = null;

        return view('study.animal-experimentation.other-intervention-plan', compact('study', 'planId'));
    }

    public function editOtherInterventionPlan(Study $study, OtherInterventionPlan $plan)
    {
        if ($plan->study_id !== $study->id) {
            abort(404);
        }

        $planId = $plan->id;

        return view('study.animal-experimentation.other-intervention-plan', compact('study', 'planId'));
    }
}
