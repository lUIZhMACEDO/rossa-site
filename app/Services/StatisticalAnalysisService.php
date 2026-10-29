<?php

namespace App\Services;

use App\Jobs\ProcessDescriptiveStatistics;
use App\Jobs\ProcessInferentialStatistics;
use App\Models\StatisticsJob;

class StatisticalAnalysisService
{
    public static function triggerStatisticsJob(?int $sampleGroupId, ?int $comparisonGroupId, array $context): void
    {
        $job = StatisticsJob::firstOrCreate(
            [
                'sample_group_id' => $sampleGroupId,
                'comparison_group_id' => $comparisonGroupId,
                'analysis_type_id' => $context['analysis_type_id'],
                'specimen_type_id' => $context['specimen_type_id'],
                'analyte_category_id' => $context['analyte_category_id'],
                'region_id' => $context['region_id'],
                'test_method' => $context['test_method'],
                'bone_tested' => $context['bone_tested'],
            ],
            [
                'status' => 'pending',
                'processing_version' => 0,
            ]
        );

        // increment version to ensure fresh calculation
        $job->increment('processing_version', 1, [
            'status' => 'pending',
            'completed_at' => null,
            'error_message' => null,
        ]);
        $job->refresh();

        // dispatch job
        // processing_version ensures stale queued jobs abort immediately
        if ($sampleGroupId) {
            ProcessDescriptiveStatistics::dispatch($job);
        } elseif ($comparisonGroupId) {
            ProcessInferentialStatistics::dispatch($job);
        }
    }
}
