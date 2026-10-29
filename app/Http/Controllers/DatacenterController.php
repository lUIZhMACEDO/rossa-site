<?php

namespace App\Http\Controllers;

use App\Enums\AnalysisTypeEnum;
use App\Models\AnalysisType;
use App\Models\AnalysisUpload;
use App\Models\Gene;
use App\Models\Study;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DatacenterController extends Controller
{
    public function index()
    {
        return view('datacenter.index');
    }

    public function studies(Request $request)
    {
        $studies = Study::query()
            ->with(['user'])
            ->when($request->input('search'), function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%'.$search.'%')
                        ->orWhere('identifier', 'like', '%'.$search.'%')
                        ->orWhere('summary', 'like', '%'.$search.'%');
                });
            })
            ->when($request->input('genetic_modification'), function ($query, $geneticModification) {
                return $query->where('study_is_genetically_modified', $geneticModification);
            })
            ->when($request->input('drug_treatment'), function ($query, $drugTreatment) {
                return $query->where('study_performs_drug_treatment', $drugTreatment);
            })
            // ->when($request->input('status'), function ($query, $status) {
            //     return $query->where('status', $status);
            // })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('datacenter.studies', compact('studies'));
    }

    public function study(Study $study)
    {
        return view('datacenter.study', compact('study'));
    }

    public function gene(Gene $gene)
    {
        $gene->loadCount('studies');

        $relatedStudiesCount = $gene->studies()
            ->select('studies.id', 'studies.identifier', 'studies.title', 'studies.created_at')
            ->distinct()
            ->count();

        return view('datacenter.gene', compact('gene', 'relatedStudiesCount'));
    }

    public function exploreAnalysis(Request $request)
    {

        $analysisTypes = AnalysisType::with(['focuses'])->get();

        $availableViews = $analysisTypes->map(function ($analysisType) {
            return [
                'name' => $analysisType->name,
                'view' => $analysisType->slug.'_sample_view',
            ];
        });

        $requestedView = $request->input('view');
        $viewNames = $availableViews->pluck('view');
        if ($requestedView && $viewNames->contains($requestedView)) {
            $viewName = $requestedView;
        } else {
            $viewName = $availableViews->first(); // Default to the first analysis type view
        }

        $exists = DB::table('pg_catalog.pg_class as c')
            ->join('pg_catalog.pg_namespace as n', 'n.oid', '=', 'c.relnamespace')
            ->where('n.nspname', 'public')
            ->where('c.relname', $viewName)
            ->whereIn('c.relkind', ['v', 'm']) // 'v' = view, 'm' = materialized view
            ->exists();

        $results = collect();

        if ($exists) {

            $results = collect(DB::select('Select * FROM '.$viewName));

            if ($request->input('download')) {
                $filename = $viewName.'_'.now()->format('Ymd_His').'.csv';
                $headers = [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => "attachment; filename=\"$filename\"",
                ];

                $callback = function () use ($results) {
                    $handle = fopen('php://output', 'w');
                    if ($results->isNotEmpty()) {
                        // Write CSV header
                        fputcsv($handle, array_keys((array) $results->first()));
                        // Write rows
                        foreach ($results as $row) {
                            fputcsv($handle, (array) $row);
                        }
                    }
                    fclose($handle);
                };

                return response()->stream($callback, 200, $headers);
            }
        }

        return view('datacenter.explore', compact('results', 'viewName', 'availableViews'));
    }

    public function dataIndex()
    {
        abort_unless(config('app.enable_data_browser'), 404);

        $publicStudies = Study::query()->publiclyVisible();

        $genetic_studies = (clone $publicStudies)
            ->where('performs_genetic_modification', true)
            ->count();
        $drug_treatment_studies = (clone $publicStudies)
            ->where('performs_drug_treatment', true)
            ->count();

        // Before we assume analysis types exist (where()->first()->id), let's ensure that they do exist
        foreach (AnalysisTypeEnum::cases() as $analysisTypeEnum) {
            $analysisType = AnalysisType::where('code', $analysisTypeEnum->value)->first();
            if (! $analysisType) {
                throw new \Exception("Expected AnalysisType with code '{$analysisTypeEnum->value}' not found. Please ensure the seeder has been run.");
            }
        }

        $publicUploads = AnalysisUpload::query()
            ->whereHas('study', fn ($query) => $query->publiclyVisible());

        $mct_datasets = (clone $publicUploads)
            ->where('analysis_type_id', AnalysisType::where('code', 'micro_ct')->first()->id)
            ->count();
        $dexa_datasets = (clone $publicUploads)
            ->where('analysis_type_id', AnalysisType::where('code', 'dexa')->first()->id)
            ->count();
        $histo_datasets = (clone $publicUploads)
            ->where('analysis_type_id', AnalysisType::where('code', 'bone_histo')->first()->id)
            ->count();
        $mech_datasets = (clone $publicUploads)
            ->where('analysis_type_id', AnalysisType::where('code', 'mechanical_testing')->first()->id)
            ->count();
        $clinical_chem_datasets = (clone $publicUploads)
            ->where('analysis_type_id', AnalysisType::where('code', 'clinical_chemistry')->first()->id)
            ->count();

        return view('datacenter.data_index', compact('genetic_studies', 'drug_treatment_studies', 'mct_datasets', 'dexa_datasets', 'histo_datasets', 'mech_datasets', 'clinical_chem_datasets'));
    }

    // $type: gene|drug|ontology|parameter
    public function browser(Request $request, ?string $type = null)
    {
        abort_unless(config('app.enable_data_browser'), 404);

        $type = $type ?? $request->query('type');

        // Landing "All" is the existing study search, not a typed browser.
        if ($type === 'all') {
            return redirect()->route('search.index', array_filter([
                'q' => $request->query('query'),
            ]));
        }

        return view('datacenter.browser', compact('type'));
    }
}
