<?php

namespace App\Services;

use App\Models\AnalysisUpload;
use App\Models\ComparisonGroup;
use App\Models\DescriptiveStatistic;
use App\Models\InferentialStatistic;
use App\Models\Measurement;
use App\Models\MeasurementValue;
use App\Models\SampleGroup;
use App\Models\StatisticsJob;
use App\Support\DataExplorationProfiler;
use Illuminate\Support\Collection;

class StudySummaryDataService
{
    public function buildComparisonGroupStatsPayload(
        ComparisonGroup $comparisonGroup,
        Collection $analysisUploadIds,
        ?array $filterContext = null
    ): array {
        /** @var DataExplorationProfiler $profiler */
        $profiler = app(DataExplorationProfiler::class);

        return $profiler->profile('comparison_group_payload_build', [
            'comparison_group_id' => $comparisonGroup->id,
            'upload_count' => $analysisUploadIds->count(),
            'context_hash' => $filterContext ? md5((string) json_encode($filterContext)) : null,
        ], function () use ($comparisonGroup, $filterContext) {
            \Log::channel('stats')->info('BUILDING COMPARISON GROUP PAYLOAD: '.$comparisonGroup->id);
            // get sample groups in comparison group
            $sampleGroups = $comparisonGroup->sampleGroups();
            $sampleGroupIds = $sampleGroups->pluck('id')->toArray();

            // get all stats jobs
            $statsJobs = StatisticsJob::where('comparison_group_id', $comparisonGroup->id)
                ->orWhereIn('sample_group_id', $sampleGroupIds)
                ->completed()
                ->get();

            if ($statsJobs->isEmpty()) {
                return [
                    'hasStatisticsJobs' => false,
                    'sampleGroups' => $sampleGroups,
                    'sampleGroupIds' => $sampleGroupIds,
                    'inferentialStats' => collect(),
                    'descriptiveStats' => collect(),
                    'measurements' => collect(),
                    'cgMeasurements' => [],
                    'hasAnova' => false,
                    'hasTTest' => false,
                    'hasTukey' => false,
                ];
            }

            $statisticsJobIds = $statsJobs->pluck('id');

            // query for inferential and descriptive stats
            $inferentialQuery = InferentialStatistic::whereIn('statistics_job_id', $statisticsJobIds)
                ->with(['measurement'])
                ->completed();
            if ($filterContext) {
                $inferentialQuery->forContext($filterContext);
            }
            $allInferentialStats = $inferentialQuery->get();
            $inferentialStats = $allInferentialStats->groupBy('measurement_id');

            $descriptiveQuery = DescriptiveStatistic::whereIn('statistics_job_id', $statisticsJobIds)
                ->with(['measurement', 'sampleGroup']);
            if ($filterContext) {
                $descriptiveQuery->forContext($filterContext);
            }
            $descriptiveStats = $descriptiveQuery->get()->groupBy('measurement_id');

            // keep measurements stable (sort by display name of measurements)
            $measurements = $inferentialStats
                ->map(fn ($stats) => $stats->first()?->measurement)
                ->filter()
                ->unique('id')
                ->sortBy(fn ($measurement) => mb_strtolower((string) ($measurement->display_name ?? $measurement->name ?? '')))
                ->values();

            $cgMeasurements = $measurements->map(function ($measurement) {
                return [
                    'id' => $measurement->id,
                    'name' => $measurement->name,
                    'display_name' => $measurement->display_name,
                    'unit' => $measurement->unit,
                ];
            })->values()->all();

            return [
                'hasStatisticsJobs' => true,
                'sampleGroups' => $sampleGroups,
                'sampleGroupIds' => $sampleGroupIds,
                'inferentialStats' => $inferentialStats,
                'descriptiveStats' => $descriptiveStats,
                'measurements' => $measurements,
                'cgMeasurements' => $cgMeasurements,
                'hasAnova' => $allInferentialStats->contains('test_type', 'anova'),
                'hasTTest' => $allInferentialStats->contains('test_type', 't_test'),
                'hasTukey' => $allInferentialStats->contains('test_type', 'tukey_hsd'),
            ];
        });
    }

