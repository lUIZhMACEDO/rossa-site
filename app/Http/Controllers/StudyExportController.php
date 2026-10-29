<?php

namespace App\Http\Controllers;

use App\Enums\StudyStatus;
use App\Enums\UploadSteps;
use App\Models\ComparisonGroup;
use App\Models\Study;
use App\Services\StudySummaryDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudyExportController extends Controller
{
    public function __construct(
        private StudySummaryDataService $summaryDataService
    ) {}

    public function statsCsv(Request $request, Study $study): StreamedResponse
    {
        // If not approved, only users allowed to manage this study may export.
        if ($study->status !== StudyStatus::APPROVED) {
            $user = Auth::user();
            if (! $user || ! $user->canEditStudy($study)) {
                abort(403, 'You are not authorized to export this study.');
            }
        }

        $request->validate([
            'comparison_group_id' => 'required|integer',
            'context' => 'required|string',
        ]);

        $comparisonGroupId = $request->integer('comparison_group_id');
        $contextJson = $request->input('context');
        $filterContext = json_decode($contextJson, true);

        // get comparison group
        $comparisonGroup = $study->comparisonGroups()->findOrFail($comparisonGroupId);

        // get upload ids for the context
        $uploadIds = $this->getUploadIdsForContext($study, $filterContext);

        if ($uploadIds->isEmpty()) {
            abort(404, 'No uploads found for this context.');
        }

        // build stats payload using service
        $payload = $this->summaryDataService->buildComparisonGroupStatsPayload(
            $comparisonGroup,
            $uploadIds,
            $filterContext
        );

        if (! $payload['hasStatisticsJobs'] || $payload['measurements']->isEmpty()) {
            abort(404, 'No statistics available for export.');
        }

        // build the csv response
        return $this->buildCsvResponse($payload, $comparisonGroup, $study);
    }

    // get upload ids for the context
    private function getUploadIdsForContext(Study $study, ?array $filterContext): Collection
    {
        $query = $study->analysisUploads()
            ->where('current_step', UploadSteps::COMPLETE);

        if ($filterContext) {
            foreach (['analysis_type_id', 'specimen_type_id', 'analyte_category_id', 'region_id'] as $field) {
                if (! array_key_exists($field, $filterContext)) {
                    continue;
                }

                if ($filterContext[$field] === null) {
                    $query->whereNull($field);
                } else {
                    $query->where($field, $filterContext[$field]);
                }
            }
        }

        return $query->pluck('id');
    }

    // build the csv response from the stats payload
    private function buildCsvResponse(
        array $payload,
        ComparisonGroup $comparisonGroup,
        Study $study
    ): StreamedResponse {
        $sampleGroups = $payload['sampleGroups'];
        $measurements = $payload['measurements'];
        $descriptiveStats = $payload['descriptiveStats'];
        $inferentialStats = $payload['inferentialStats'];
        $hasTTest = $payload['hasTTest'];
        $hasAnova = $payload['hasAnova'];
        $hasTukey = $payload['hasTukey'];

        $filename = $this->generateFilename($study, $comparisonGroup);

        return response()->streamDownload(function () use (
            $sampleGroups,
            $measurements,
            $descriptiveStats,
            $inferentialStats,
            $hasTTest,
            $hasAnova,
            $hasTukey
        ) {
            $handle = fopen('php://output', 'w');

            // build header row
            $headers = ['variable', 'unit'];
            foreach ($sampleGroups as $sg) {
                $label = $this->sanitizeColumnName($sg->label);
                $headers[] = "{$label}_mean";
                $headers[] = "{$label}_sd";
                $headers[] = "{$label}_n";
            }
            if ($hasTTest) {
                $headers[] = 'ttest_pvalue';
            }
            if ($hasAnova) {
                $headers[] = 'anova_pvalue';
            }
            if ($hasTukey) {
                $headers[] = 'tukey_hsd';
            }
            fputcsv($handle, $headers);

            // build data rows
            foreach ($measurements as $measurement) {
                $row = [
                    $measurement->display_name ?? $measurement->name,
                    $measurement->unit ?? '',
                ];

                // get descriptive stats for each sample group
                $descStats = $descriptiveStats->get($measurement->id, collect());
                foreach ($sampleGroups as $sg) {
                    $stat = $descStats->firstWhere('sample_group_id', $sg->id);
                    $row[] = $stat?->mean ?? '';
                    $row[] = $stat?->sd ?? '';
                    $row[] = $stat?->n ?? '';
                }

                // get inferential stats
                $infStats = $inferentialStats->get($measurement->id, collect());
                if ($hasTTest) {
                    $ttest = $infStats->firstWhere('test_type', 't_test');
                    $row[] = $ttest?->p_value ?? '';
                }
                if ($hasAnova) {
                    $anova = $infStats->firstWhere('test_type', 'anova');
                    $row[] = $anova?->p_value ?? '';
                }
                if ($hasTukey) {
                    $tukey = $infStats->firstWhere('test_type', 'tukey_hsd');
                    $row[] = $tukey?->metadata['display_string'] ?? '';
                }

                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function generateFilename(Study $study, ComparisonGroup $comparisonGroup): string
    {
        $studyId = $study->identifier ?? "study-{$study->id}";
        $cgLabel = $comparisonGroup->sampleGroups()
            ->map(fn ($sg) => $this->sanitizeColumnName($sg->label))
            ->join('_vs_');
        $date = now()->format('Y-m-d');

        return "{$studyId}_{$cgLabel}_stats_{$date}.csv";
    }

    private function sanitizeColumnName(string $name): string
    {
        return preg_replace('/[^a-z0-9]+/', '_', strtolower(trim($name)));
    }
}
