<?php

namespace App\Jobs;

use App\Enums\UploadSteps;
use App\Models\AnalysisUpload;
use App\Models\ComparisonGroup;
use App\Models\DescriptiveStatistic;
use App\Models\InferentialStatistic;
use App\Models\Sample;
use App\Models\StatisticsJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use MathPHP\Statistics\ANOVA;
use Symfony\Component\Process\Process;

class ProcessInferentialStatistics implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $expectedVersion;

    public function __construct(
        public StatisticsJob $statisticsJob
    ) {
        // capture version at dispatch time
        $this->expectedVersion = $statisticsJob->processing_version;
    }

    public function handle(): void
    {
        try {
            // check if this job is still current (abort stale queued jobs immediately)
            $this->statisticsJob->refresh();
            if ($this->statisticsJob->processing_version !== $this->expectedVersion) {
                Log::channel('stats')->info('Aborting stale inferential statistics job - newer version dispatched', [
                    'job_id' => $this->statisticsJob->id,
                    'expected_version' => $this->expectedVersion,
                    'current_version' => $this->statisticsJob->processing_version,
                ]);

                return; // abort immediately
            }

            // mark as processing
            $this->statisticsJob->update(['status' => 'processing']);

            // get comparison group and context
            $comparisonGroup = $this->statisticsJob->comparisonGroup;
            $context = $this->statisticsJob->getContext();

            // remove old inferential statistics for this comparison group, context
            InferentialStatistic::where('comparison_group_id', $this->statisticsJob->comparison_group_id)
                ->where('analysis_type_id', $this->statisticsJob->analysis_type_id)
                ->where('specimen_type_id', $this->statisticsJob->specimen_type_id)
                ->where('analyte_category_id', $this->statisticsJob->analyte_category_id)
                ->where('region_id', $this->statisticsJob->region_id)
                ->where('test_method', $this->statisticsJob->test_method)
                ->where('bone_tested', $this->statisticsJob->bone_tested)
                ->delete();

            // get sample groups
            $sampleGroupIds = $comparisonGroup->sample_group_ids ?? [];
            if (count($sampleGroupIds) < 2) {
                throw new \Exception('Comparison group must have at least 2 sample groups');
            }

            // get all measurement data structured by measurement and group
            // [measurement_id => [group_id => [values]]]
            $measurementData = $this->getAllMeasurementsInContext($comparisonGroup->study_id, $sampleGroupIds, $context);

            if (empty($measurementData)) {
                Log::channel('stats')->info('No measurements found in context', [
                    'comparison_group_id' => $comparisonGroup->id,
                    'context' => $context,
                ]);

                // only mark completed if this is the latest version
                $this->statisticsJob->refresh();
                if ($this->statisticsJob->processing_version === $this->expectedVersion) {
                    $this->statisticsJob->update([
                        'status' => 'completed',
                        'measurements_count' => 0,
                        'completed_at' => now(),
                        'error_message' => 'No measurements in this context',
                    ]);
                }

                return;
            }

            // calculate inferential stats using batch Python processing
            $batchResults = $this->runBatchPythonStats(
                $this->statisticsJob,
                $comparisonGroup,
                $measurementData,
                $context
            );

            $totalMeasurements = count($measurementData);
            $processedCount = count($batchResults['processed_measurements'] ?? []);
            $insufficientCount = count($batchResults['unprocessed_measurements'] ?? []);
            $statsCreated = count($batchResults['stats_created'] ?? []);

            Log::channel('stats')->info('Batch processing summary', [
                'total_measurements' => $totalMeasurements,
                'measurements_processed' => $processedCount,
                'measurements_insufficient_data' => $insufficientCount,
                'measurements_with_stats' => $statsCreated,
            ]);

            // final check: only mark completed if we're still the latest version
            $this->statisticsJob->refresh();
            if ($this->statisticsJob->processing_version === $this->expectedVersion) {
                // we're still the latest - mark as completed
                $this->statisticsJob->update([
                    'status' => 'completed',
                    'measurements_count' => $statsCreated,
                    'completed_at' => now(),
                ]);

                Log::channel('stats')->info('Inferential statistics completed', [
                    'job_id' => $this->statisticsJob->id,
                    'comparison_group_id' => $comparisonGroup->id,
                    'context' => $context,
                    'measurements_count' => $statsCreated,
                ]);
            } else {
                // newer version was dispatched - don't mark as completed
                Log::channel('stats')->info('Inferential statistics finished but NOT marking as completed - newer version pending', [
                    'job_id' => $this->statisticsJob->id,
                    'expected_version' => $this->expectedVersion,
                    'current_version' => $this->statisticsJob->processing_version,
                ]);
            }

        } catch (\Exception $e) {
            $this->statisticsJob->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::channel('stats')->error('Inferential statistics job failed', [
                'job_id' => $this->statisticsJob->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    protected function getAllMeasurementsInContext(int $studyId, array $sampleGroupIds, array $context): array
    {
        // get uploads in this study + context (only complete uploads)
        $uploadIds = AnalysisUpload::where('study_id', $studyId)
            ->where('analysis_type_id', $context['analysis_type_id'])
            ->where('specimen_type_id', $context['specimen_type_id'])
            ->where('analyte_category_id', $context['analyte_category_id'])
            ->where('region_id', $context['region_id'])
            ->where('test_method', $context['test_method'])
            ->where('bone_tested', $context['bone_tested'])
            ->where('current_step', UploadSteps::COMPLETE)
            ->pluck('id')
            ->toArray();

        if (empty($uploadIds)) {
            return [];
        }

        // get all samples
        $samples = Sample::whereIn('analysis_upload_id', $uploadIds)
            ->whereIn('sample_group_id', $sampleGroupIds)
            ->with('measurementValues')
            ->get();

        // build structure: [measurement_id => [sample_group_id => [values]]]
        $measurementData = [];

        foreach ($samples as $sample) {
            foreach ($sample->measurementValues as $mv) {
                if (! is_numeric($mv->value)) {
                    continue;
                }

                $measurementId = $mv->measurement_id;
                $groupId = $sample->sample_group_id;

                if (! isset($measurementData[$measurementId])) {
                    $measurementData[$measurementId] = [];
                }
                if (! isset($measurementData[$measurementId][$groupId])) {
                    $measurementData[$measurementId][$groupId] = [];
                }

                $measurementData[$measurementId][$groupId][] = (float) $mv->value;
            }
        }

        return $measurementData;
    }

    protected function runBatchPythonStats(
        StatisticsJob $statisticsJob,
        ComparisonGroup $comparisonGroup,
        array $measurementData,
        array $context
    ): array {
        try {
            // prepare batch input data for Python script
            $pythonInput = ['measurements' => []];
            $measurementMeta = []; // track metadata for each measurement
            $unprocessedMeasurements = []; // measurements without sufficient data

            foreach ($measurementData as $measurementId => $groupedValues) {
                // check which sample groups have sufficent data
                // groupedData has sufficient data
                $groupedData = [];
                // insufficient
                $insufficientGroups = [];
                // groups without have NO data (not listed in groupedValues)
                $groupsWithoutData = [];

                foreach ($groupedValues as $groupId => $values) {
                    if (count($values) >= 3) {
                        $groupedData[$groupId] = [
                            'group_id' => $groupId,
                            'values' => $values,
                        ];
                    } else {
                        $insufficientGroups[] = $groupId;
                    }
                }

                $groupsWithoutData = array_values(array_diff(
                    array_map('intval', $comparisonGroup->sample_group_ids ?? []),
                    array_keys($groupedValues)
                ));

                // Only process measurements with sufficient data
                if (count($groupedData) >= 2) {
                    $measurement = [
                        'measurement_id' => $measurementId,
                        'groups' => [],
                    ];

                    foreach ($groupedData as $groupId => $data) {
                        // does this work?
                        $measurement['groups'][] = $data;
                    }

                    $pythonInput['measurements'][] = $measurement;

                    // store metadata for later
                    $measurementMeta[$measurementId] = [
                        'grouped_data' => $groupedData,
                        'insufficient_groups' => $insufficientGroups,
                        'groups_without_data' => $groupsWithoutData,
                    ];
                } else {
                    // stats entry for measurement with insufficient data
                    InferentialStatistic::updateOrCreate(
                        [
                            'statistics_job_id' => $statisticsJob->id,
                            'comparison_group_id' => $comparisonGroup->id,
                            'measurement_id' => $measurementId,
                            'analysis_type_id' => $context['analysis_type_id'],
                            'specimen_type_id' => $context['specimen_type_id'],
                            'analyte_category_id' => $context['analyte_category_id'],
                            'region_id' => $context['region_id'],
                            'test_method' => $context['test_method'],
                            'bone_tested' => $context['bone_tested'],
                            'test_type' => 'none',
                        ],
                        [
                            'status' => 'insufficient_data',
                            'groups_tested' => null,
                            'metadata' => [
                                'groups_with_data' => array_keys($groupedData),
                                'groups_insufficient_data' => $insufficientGroups,
                                'groups_without_data' => $groupsWithoutData,
                                'reason' => 'Fewer than 2 groups have sufficient data (n >= 3)',
                            ],
                            'p_value' => null,
                            'test_statistic' => null,
                        ]
                    );
                    $unprocessedMeasurements[] = $measurementId;
                }
            }

            if (empty($pythonInput['measurements'])) {
                return []; // no measurements to process
            }

            $inputJson = json_encode($pythonInput);

            // find script
            $scriptPath = base_path('scripts/unified_stats.py');
            if (! file_exists($scriptPath)) {
                Log::channel('stats')->warning('Python script not found', ['path' => $scriptPath]);

                return [];
            }

            $pythonBinary = config('python.binary');
            if (! is_string($pythonBinary) || $pythonBinary === '') {
                Log::channel('stats')->warning('Failed to start Python process for batch');

                return [];
            }

            $process = new Process([$pythonBinary, $scriptPath]);
            $process->setInput($inputJson);
            $process->setTimeout(null);

            try {
                $process->run();
            } catch (\Throwable $e) {
                Log::channel('stats')->warning('Failed to start Python process for batch', [
                    'error' => $e->getMessage(),
                ]);

                return [];
            }

            $output = $process->getOutput();
            $errors = $process->getErrorOutput();
            $returnCode = $process->getExitCode();

            if ($returnCode !== 0) {
                Log::channel('stats')->warning('Python batch script failed', [
                    'return_code' => $returnCode,
                    'stderr' => $errors,
                ]);

                return [];
            }

            // parse json output
            $batchResult = json_decode($output, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::channel('stats')->warning('Failed to parse Python batch output', [
                    'error' => json_last_error_msg(),
                    'output' => substr($output, 0, 500),
                ]);

                return [];
            }

            if (! isset($batchResult['results']) || ! is_array($batchResult['results'])) {
                Log::channel('stats')->warning('Python batch output missing results array');

                return [];
            }

            // process each result and save to db
            $processedCount = 0;
            foreach ($batchResult['results'] as $result) {
                $measurementId = $result['measurement_id'] ?? null;
                if (! $measurementId || ! isset($measurementMeta[$measurementId])) {
                    continue;
                }

                if (isset($result['error'])) {
                    Log::channel('stats')->warning('Python returned error for measurement', [
                        'measurement_id' => $measurementId,
                        'error' => $result['error'],
                    ]);

                    continue;
                }

                $preMeta = $measurementMeta[$measurementId];
                $groupIds = array_keys($preMeta['grouped_data']);

                // base metadata for all tests - group info
                $baseMetadata = [
                    'groups_with_data' => $groupIds,
                    'groups_insufficient_data' => $preMeta['insufficient_groups'],
                    'groups_without_data' => $preMeta['groups_without_data'],
                ];

                // base record attributes for all tests
                $baseRecord = [
                    'statistics_job_id' => $statisticsJob->id,
                    'comparison_group_id' => $comparisonGroup->id,
                    'analysis_type_id' => $context['analysis_type_id'],
                    'specimen_type_id' => $context['specimen_type_id'],
                    'analyte_category_id' => $context['analyte_category_id'],
                    'region_id' => $context['region_id'],
                    'test_method' => $context['test_method'],
                    'bone_tested' => $context['bone_tested'],
                    'measurement_id' => $measurementId,
                ];

                // 1. Shapiro-Wilk tests (all in metadata, summary in main fields)
                if (isset($result['shapiro_wilk']) && is_array($result['shapiro_wilk'])) {
                    // worst p-val and statistic as rep values
                    $swWorst = collect($result['shapiro_wilk'])->sortBy('p_value')->first();

                    InferentialStatistic::updateOrCreate(
                        array_merge($baseRecord, ['test_type' => 'shapiro_wilk']),
                        [
                            'p_value' => $swWorst['p_value'] ?? null,
                            'test_statistic' => $swWorst['statistic'] ?? null,
                            'metadata' => array_merge($baseMetadata, [
                                'results_by_group' => $result['shapiro_wilk'],
                                'interpretation' => 'Normality test per group. Low p-values indicate non-normal distribution.',
                            ]),
                            'status' => 'completed',
                        ]
                    );
                }

                // 2. Levene's test
                if (isset($result['levene'])) {
                    InferentialStatistic::updateOrCreate(
                        array_merge($baseRecord, ['test_type' => 'levene']),
                        [
                            'p_value' => $result['levene']['p_value'] ?? null,
                            'test_statistic' => $result['levene']['statistic'] ?? null,
                            'metadata' => array_merge($baseMetadata, [
                                'equal_variance' => $result['levene']['equal_variance'] ?? null,
                                'interpretation' => 'Tests homogeneity of variances. p < 0.05 suggests unequal variances.',
                            ]),
                            'status' => 'completed',
                        ]
                    );
                }

                // 3. main test (t-test or ANOVA)
                $mainTestMetadata = $baseMetadata;
                if ($result['test_type'] === 't_test') {
                    $mainTestMetadata['variant'] = $result['test_variant'] ?? 'unknown';
                    $mainTestMetadata['interpretation'] = 'Compares means between two groups.';
                    if (isset($result['t_test_metadata'])) {
                        $mainTestMetadata = array_merge($mainTestMetadata, $result['t_test_metadata']);
                    }
                } elseif ($result['test_type'] === 'anova') {
                    $mainTestMetadata['interpretation'] = 'Compares means among multiple groups.';
                    if (isset($result['anova_metadata'])) {
                        $mainTestMetadata = array_merge($mainTestMetadata, $result['anova_metadata']);
                    }
                }

                InferentialStatistic::updateOrCreate(
                    array_merge($baseRecord, ['test_type' => $result['test_type']]),
                    [
                        'p_value' => $result['p_value'],
                        'test_statistic' => $result['test_statistic'],
                        'metadata' => $mainTestMetadata,
                        'status' => 'completed',
                    ]
                );

                // 4. Tukey HSD comparisons (all in metadata, most significant in main fields)
                if (isset($result['tukey_hsd']['comparisons']) && is_array($result['tukey_hsd']['comparisons'])) {
                    // most significant comparison as representative values
                    $mostSignificant = collect($result['tukey_hsd']['comparisons'])->sortBy('p_value')->first();

                    // format display
                    $displayString = $this->formatTukeyDisplay(
                        $result['tukey_hsd']['comparisons'],
                        $comparisonGroup,
                        $measurementId,
                        $context
                    );

                    InferentialStatistic::updateOrCreate(
                        array_merge($baseRecord, ['test_type' => 'tukey_hsd']),
                        [
                            'p_value' => $mostSignificant['p_value'] ?? null,
                            'test_statistic' => $mostSignificant['statistic'] ?? null,
                            'metadata' => array_merge($baseMetadata, [
                                'confidence_level' => $result['tukey_hsd']['confidence_level'] ?? null,
                                'comparisons' => $result['tukey_hsd']['comparisons'],
                                'interpretation' => 'Post-hoc pairwise comparisons following significant ANOVA.',
                                'most_significant' => $mostSignificant,
                                'display_string' => $displayString,
                            ]),
                            'status' => 'completed',
                        ]
                    );
                }

                $processedCount++;
            }

            Log::channel('stats')->info('Batch Python stats completed', [
                'measurements_processed' => $processedCount,
                'measurements_submitted' => count($pythonInput['measurements']),
            ]);

            return [
                'processed_measurements' => array_keys($measurementMeta),
                'unprocessed_measurements' => $unprocessedMeasurements,
                'stats_created' => array_unique(array_merge(array_keys($measurementMeta), $unprocessedMeasurements)),
            ];

        } catch (\Exception $e) {
            Log::channel('stats')->warning('Batch Python stats execution failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }

    // format tukey results into display (A > B ~ control)
    protected function formatTukeyDisplay(
        array $comparisons,
        ComparisonGroup $comparisonGroup,
        int $measurementId,
        array $context
    ): string {
        try {
            $sampleGroupIds = $comparisonGroup->sample_group_ids ?? [];

            // query means from descriptive statistics
            $descriptiveStats = DescriptiveStatistic::whereIn('sample_group_id', $sampleGroupIds)
                ->where('analysis_type_id', $context['analysis_type_id'])
                ->where('specimen_type_id', $context['specimen_type_id'])
                ->where('analyte_category_id', $context['analyte_category_id'])
                ->where('region_id', $context['region_id'])
                ->where('test_method', $context['test_method'])
                ->where('bone_tested', $context['bone_tested'])
                ->where('measurement_id', $measurementId)
                ->with('sampleGroup')
                ->get()
                ->keyBy('sample_group_id');

            $groupMeans = [];
            foreach ($descriptiveStats as $groupId => $stat) {
                if ($stat->n > 0 && $stat->sampleGroup) {
                    $groupMeans[$groupId] = [
                        'label' => $stat->sampleGroup->label,
                        'mean' => $stat->mean,
                    ];
                }
            }

            if (empty($groupMeans)) {
                return 'ns';
            }

            // get significant comparisons (p < 0.05)
            $significantPairs = collect($comparisons)->filter(function ($comp) {
                return isset($comp['p_value']) && $comp['p_value'] < 0.05;
            });

            if ($significantPairs->isEmpty()) {
                return 'ns';
            }

            // sort groups by mean (desc)
            uasort($groupMeans, function ($a, $b) {
                return $b['mean'] <=> $a['mean'];
            });

            // build ordered list with separators
            $orderedGroups = array_keys($groupMeans);
            $displayParts = [];

            foreach ($orderedGroups as $idx => $groupId) {
                $displayParts[] = $groupMeans[$groupId]['label'];

                // check for significant difference with next group
                if ($idx < count($orderedGroups) - 1) {
                    $nextGroupId = $orderedGroups[$idx + 1];

                    // check for significant difference
                    $hasSigDiff = $significantPairs->contains(function ($comp) use ($groupId, $nextGroupId) {
                        return ($comp['group1_id'] == $groupId && $comp['group2_id'] == $nextGroupId) ||
                               ($comp['group1_id'] == $nextGroupId && $comp['group2_id'] == $groupId);
                    });

                    $displayParts[] = $hasSigDiff ? ' > ' : ' ≈ ';
                }
            }

            return implode('', $displayParts);

        } catch (\Exception $e) {
            Log::channel('stats')->warning('Failed to format Tukey display', [
                'measurement_id' => $measurementId,
                'error' => $e->getMessage(),
            ]);

            return 'Error';
        }
    }
}