    public function buildMeasurementPlotSeriesPayload(
        array $measurement,
        Collection $analysisUploadIds,
        array $sampleGroupIds = [],
        array $baseSampleGroupIds = [],
        array $extraSampleGroupIds = []
    ): array {
        if (! isset($measurement['id'])) {
            throw new \InvalidArgumentException('Measurement id is required for plot payload queries.');
        }

        /** @var DataExplorationProfiler $profiler */
        $profiler = app(DataExplorationProfiler::class);

        return $profiler->profile('measurement_plot_payload_build', [
            'measurement_id' => $measurement['id'],
            'upload_count' => $analysisUploadIds->count(),
            'base_group_count' => count($baseSampleGroupIds),
            'extra_group_count' => count($extraSampleGroupIds),
        ], function () use ($measurement, $analysisUploadIds, $sampleGroupIds, $baseSampleGroupIds, $extraSampleGroupIds) {
            $baseSampleGroupIds = ! empty($baseSampleGroupIds) ? $baseSampleGroupIds : $sampleGroupIds;
            $combinedSampleGroupIds = array_merge($baseSampleGroupIds, $extraSampleGroupIds, $sampleGroupIds);
            $combinedSampleGroupIds = array_values(array_unique(array_filter($combinedSampleGroupIds, function ($id) {
                return $id !== null && $id !== '';
            })));
            $filterSampleGroupIds = empty($combinedSampleGroupIds) ? null : $combinedSampleGroupIds;

            $plotSeries = $this->plotSeriesFromMeasurementValues(
                $this->fetchMeasurementValues(
                    $measurement['id'],
                    $analysisUploadIds,
                    $filterSampleGroupIds
                ),
                $combinedSampleGroupIds
            );

            return [
                'plotSeries' => $plotSeries,
                'baseSampleGroupIds' => array_values($baseSampleGroupIds),
                'extraSampleGroupIds' => array_values($extraSampleGroupIds),
            ];
        });
    }

    public function buildMultipleMeasurementPlotSeriesPayloads(
        ComparisonGroup $comparisonGroup,
        array $measurements,
        Collection $analysisUploadIds
    ): array {
        if (empty($measurements)) {
            return [];
        }

        $measurementIds = array_filter(array_map(fn ($m) => $m['id'] ?? null, $measurements));
        if (empty($measurementIds)) {
            return [];
        }

        /** @var DataExplorationProfiler $profiler */
        $profiler = app(DataExplorationProfiler::class);

        return $profiler->profile('multiple_measurement_plot_payload_build', [
            'comparison_group_id' => $comparisonGroup->id,
            'measurement_count' => count($measurements),
            'upload_count' => $analysisUploadIds->count(),
        ], function () use ($comparisonGroup, $measurementIds, $analysisUploadIds) {
            // get sample groups and combined groups
            $baseSampleGroupIds = $comparisonGroup->sampleGroups()->pluck('id')->toArray();
            $extraSampleGroupIds = $this->sampleGroupsForAnalysisUploads($analysisUploadIds)
                ->whereNotIn('id', $baseSampleGroupIds)
                ->pluck('id')
                ->toArray();
            $combinedSampleGroupIds = array_merge($baseSampleGroupIds, $extraSampleGroupIds);
            $combinedSampleGroupIds = array_values(array_unique(array_filter($combinedSampleGroupIds, function ($id) {
                return $id !== null && $id !== '';
            })));
            $filterSampleGroupIds = empty($combinedSampleGroupIds) ? null : $combinedSampleGroupIds;

            // single query for all measurements, grouped by measurement_id
            $allPlotMeasures = MeasurementValue::whereHas('sample', function ($query) use ($analysisUploadIds, $filterSampleGroupIds) {
                $query->whereIn('analysis_upload_id', $analysisUploadIds);
                if ($filterSampleGroupIds) {
                    $query->whereIn('sample_group_id', $filterSampleGroupIds);
                }
            })->whereIn('measurement_id', $measurementIds)
                ->with(['sample.sampleGroup'])
                ->get()
                ->groupBy('measurement_id');

            $result = [];
            foreach ($measurementIds as $measurementId) {
                $measurementValues = $allPlotMeasures->get($measurementId, collect());

                $result[$measurementId] = [
                    'plotSeries' => $this->plotSeriesFromMeasurementValues(
                        $measurementValues,
                        $combinedSampleGroupIds
                    ),
                    'baseSampleGroupIds' => array_values($baseSampleGroupIds),
                    'extraSampleGroupIds' => array_values($extraSampleGroupIds),
                ];
            }

            return $result;
        });
    }

