<?php

namespace App\Http\Controllers;

use App\Models\AnimalModel;
use App\Models\GonadectomyProcedure;
use App\Models\SampleGroup;
use App\Models\Study;
use Illuminate\Http\Request;

class SampleGroupController extends Controller
{
    public function show(Request $request, Study $study)
    {
        $study->load(
            'sampleGroups.animalModel.geneticMethod',
            'sampleGroups.animalModel.geneticCategory',
            'sampleGroups.animalModel.animalLine',
            'sampleGroups.animalModel.parents.animalLine',
            'sampleGroups.drugAdministrationPlan',
            'sampleGroups.gonadectomyProcedure',
            'animalModels.geneticCategory',
            'animalModels.geneticMethod'
        );
        $study->load('sampleGroups.animalModel.geneticMethod', 'sampleGroups.drugAdministrationPlan', 'sampleGroups.gonadectomyProcedure', 'sampleGroups.otherInterventionPlan');
        $study->load('animalModels.geneticMethod');

        $animalModelId = $request->query('animal_model');
        $selectedAnimalModel = $study->animalModels->where('id', $animalModelId)->first();

        $animalModel2Id = $request->query('animal_model_2');
        $selectedAnimalModel2 = $study->animalModels->where('id', $animalModel2Id)->first();

        return view('study.sample-groups.index', compact('study', 'selectedAnimalModel', 'selectedAnimalModel2'));
    }

    public function submit(Request $request, Study $study)
    {
        // Validate the incoming request based on the actual form field names
        $validated = $request->validate([
            'sample_group_id' => 'nullable|exists:sample_groups,id',
            'label' => 'required|string|max:255',
            'animal_model' => 'required|exists:animal_models,id',
            'age' => 'required|numeric|min:0|max:9999',
            'sex' => 'required|in:M,F',
            'control' => 'nullable|boolean',
            'drug_administration_plan_id' => 'nullable|exists:drug_administration_plans,id',
            'gonadectomy_procedure' => 'nullable|integer',
            'other_intervention_plan_id' => 'nullable|exists:other_intervention_plans,id',
            'genotype' => 'nullable|string|in:+/+,+/-,-/-',
            'cre_status' => 'nullable|string|in:positive,negative',
        ]);

        // Validate OVX/ORX based on the sex
        $gonadectomyProcedure = GonadectomyProcedure::find($validated['gonadectomy_procedure'] ?? null);
        if ($gonadectomyProcedure) {
            if ($validated['sex'] === 'M' && $gonadectomyProcedure->type === 'ovx') {
                return redirect()->back()->withErrors(['Selected gonadectomy procedure does not match the sex of the animal.'])->withInput();
            }

            if ($validated['sex'] === 'F' && $gonadectomyProcedure->type === 'orx') {
                return redirect()->back()->withErrors(['Selected gonadectomy procedure does not match the sex of the animal.'])->withInput();
            }
        }

        // Determine if we're updating an existing sample group or creating a new one
        $isUpdating = ! empty($validated['sample_group_id']);

        if ($isUpdating) {
            // Find and update the existing sample group
            $sampleGroup = SampleGroup::findOrFail($validated['sample_group_id']);

            // Ensure the sample group belongs to the current study
            if ($sampleGroup->study_id !== $study->id) {
                return redirect()->back()->withErrors(['Sample group does not belong to this study.'])->withInput();
            }
        } else {
            // Create a new sample group
            $sampleGroup = new SampleGroup;
            $sampleGroup->study_id = $study->id;
        }

        // Set the sample group attributes
        $sampleGroup->animal_model_id = $validated['animal_model'];
        $sampleGroup->control = $validated['control'] ?? false;
        $sampleGroup->genotype = $validated['genotype'] ?? null;
        $sampleGroup->drug_administration_plan_id = $validated['drug_administration_plan_id'] ?? null;
        $sampleGroup->gonadectomy_procedure_id = $validated['gonadectomy_procedure'] ?? null;
        $sampleGroup->other_intervention_plan_id = $validated['other_intervention_plan_id'] ?? null;
        $sampleGroup->age = $validated['age'];
        $sampleGroup->sex = $validated['sex'];
        $sampleGroup->label = $validated['label'];
        $sampleGroup->cre_status = $validated['cre_status'] ?? null;

        $selectedModel = $sampleGroup->animal_model_id
            ? AnimalModel::with('geneticCategory')->find($sampleGroup->animal_model_id)
            : null;
        if (! empty($sampleGroup->genotype) && $selectedModel) {
            $allowed = $selectedModel->sampleGroupGenotypeChoices();
            if (count($allowed) === 0) {
                $allowed = ['+/+', '+/-', '-/-'];
            }
            if ($isUpdating && $sampleGroup->getOriginal('genotype')) {
                $allowed[] = $sampleGroup->getOriginal('genotype');
            }
            if (! in_array($sampleGroup->genotype, $allowed, true)) {
                return redirect()->back()->withErrors(['genotype' => 'Select a genotype option for this animal model.'])->withInput();
            }
        }

        // Check for duplicates only when creating a new sample group
        if (! $isUpdating) {
            $existingGroup = $study->sampleGroups()
                ->where('animal_model_id', $sampleGroup->animal_model_id)
                ->where('control', $sampleGroup->control)
                ->where('genotype', $sampleGroup->genotype)
                ->where('drug_administration_plan_id', $sampleGroup->drug_administration_plan_id)
                ->where('gonadectomy_procedure_id', $sampleGroup->gonadectomy_procedure_id)
                ->where('other_intervention_plan_id', $sampleGroup->other_intervention_plan_id)
                ->where('age', $sampleGroup->age)
                ->where('sex', $sampleGroup->sex)
                ->where('cre_status', $sampleGroup->cre_status)
                ->first();

            if ($existingGroup) {
                return redirect()->back()->withErrors(['This sample group already exists.'])->withInput();
            }
        } else {
            // When updating, check for duplicates excluding the current sample group
            $existingGroup = $study->sampleGroups()
                ->where('id', '!=', $sampleGroup->id)
                ->where('animal_model_id', $sampleGroup->animal_model_id)
                ->where('control', $sampleGroup->control)
                ->where('genotype', $sampleGroup->genotype)
                ->where('drug_administration_plan_id', $sampleGroup->drug_administration_plan_id)
                ->where('gonadectomy_procedure_id', $sampleGroup->gonadectomy_procedure_id)
                ->where('other_intervention_plan_id', $sampleGroup->other_intervention_plan_id)
                ->where('age', $sampleGroup->age)
                ->where('sex', $sampleGroup->sex)
                ->where('cre_status', $sampleGroup->cre_status)
                ->first();

            if ($existingGroup) {
                return redirect()->back()->withErrors(['A sample group with these characteristics already exists.'])->withInput();
            }
        }

        $sampleGroup->save();

        $message = $isUpdating ? 'Sample group updated successfully.' : 'Sample group created successfully.';

        return redirect()->route('study.sample-groups', $study)->with('success', $message);
    }

