<?php

namespace App\Http\Controllers;

use App\Enums\UploadSteps;
use App\Models\Study;
use App\Services\StudyOriginalDataZipService;
use App\Services\StudyService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudySummaryController extends Controller
{
    public function __construct(
        private StudyService $studyService,
        private StudyOriginalDataZipService $originalDataZip,
    ) {}

    public function show(Study $study)
    {
        // Redirect to the information tab by default
        return redirect()->route('study.summary.information', $study);
    }

    public function information(Study $study)
    {
        $study->load([
            'animalModels.animalLine',
            'animalModels.gene',
            'animalModels.targetGenes',
            'animalModels.parents.animalLine',
            'animalModels.parents.gene',
            'animalModels.geneticCategory',
            'animalModels.geneticMethod',
            'sampleGroups.animalModel.animalLine',
            'sampleGroups.animalModel.parents.animalLine',
            'sampleGroups.animalModel.geneticCategory',
            'sampleGroups.animalModel.geneticMethod',
            'sampleGroups.drugAdministrationPlan',
            'sampleGroups.gonadectomyProcedure',
        ]);

        return view('study.summary.information', compact('study'));
    }

    public function exploration(Study $study)
    {
        return view('study.summary.exploration', compact('study'));
    }

    public function download(Study $study)
    {
        $uploads = $study->analysisUploads()
            ->with(['analysisType', 'specimenType', 'region'])
            ->where('current_step', UploadSteps::COMPLETE)
            ->get();

        $mediaUploads = $study->mediaUploads()
            ->with(['sampleGroup'])
            ->downloadable()
            ->orderBy('original_filename')
            ->get();

        $downloadAllBytes = (int) $uploads->sum('size') + (int) $mediaUploads->sum('size');
        $downloadAllSize = $this->originalDataZip->formatBytes($downloadAllBytes);

        return view('study.summary.download', compact(
            'study',
            'uploads',
            'mediaUploads',
            'downloadAllBytes',
            'downloadAllSize',
        ));
    }

    public function downloadAll(Study $study): StreamedResponse
    {
        return $this->originalDataZip->stream($study);
    }
}