    /**
     * build modal payload for a single measurement
     * accepts optional cgMeasurements to avoid re-fetching comparison group payload
     */
    public function buildModalPayload(
        int $measurementId,
        int $comparisonGroupId,
        Collection $analysisUploadIds,
        ?array $filterContext = null,
        array $cgMeasurements = []
    ): array {
        /** @var DataExplorationProfiler $profiler */
        $profiler = app(DataExplorationProfiler::class);

        return $profiler->profile('modal_payload_build', [
            'measurement_id' => $measurementId,
            'comparison_group_id' => $comparisonGroupId,
            'upload_count' => $analysisUploadIds->count(),
            'cg_measurements_cached' => ! empty($cgMeasurements),
        ], function () use ($measurementId, $comparisonGroupId, $analysisUploadIds, $filterContext, $cgMeasurements) {
            $comparisonGroup = ComparisonGroup::find($comparisonGroupId);
            if (! $comparisonGroup) {
                return ['error' => 'Comparison group not found'];
            }

            // fallback to build cg payload if no cgMeasurements are provided
            if (empty($cgMeasurements)) {
                $cgPayload = $this->buildComparisonGroupStatsPayload($comparisonGroup, $analysisUploadIds, $filterContext);
                $cgMeasurements = $cgPayload['cgMeasurements'] ?? [];
            }

            $sampleGroups = $comparisonGroup->sampleGroups();
            $sampleGroupIds = $sampleGroups->pluck('id')->toArray();

            // get additional sample groups
            $allSampleGroupsInUploads = $this->sampleGroupsForAnalysisUploads($analysisUploadIds);
            $additionalSampleGroups = $allSampleGroupsInUploads->whereNotIn('id', $sampleGroupIds)->values();
            $extraSampleGroupIds = $additionalSampleGroups->pluck('id')->toArray();

            // get measurement info
            $measurement = Measurement::find($measurementId);
            if (! $measurement) {
                return ['error' => 'Measurement not found'];
            }

            $measurementArray = [
                'id' => $measurement->id,
                'name' => $measurement->name,
                'display_name' => $measurement->display_name,
                'unit' => $measurement->unit,
            ];

            $allRequestedSampleGroupIds = array_values(array_unique(array_merge($sampleGroupIds, $extraSampleGroupIds)));
            $measurementValues = $this->fetchMeasurementValues(
                $measurementId,
                $analysisUploadIds,
                $allRequestedSampleGroupIds,
                [
                    'measurement',
                    'sample.sampleGroup.animalModel',
                    'sample.analysisUpload.specimenType',
                    'sample.analysisUpload.region',
                ]
            );

            $plotPayload = [
                'plotSeries' => $this->plotSeriesFromMeasurementValues(
                    $measurementValues,
                    $allRequestedSampleGroupIds
                ),
                'baseSampleGroupIds' => array_values($sampleGroupIds),
                'extraSampleGroupIds' => array_values($extraSampleGroupIds),
            ];

            $tableRowsBySampleGroup = $this->tableRowsFromMeasurementValues($measurementValues);

            $tableData = [];
            foreach ($sampleGroupIds as $sampleGroupId) {
                $tableData = array_merge($tableData, $tableRowsBySampleGroup[$sampleGroupId] ?? []);
            }

            // get context description from first analysis upload
            $contextDescription = null;
            $contextDescriptionSlug = null;
            if ($analysisUploadIds->isNotEmpty()) {
                $firstUpload = AnalysisUpload::whereIn('id', $analysisUploadIds)
                    ->with(['analysisType', 'specimenType', 'analyteCategory', 'region', 'mechanicalTestingDetails'])
                    ->first();
                if ($firstUpload) {
                    $contextDescription = $firstUpload->getContextDescription(withAnalysisType: true);
                    $contextDescriptionSlug = $firstUpload->getContextDescription(withAnalysisType: true, asSlug: true);
                }
            }

            // $additionalTableData = [];
            // foreach ($additionalSampleGroups as $sg) {
            //     $additionalTableData[$sg->id] = [
            //         'label' => $sg->label,
            //         'rows' => $tableRowsBySampleGroup[$sg->id] ?? [],
            //     ];
            // }

            return [
                'measurement' => $measurementArray,
                'cgMeasurements' => $cgMeasurements,
                'sampleGroups' => $sampleGroups->map(fn ($sg) => [
                    'id' => $sg->id,
                    'label' => $sg->label,
                ])->values()->toArray(),
                'sampleGroupIds' => $sampleGroupIds,
                'additionalSampleGroups' => $additionalSampleGroups->map(fn ($sg) => [
                    'id' => $sg->id,
                    'label' => $sg->label,
                ])->values()->toArray(),
                'plotPayload' => $plotPayload,
                'tableData' => $tableData,
                'contextDescription' => $contextDescription,
                'contextDescriptionSlug' => $contextDescriptionSlug,
                // 'additionalTableData' => $additionalTableData,
            ];
        });
    }