    public function edit(Request $request, Study $study, SampleGroup $sampleGroup)
    {
        $study->load(
            'sampleGroups.animalModel.geneticMethod',
            'sampleGroups.animalModel.geneticCategory',
            'sampleGroups.animalModel.animalLine',
            'sampleGroups.animalModel.parents.animalLine',
            'sampleGroups.drugAdministrationPlan',
            'sampleGroups.gonadectomyProcedure',
            'animalModels.geneticCategory',
            'animalModels.geneticMethod'
        );
        $study->load('sampleGroups.animalModel.geneticMethod', 'sampleGroups.drugAdministrationPlan', 'sampleGroups.gonadectomyProcedure', 'sampleGroups.otherInterventionPlan');

        $animalModelId = $request->query('animal_model') ?: $sampleGroup->animal_model_id;
        $selectedAnimalModel = $study->animalModels->where('id', $animalModelId)->first();

        $animalModel2Id = $request->query('animal_model_2') ?: $sampleGroup->animal_model_2_id;
        $selectedAnimalModel2 = $study->animalModels->where('id', $animalModel2Id)->first();

        return view('study.sample-groups.edit', compact('study', 'sampleGroup', 'selectedAnimalModel', 'selectedAnimalModel2'));
    }

    public function destroy(Request $request, Study $study)
    {
        $request->validate([
            'sample_group_id' => 'required|exists:sample_groups,id',
        ]);

        $sampleGroup = SampleGroup::find($request->sample_group_id);
        if ($sampleGroup) {
            $sampleGroup->delete();

            return redirect()->route('study.sample-groups', $study)->with('success', 'Sample group deleted successfully.');
        }

        return redirect()->route('study.sample-groups', $study)->withErrors(['Sample group not found.']);
    }
}
