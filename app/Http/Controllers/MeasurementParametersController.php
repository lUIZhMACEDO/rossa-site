<?php

namespace App\Http\Controllers;

use App\Models\AnalysisType;
use App\Models\Measurement;
use Illuminate\Http\Request;

class MeasurementParametersController extends Controller
{
    public function showMeasurementParameters()
    {

        $measurements = Measurement::official()
            ->with(['analysisType', 'regions', 'specimenType'])
            ->get();

        $staticMeasurements = Measurement::whereNull('analysis_type_id')
            ->with(['analysisType', 'regions', 'specimenType'])
            ->where('name', 'weight')
            ->get();

        return view('measurement-parameters', compact('measurements', 'staticMeasurements'));
    }

    public function adminIndex()
    {
        $analysisTypes = AnalysisType::all();

        $measurements = Measurement::with(['analysisType', 'specimenType'])
            ->when(request('analysis_type_id'), function ($query) {
                $at = request('analysis_type_id');
                if ($at == 'null') {
                    $query->whereNull('analysis_type_id');
                } else {
                    $query->where('analysis_type_id', request('analysis_type_id'));
                }
            })
            ->when(request('measurement_name'), function ($query) {
                $name = request('measurement_name');
                $query->where(function ($q) use ($name) {
                    $q->where('name', 'ilike', "%{$name}%")
                        ->orWhere('display_name', 'ilike', "%{$name}%")
                        ->orWhereJsonContains('synonyms', $name);
                });
            })
            ->when(request()->has('exclude_study_measurements'), function ($query) {
                $query->official();
            })
            ->orderBy('display_name')
            ->get();

        return view('admin.measurements.index', compact('analysisTypes', 'measurements'));
    }

    public function create()
    {
        $analysisTypes = AnalysisType::withoutOther()->get();

        return view('admin.measurements.create', compact('analysisTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:measurements,name',
            'display_name' => 'required|string|max:255',
            'unit' => 'nullable|string|max:255',
            'analysis_type_id' => 'nullable|exists:analysis_types,id',
            'synonyms' => 'nullable|string',
            'graph_exclude' => 'nullable|boolean',
        ]);

        // Process synonyms from textarea (one per line) to array
        $synonyms = [];
        if (! empty($validated['synonyms'])) {
            $synonyms = array_filter(
                array_map('trim', explode("\n", $validated['synonyms'])),
                fn ($synonym) => ! empty($synonym)
            );
        }

        $measurement = Measurement::create([
            'name' => $validated['name'],
            'display_name' => $validated['display_name'],
            'unit' => $validated['unit'] ?? null,
            'analysis_type_id' => $validated['analysis_type_id'] ?? null,
            'synonyms' => $synonyms,
            'graph_exclude' => $validated['graph_exclude'] ?? false,
            'study_id' => null, // Official measurement
        ]);

        return redirect()
            ->route('admin.measurements.edit', $measurement)
            ->with('success', 'Measurement created successfully!');
    }

    public function edit(Measurement $measurement)
    {
        $analysisTypes = AnalysisType::all();
        $measurementValuesCount = $measurement->measurementValues()->count();

        return view('admin.measurements.edit', compact('measurement', 'analysisTypes', 'measurementValuesCount'));
    }

    public function update(Request $request, Measurement $measurement)
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
            'unit' => 'nullable|string|max:255',
            'synonyms' => 'nullable|string',
            'graph_exclude' => 'nullable|boolean',
        ]);

        // Process synonyms from textarea (one per line) to array
        $synonyms = [];
        if (! empty($validated['synonyms'])) {
            $synonyms = array_filter(
                array_map('trim', explode("\n", $validated['synonyms'])),
                fn ($synonym) => ! empty($synonym)
            );
        }

        $measurement->update([
            'display_name' => $validated['display_name'],
            'unit' => $validated['unit'] ?? null,
            'synonyms' => $synonyms,
            'graph_exclude' => $validated['graph_exclude'] ?? false,
        ]);

        return redirect()
            ->route('admin.measurements.edit', $measurement)
            ->with('success', 'Measurement updated successfully!');
    }

    public function destroy(Measurement $measurement)
    {
        // Only allow deleting official measurements
        if ($measurement->study_id !== null) {
            return redirect()
                ->route('admin.measurements')
                ->with('error', 'Cannot delete study-specific measurements.');
        }

        // Check if measurement has any values
        if ($measurement->measurementValues()->count() > 0) {
            return redirect()
                ->route('admin.measurements.edit', $measurement)
                ->with('error', 'Cannot delete measurement that has measurement values.');
        }

        $measurement->delete();

        return redirect()
            ->route('admin.measurements')
            ->with('success', 'Measurement deleted successfully!');
    }
}
