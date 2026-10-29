<?php

namespace App\Jobs;

use App\Enums\UploadSteps;
use App\Models\AnalysisUpload;
use App\Models\DescriptiveStatistic;
use App\Models\Sample;
use App\Models\SampleGroup;
use App\Models\StatisticsJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use MathPHP\Statistics\Average;
use MathPHP\Statistics\Descriptive;

class ProcessDescriptiveStatistics implements ShouldQueue
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
                Log::channel('stats')->info('Aborting stale descriptive statistics job - newer version dispatched', [
                    'job_id' => $this->statisticsJob->id,
                    'expected_version' => $this->expectedVersion,
                    'current_version' => $this->statisticsJob->processing_version,
                ]);

                return; // abort immediately
            }

            // mark as processing
            $this->statisticsJob->update(['status' => 'processing']);

            $sampleGroup = $this->statisticsJob->sampleGroup;
            $context = $this->statisticsJob->getContext();

            // query uploads first (filter by study + context), then get samples (only complete uploads)
            $uploadIds = AnalysisUpload::where('study_id', $sampleGroup->study_id)
                ->where('analysis_type_id', $context['analysis_type_id'])
                ->where('specimen_type_id', $context['specimen_type_id'])
                ->where('analyte_category_id', $context['analyte_category_id'])
                ->where('region_id', $context['region_id'])
                ->where('test_method', $context['test_method'])
                ->where('bone_tested', $context['bone_tested'])
                ->where('current_step', UploadSteps::COMPLETE)
                ->pluck('id');

            // if there are no uploads for this context, remove all descriptive statistics for this sample group, context
            if ($uploadIds->isEmpty()) {
                \Log::channel('stats')->info('No uploads found matching this context, deleting all descriptive statistics for this sample group, context', [
                    'sample_group_id' => $sampleGroup->id,
                    'context' => $context,
                ]);
                $this->removeOldStats($sampleGroup, $context);

                return;
            }

            // now query samples from those few uploads
            $samples = Sample::whereIn('analysis_upload_id', $uploadIds)
                ->where('sample_group_id', $sampleGroup->id)
                ->with('measurementValues.measurement')
                ->get();

            if ($samples->isEmpty()) {
                \Log::channel('stats')->info('No samples found matching this context in this group, removing all descriptive statistics for this sample group, context', [
                    'sample_group_id' => $sampleGroup->id,
                    'context' => $context,
                ]);
                $this->removeOldStats($sampleGroup, $context);

                return;
            }

            // build structure: [measurement_id => ['measurement' => Model, 'values' => [floats]], ...]
            $measurementData = [];

            foreach ($samples as $sample) {
                foreach ($sample->measurementValues as $mv) {
                    if (! is_numeric($mv->value)) {
                        continue;
                    }
                    $measurementId = $mv->measurement_id;
                    if (! isset($measurementData[$measurementId])) {
                        $measurementData[$measurementId] = [
                            'measurement' => $mv->measurement,
                            'values' => [],
                        ];
                    }
                    $measurementData[$measurementId]['values'][] = (float) $mv->value;
                }
            }

            // deleting stats for measurements that are no longer present
            DescriptiveStatistic::where('sample_group_id', $sampleGroup->id)
                ->where('analysis_type_id', $context['analysis_type_id'])
                ->where('specimen_type_id', $context['specimen_type_id'])
                ->where('analyte_category_id', $context['analyte_category_id'])
                ->where('region_id', $context['region_id'])
                ->where('test_method', $context['test_method'])
                ->where('bone_tested', $context['bone_tested'])
                ->whereNotIn('measurement_id', array_keys($measurementData))
                ->delete();

            if (empty($measurementData)) {
                \Log::channel('stats')->info('No measurements found for this context, removing all descriptive statistics for this sample group, context', [
                    'sample_group_id' => $sampleGroup->id,
                    'context' => $context,
                    'measurement_data' => $measurementData,
                ]);
                $this->removeOldStats($sampleGroup, $context);

                return;
            }

            // calculate descriptive stats for each measurement
            $statsCreated = 0;
            foreach ($measurementData as $measurementId => $data) {
                $this->calculateDescriptiveStatsForMeasurement(
                    $this->statisticsJob,
                    $sampleGroup,
                    $data['measurement'],
                    $data['values'],
                    $context
                );
                $statsCreated++;
            }

            // only mark completed if this is the latest version
            $this->statisticsJob->refresh();
            if ($this->statisticsJob->processing_version === $this->expectedVersion) {
                $this->statisticsJob->update([
                    'status' => 'completed',
                    'measurements_count' => $statsCreated,
                    'completed_at' => now(),
                ]);

                Log::channel('stats')->info('Descriptive statistics completed', [
                    'job_id' => $this->statisticsJob->id,
                    'sample_group_id' => $sampleGroup->id,
                    'context' => $context,
                    'measurements' => $statsCreated,
                ]);
            } else {
                // newer version was dispatched, do not mark as completed
                Log::channel('stats')->info('Descriptive statistics finished but not marking as completed - newer version pending', [
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

            Log::channel('stats')->error('Descriptive statistics job failed', [
                'job_id' => $this->statisticsJob->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    protected function removeOldStats(SampleGroup $group, array $context): void
    {
        DescriptiveStatistic::where('sample_group_id', $group->id)
            ->where('analysis_type_id', $context['analysis_type_id'])
            ->where('specimen_type_id', $context['specimen_type_id'])
            ->where('analyte_category_id', $context['analyte_category_id'])
            ->where('region_id', $context['region_id'])
            ->where('test_method', $context['test_method'])
            ->where('bone_tested', $context['bone_tested'])
            ->delete();

        // only mark completed if this is the latest version
        $this->statisticsJob->refresh();
        if ($this->statisticsJob->processing_version === $this->expectedVersion) {
            $this->statisticsJob->update([
                'status' => 'completed',
                'measurements_count' => 0,
                'completed_at' => now(),
                'error_message' => 'Removed old descriptive statistics for this context',
            ]);
        }
    }

    protected function calculateDescriptiveStatsForMeasurement(
        StatisticsJob $job,
        SampleGroup $group,
        $measurement,
        array $values,
        array $context
    ): void {
        // need at least 1 value
        if (count($values) < 1) {
            return;
        }

        $n = count($values);

        // calculate all descriptive statistics
        $statsKey = [
            'sample_group_id' => $group->id,
            'analysis_type_id' => $context['analysis_type_id'],
            'specimen_type_id' => $context['specimen_type_id'],
            'analyte_category_id' => $context['analyte_category_id'],
            'region_id' => $context['region_id'],
            'test_method' => $context['test_method'],
            'bone_tested' => $context['bone_tested'],
            'measurement_id' => $measurement->id,
        ];

        $stats = [
            'statistics_job_id' => $job->id,
            'n' => $n,
        ];

        try {
            // basic statistics
            $stats['mean'] = Average::mean($values);
            $stats['median'] = Average::median($values);
            $stats['min'] = min($values);
            $stats['max'] = max($values);
            $stats['range'] = $stats['max'] - $stats['min'];

            // variance, sd, sem, cv
            if ($n > 1) {
                // true = sample, false = population
                $stats['variance'] = Descriptive::variance($values, true);
                $stats['sd'] = Descriptive::standardDeviation($values, true);
                $stats['sem'] = $stats['sd'] / sqrt($n);

                // coefficient of variation (%)
                if ($stats['mean'] != 0) {
                    $stats['cv'] = ($stats['sd'] / $stats['mean']) * 100;
                }
            }

            // quartiles, iqr
            $quartiles = Descriptive::quartiles($values);
            $stats['q1'] = $quartiles['Q1'];
            $stats['q3'] = $quartiles['Q3'];
            $stats['iqr'] = $stats['q3'] - $stats['q1'];

            // outlier detection (2 sd, 3 sd)
            if (isset($stats['sd']) && $stats['sd'] > 0) {
                $outliers2sd = [];
                $outliers3sd = [];

                foreach ($values as $value) {
                    $zScore = abs(($value - $stats['mean']) / $stats['sd']);
                    if ($zScore > 2) {
                        $outliers2sd[] = [
                            'value' => $value,
                            'z_score' => round($zScore, 2),
                        ];
                    }
                    if ($zScore > 3) {
                        $outliers3sd[] = [
                            'value' => $value,
                            'z_score' => round($zScore, 2),
                        ];
                    }
                }

                $stats['outliers_2sd'] = ! empty($outliers2sd) ? $outliers2sd : null;
                $stats['outliers_3sd'] = ! empty($outliers3sd) ? $outliers3sd : null;
            }

        } catch (\Exception $e) {
            Log::channel('stats')->warning('Failed to calculate some descriptive stats', [
                'measurement' => $measurement->id,
                'group' => $group->id,
                'error' => $e->getMessage(),
            ]);
        }

        DescriptiveStatistic::updateOrCreate($statsKey, $stats);
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('stats')->error('Descriptive statistics job failed', [
            'job_id' => $this->statisticsJob->id,
            'error' => $exception->getMessage(),
        ]);

        $this->statisticsJob->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
        ]);
    }
}