    private function fetchMeasurementValues(
        int $measurementId,
        Collection $analysisUploadIds,
        ?array $sampleGroupIds = null,
        array $with = ['sample.sampleGroup']
    ): Collection {
        return MeasurementValue::whereHas('sample', function ($query) use ($analysisUploadIds, $sampleGroupIds) {
            $query->whereIn('analysis_upload_id', $analysisUploadIds);
            if (! empty($sampleGroupIds)) {
                $query->whereIn('sample_group_id', $sampleGroupIds);
            }
        })
            ->where('measurement_id', $measurementId)
            ->with($with)
            ->get();
    }

    /**
     * Build plot series ordered by preferred sample group IDs (comparison group
     * order, then extras). Without a stable order, unordered DB rows make
     * series shuffle on remount and box plot colors appear to cycle.
     *
     * @param  array<int, int|string>  $preferredSampleGroupIds
     * @return array<int, array{sample_group_id: int, label: mixed, points: array<int, array{value: mixed, sample_label: mixed}>}>
     */
    private function plotSeriesFromMeasurementValues(Collection $measurementValues, array $preferredSampleGroupIds = []): array
    {
        $seriesByGroupId = $measurementValues
            ->groupBy(fn ($value) => (int) $value->sample->sample_group_id)
            ->map(function ($group) {
                $first = $group->first();

                return [
                    'sample_group_id' => (int) $first->sample->sample_group_id,
                    'label' => $first->sample->sampleGroup->label,
                    'points' => $group->map(function ($item) {
                        return [
                            'value' => $item->value,
                            'sample_label' => $item->sample->label,
                        ];
                    })->sortBy('sample_label')->values()->toArray(),
                ];
            });

        $preferredSampleGroupIds = array_values(array_unique(array_map('intval', $preferredSampleGroupIds)));

        if ($preferredSampleGroupIds === []) {
            return $seriesByGroupId->sortKeys()->values()->toArray();
        }

        $ordered = [];
        foreach ($preferredSampleGroupIds as $id) {
            if ($seriesByGroupId->has($id)) {
                $ordered[] = $seriesByGroupId->get($id);
                $seriesByGroupId->forget($id);
            }
        }

        return array_merge($ordered, $seriesByGroupId->sortKeys()->values()->all());
    }

    private function tableRowsFromMeasurementValues(Collection $measurementValues): array
    {
        return $measurementValues
            ->groupBy(fn ($data) => $data->sample->sample_group_id)
            ->map(function ($groupRows) {
                return $groupRows->map(function ($data) {
                    return [
                        'sample_id' => $data->sample->id ?? null,
                        'sample_group_label' => $data->sample->sampleGroup->label ?? 'N/A',
                        'value' => $data->value,
                        'bone_type' => $data->sample->analysisUpload->specimenType->name ?? 'N/A',
                        'region' => $data->sample->analysisUpload->region->label ?? 'N/A',
                        'sex' => ucfirst($data->sample->sampleGroup->sex ?? 'N/A'),
                        'age' => $data->sample->sampleGroup->age ?? 'N/A',
                    ];
                })->values()->toArray();
            })
            ->all();
    }

    private function sampleGroupsForAnalysisUploads(Collection $analysisUploadIds): Collection
    {
        if ($analysisUploadIds->isEmpty()) {
            return collect();
        }

        $uploadsWithGroups = AnalysisUpload::whereIn('id', $analysisUploadIds)->get();
        $allSampleGroupIds = $uploadsWithGroups->pluck('sample_group_ids')->flatten()->unique()->filter();

        if ($allSampleGroupIds->isEmpty()) {
            return collect();
        }

        return SampleGroup::whereIn('id', $allSampleGroupIds)->get();
    }
}
