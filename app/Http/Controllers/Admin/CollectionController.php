<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StudyStatus;
use App\Http\Controllers\Controller;
use App\Models\AnalysisUpload;
use App\Models\AnimalModel;
use App\Models\Collection;
use App\Models\SampleGroup;
use App\Models\Study;
use App\Services\StudyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CollectionController extends Controller
{
    public function index()
    {
        $collections = Collection::withStudyStatusCounts()
            ->with('investigators')
            ->latest()
            ->paginate(25);

        return view('collections.index', compact('collections'));
    }

    public function create()
    {
        $collection = new Collection([
            'user_id' => Auth::id(),
        ]);

        return view('collections.collection-information', compact('collection'));
    }

    public function edit(Collection $collection)
    {
        return view('collections.collection-information', compact('collection'));
    }

    public function store(Request $request)
    {
        $collection = Collection::create([
            'user_id' => Auth::id(),
        ]);

        return redirect()
            ->route('admin.collections.edit', $collection)
            ->with('success', 'Collection created. Fill in the details below.');
    }

    public function update(Request $request, Collection $collection)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string',
            'funding_sources' => 'nullable|string',
            'conflicts' => 'nullable|string',
            'completion_date' => 'nullable|date',
        ]);

        $collection->update($validated);

        return redirect()
            ->route('admin.collections.edit', $collection)
            ->with('success', 'Collection information updated.');
    }

    /**
     * All studies belonging to a collection, with their animal model and a count
     * of analysis uploads so gaps in data coverage are visible.
     */
    public function studies(Request $request, Collection $collection)
    {
        $query = $collection->studies();

        // Optional filter for working backwards through lines still missing data
        if ($request->query('missing_data')) {
            $withData = AnalysisUpload::whereIn('study_id', $collection->studies()->select('id'))
                ->distinct()
                ->pluck('study_id');

            $query->whereNotIn('id', $withData);
        }

        $studies = $query->orderBy('id')->paginate(50)->withQueryString();

        $studyIds = $studies->pluck('id');

        // Joined rather than eager loaded so this does not depend on relationship
        // method names existing on Study or AnimalModel.
        // A study now has two animal models (the KO line and the wild-type
        // control). Prefer the experimental one (has a gene) for this listing,
        // so the Gene and Animal Model columns show the knockout, not the WT.
        $animalModels = AnimalModel::whereIn('animal_models.study_id', $studyIds)
            ->whereNotNull('animal_models.gene_id')
            ->leftJoin('genes', 'animal_models.gene_id', '=', 'genes.id')
            ->get([
                'animal_models.study_id',
                'animal_models.animal_model_name',
                'genes.symbol as gene_symbol',
            ])
            ->keyBy('study_id');

        $uploadCounts = AnalysisUpload::whereIn('study_id', $studyIds)
            ->selectRaw('study_id, count(*) as total')
            ->groupBy('study_id')
            ->pluck('total', 'study_id');

        // Sample groups per study, joined to their animal model so we can show
        // which model each group points at without loading full relationships.
        $sampleGroups = SampleGroup::whereIn('sample_groups.study_id', $studyIds)
            ->leftJoin('animal_models', 'sample_groups.animal_model_id', '=', 'animal_models.id')
            ->orderBy('sample_groups.control')
            ->orderBy('sample_groups.sex')
            ->get([
                'sample_groups.study_id',
                'sample_groups.label',
                'sample_groups.sex',
                'sample_groups.genotype',
                'sample_groups.control',
                'sample_groups.age',
                'animal_models.animal_model_name',
            ])
            ->groupBy('study_id');

        // Coverage across the whole collection, not just the current page
        $totalStudies = $collection->studies()->count();
        $studiesWithData = AnalysisUpload::whereIn('study_id', $collection->studies()->select('id'))
            ->distinct('study_id')
            ->count('study_id');

        $studyService = app(StudyService::class);
        $approvalBlockerCount = $studyService->getCollectionApprovalBlockers($collection);
        $canApproveCollection = $studyService->canApproveCollection($collection);
        $pendingStudyCount = $collection->studies()
            ->where('status', StudyStatus::PENDING)
            ->count();

        $collection->loadCount([
            'studies',
            'studies as studies_in_progress_count' => fn ($q) => $q->where('status', StudyStatus::IN_PROGRESS),
            'studies as studies_pending_count' => fn ($q) => $q->where('status', StudyStatus::PENDING),
            'studies as studies_approved_count' => fn ($q) => $q->where('status', StudyStatus::APPROVED),
            'studies as studies_rejected_count' => fn ($q) => $q->where('status', StudyStatus::REJECTED),
        ]);

        return view('collections.studies', compact(
            'collection',
            'studies',
            'animalModels',
            'uploadCounts',
            'sampleGroups',
            'totalStudies',
            'studiesWithData',
            'approvalBlockerCount',
            'canApproveCollection',
            'pendingStudyCount',
        ));
    }

    /**
     * Remove a single study from a collection.
     */
    public function submit(Collection $collection, StudyService $studyService)
    {
        $errors = $studyService->submitCollection($collection, Auth::user());

        if (! empty($errors)) {
            return redirect()
                ->route('admin.collections.studies', $collection)
                ->with('error', $studyService->firstErrorMessage($errors));
        }

        return redirect()
            ->route('admin.collections.studies', $collection)
            ->with('success', 'Collection submitted for ROSSA review.');
    }

    /**
     * Approve every PENDING study in this collection, then mark the collection
     * itself approved. Gated by manage-collections at the route level — a
     * single deliberate action rather than approving studies one at a time.
     */
    public function approve(Collection $collection, StudyService $studyService)
    {
        try {
            $studyService->approveCollection($collection, Auth::user());
        } catch (\Throwable $exception) {
            return redirect()
                ->route('admin.collections.studies', $collection)
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.collections.studies', $collection)
            ->with('success', 'Collection approved. All submitted studies are now public.');
    }

    public function approveStudies(Request $request, Collection $collection, StudyService $studyService)
    {
        $validated = $request->validate([
            'study_ids' => 'required|array|min:1',
            'study_ids.*' => 'integer|exists:studies,id',
        ]);

        try {
            $approved = $studyService->approveCollectionStudies(
                $collection,
                $validated['study_ids'],
                Auth::user()
            );
        } catch (\Throwable $exception) {
            return redirect()
                ->route('admin.collections.studies', $collection)
                ->with('error', $exception->getMessage());
        }

        if ($approved === 0) {
            return redirect()
                ->route('admin.collections.studies', $collection)
                ->with('error', 'No pending studies were selected for approval.');
        }

        return redirect()
            ->route('admin.collections.studies', $collection)
            ->with('success', "{$approved} studies approved. The collection remains awaiting final approval.");
    }

    public function destroyStudy(Collection $collection, Study $study)
    {
        if ($study->collection_id !== $collection->id) {
            abort(404);
        }

        $study->delete();

        return redirect()
            ->route('admin.collections.studies', $collection)
            ->with('success', 'Study removed from this collection.');
    }

    public function destroy(Collection $collection)
    {
        // Guard: only allow deletion when no studies are attached, so we never
        // orphan or destroy ingested data by deleting its parent collection.
        if ($collection->studies()->count() > 0) {
            return redirect()
                ->route('admin.collections')
                ->with('error', 'This collection has studies attached and cannot be deleted.');
        }

        $collection->delete();

        return redirect()
            ->route('admin.collections')
            ->with('success', 'Collection deleted.');
    }
}
