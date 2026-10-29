<?php

namespace App\Http\Controllers;

use App\Models\ComparisonGroup;
use App\Models\SampleGroup;
use App\Models\Study;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ComparisonGroupController extends Controller
{
    public function index(Study $study)
    {
        $study->load(['comparisonGroups', 'sampleGroups' => function ($query) {
            $query->with([
                'animalModel.animalLine',
                'animalModel.parents.animalLine',
                'animalModel.geneticCategory',
                'animalModel.geneticMethod',
            ])->orderBy('created_at', 'asc');
        }]);

        return view('study.comparison-groups.index', compact('study'));
    }

    public function create(Study $study)
    {
        $study->load(['sampleGroups' => function ($query) {
            $query->with([
                'animalModel.animalLine',
                'animalModel.parents.animalLine',
                'animalModel.geneticCategory',
                'animalModel.geneticMethod',
            ])->orderBy('created_at', 'asc');
        }]);

        return view('study.comparison-groups.create', compact('study'));
    }

    public function store(Request $request, Study $study)
    {
        $validated = $this->validateComparisonGroupData($request, $study);

        $comparisonGroup = ComparisonGroup::create([
            'study_id' => $study->id,
            'sample_group_ids' => $validated['sample_group_ids'],
        ]);

        return redirect()
            ->route('study.comparison-groups.index', $study)
            ->with('success', 'Comparison group created successfully.');
    }

    public function edit(Study $study, ComparisonGroup $comparisonGroup)
    {
        // ensure comparison group belongs to this study and user is authorized
        if ($comparisonGroup->study_id !== $study->id) {
            abort(403);
        }

        $study->load(['sampleGroups' => function ($query) {
            $query->with([
                'animalModel.animalLine',
                'animalModel.parents.animalLine',
                'animalModel.geneticCategory',
                'animalModel.geneticMethod',
            ])->orderBy('created_at', 'asc');
        }]);

        return view('study.comparison-groups.create', compact('study', 'comparisonGroup'));
    }

    public function update(Request $request, Study $study, ComparisonGroup $comparisonGroup)
    {
        // ensure comparison group belongs to this study and user is authorized
        if ($comparisonGroup->study_id !== $study->id) {
            abort(403);
        }

        $validated = $this->validateComparisonGroupData($request, $study, $comparisonGroup);

        $comparisonGroup->update([
            'sample_group_ids' => $validated['sample_group_ids'],
        ]);

        return redirect()
            ->route('study.comparison-groups.index', $study)
            ->with('success', 'Comparison group updated successfully.');
    }

    public function destroy(Study $study, ComparisonGroup $comparisonGroup)
    {
        // ensure comparison group belongs to this study and user is authorized
        if ($comparisonGroup->study_id !== $study->id) {
            abort(403);
        }

        $comparisonGroup->delete();

        return redirect()
            ->route('study.comparison-groups.index', $study)
            ->with('success', 'Comparison group deleted successfully.');
    }

    private function validateComparisonGroupData(Request $request, Study $study, ?ComparisonGroup $existingComparisonGroup = null): array
    {
        $validated = $request->validate([
            'sample_group_ids' => 'required|array|min:2',
            'sample_group_ids.*' => 'required|integer|exists:sample_groups,id',
        ]);

        // ensure all sample groups belong to this study
        $sampleGroups = SampleGroup::whereIn('id', $validated['sample_group_ids'])
            ->where('study_id', $study->id)
            ->get();

        if ($sampleGroups->count() !== count($validated['sample_group_ids'])) {
            throw ValidationException::withMessages([
                'sample_group_ids' => 'All sample groups must belong to this study.',
            ]);
        }

        // check for duplicate ids
        if (count($validated['sample_group_ids']) !== count(array_unique($validated['sample_group_ids']))) {
            throw ValidationException::withMessages([
                'sample_group_ids' => 'Cannot include the same sample group multiple times.',
            ]);
        }

        // check for duplicate comparison groups
        $sortedIds = collect($validated['sample_group_ids'])->sort()->values()->toArray();

        $existingGroups = ComparisonGroup::where('study_id', $study->id)
            ->get()
            ->filter(function ($group) use ($sortedIds, $existingComparisonGroup) {
                // if editing, skip the current comparison group
                if ($existingComparisonGroup && $group->id === $existingComparisonGroup->id) {
                    return false;
                }

                // compare sorted arrays
                $groupSortedIds = collect($group->sample_group_ids)->sort()->values()->toArray();

                return $sortedIds === $groupSortedIds;
            });

        if ($existingGroups->isNotEmpty()) {
            throw ValidationException::withMessages([
                'sample_group_ids' => 'A comparison group with this exact set of sample groups already exists for this study.',
            ]);
        }

        return $validated;
    }
}
