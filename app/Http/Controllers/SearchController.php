<?php

namespace App\Http\Controllers;

use App\Enums\PermissionType;
use App\Enums\StudyStatus;
use App\Enums\UploadSteps;
use App\Models\Collection;
use App\Models\MediaUpload;
use App\Models\Study;
use App\Models\StudyFigure;
use App\Services\StudyOriginalDataZipService;
use App\Support\StorageFileResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SearchController extends Controller
{
    public function __construct(private StudyOriginalDataZipService $originalDataZip) {}

    private function verifyPublicStudy(Study $study): void
    {
        if ($study->isPubliclyVisible()) {
            return;
        }

        $user = auth()->user();

        if ($user && ($study->isOwner($user) || $user->hasPermission(PermissionType::MANAGE_STUDIES->value))) {
            return;
        }

        abort(404);
    }

    private function studyBackContext(Request $request): array
    {
        $return = $request->query('return');

        if ($return && str_starts_with($return, config('app.url'))) {
            return [
                'backUrl' => $return,
                'backLabel' => 'Back to Data Browser',
                'routeParams' => ['return' => $return],
            ];
        }

        $routeParams = array_filter(['q' => $request->query('q')]);

        return [
            'backUrl' => route('search.index', $routeParams),
            'backLabel' => 'Back to Search',
            'routeParams' => $routeParams,
        ];
    }

    public function index(Request $request)
    {
        $query = $request->input('q');
        $studies = collect();

        $studies = Study::query()
            ->publiclyVisible()
            ->when($query, function ($q) use ($query) {
                $q->deepSearch($query);
            })
            ->with([
                'investigators',
                'animalModels.animalLine',
                'animalModels.gene',
                'animalModels.targetGenes',
                'animalModels.parents.animalLine',
                'animalModels.parents.gene',
                'animalModels.geneticCategory',
                'diseaseLines',
                'drugAdministrationPlans.events.molecule',
                'tags',
                'analysisUploads.analysisType',
                'sampleGroups',
                'collection',
            ])
            ->orderBy('completion_date', 'desc')
            ->orderBy('submitted_at', 'desc')
            ->paginate(10);

        return view('search.index', compact('studies', 'query'));
    }

    public function show(Request $request, Study $study)
    {
        $this->verifyPublicStudy($study);

        $study->load([
            'investigators',
            'collection',
            'collection.investigators',
            'collection.publications',
            'animalModels.animalLine',
            'animalModels.gene',
            'animalModels.targetGenes',
            'animalModels.parents.animalLine',
            'animalModels.parents.gene',
            'animalModels.geneticCategory',
            'animalModels.geneticMethod',
            'animalModels.inducibleDrugTreatment.molecule',
            'diseaseLines',
            'drugAdministrationPlans.events.molecule',
            'gonadectomyProcedures',
            'sampleGroups.animalModel.animalLine',
            'sampleGroups.animalModel.parents.animalLine',
            'sampleGroups.animalModel.geneticCategory',
            'sampleGroups.animalModel.geneticMethod',
            'sampleGroups.drugAdministrationPlan',
            'sampleGroups.gonadectomyProcedure',
            'comparisonGroups',
            'tags',
        ]);

        return view('search.show', array_merge(compact('study'), $this->studyBackContext($request)));
    }

    public function showCollection(Request $request, Collection $collection)
    {
        $user = auth()->user();
        $isPreview = $user && $collection->canUserView($user);

        // Public visitors only ever see an approved collection. Authorized
        // viewers (admin, or the corresponding investigator) can preview it
        // at any stage, so they can see how the report looks as it fills out.
        abort_unless($collection->status === 'approved' || $isPreview, 404);

        $collection->load(['investigators', 'publications']);

        $studiesQuery = $collection->studies()->with('animalModels.gene')->orderBy('id');

        if (! $isPreview) {
            // The public only ever sees approved studies.
            $studiesQuery->where('status', StudyStatus::APPROVED);
        }
        // In preview mode, show every study regardless of status, since a
        // collection early in the process may have none approved yet.

        $studies = $studiesQuery->get();

        $from = $request->query('from');
        $navContext = in_array($from, ['admin', 'investigator'], true)
            ? $from
            : ($user && $collection->hasCorrespondingUser($user) ? 'investigator' : 'admin');

        return view('collections.show', compact('collection', 'studies', 'isPreview', 'navContext'));
    }

    public function dataExploration(Request $request, Study $study)
    {
        $this->verifyPublicStudy($study);

        return view('search.data-exploration', array_merge(compact('study'), $this->studyBackContext($request)));
    }

    public function dataDownload(Request $request, Study $study)
    {
        $this->verifyPublicStudy($study);

        $uploads = $study->analysisUploads()
            ->with(['measurements', 'analysisType', 'specimenType', 'region'])
            ->where('current_step', UploadSteps::COMPLETE)
            ->get();

        $mediaUploads = $study->mediaUploads()
            ->with(['sampleGroup'])
            ->downloadable()
            ->orderBy('original_filename')
            ->get();

        $downloadAllBytes = (int) $uploads->sum('size') + (int) $mediaUploads->sum('size');
        $downloadAllSize = $this->originalDataZip->formatBytes($downloadAllBytes);

        return view('search.data-download', array_merge(compact(
            'study',
            'uploads',
            'mediaUploads',
            'downloadAllBytes',
            'downloadAllSize',
        ), $this->studyBackContext($request)));
    }

    public function downloadMedia(Study $study, MediaUpload $media): StreamedResponse
    {
        $this->verifyPublicStudy($study);

        abort_unless(
            (int) $media->study_id === (int) $study->id && $media->isDownloadable() && Storage::exists($media->path),
            404
        );

        return StorageFileResponse::download($media->path, $media->original_filename, $media->mime_type);
    }

    public function showFigure(Study $study, StudyFigure $figure): StreamedResponse
    {
        $this->verifyPublicStudy($study);

        abort_unless(
            (int) $figure->study_id === (int) $study->id && Storage::exists($figure->path),
            404
        );

        return Storage::response($figure->path, $figure->original_filename, [
            'Content-Type' => $figure->mime_type ?? 'application/octet-stream',
        ]);
    }

    public function downloadAll(Study $study): StreamedResponse
    {
        $this->verifyPublicStudy($study);

        return $this->originalDataZip->stream($study);
    }
}
