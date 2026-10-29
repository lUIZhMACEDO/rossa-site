<?php

namespace App\Observers;

use App\Models\AnalysisUpload;
use App\Models\ComparisonGroup;
use App\Services\StatisticalAnalysisService;

class ComparisonGroupObserver
{
    public function created(ComparisonGroup $comparisonGroup): void
    {
        \Log::channel('stats')->info('CGO: created', ['comparison_group_id' => $comparisonGroup->id]);
        $this->triggerInferentialStatistics($comparisonGroup);
    }

    public function updated(ComparisonGroup $comparisonGroup): void
    {
        \Log::channel('stats')->info('CGO: updated', ['comparison_group_id' => $comparisonGroup->id]);
        // only trigger stats if the ids are different
        if ($comparisonGroup->isDirty('sample_group_ids')) {
            $old = $comparisonGroup->getOriginal('sample_group_ids') ?? [];
            $new = $comparisonGroup->sample_group_ids ?? [];

            $added = array_diff($new, $old);
            $removed = array_diff($old, $new);

            if (! empty($added) || ! empty($removed)) {
                \Log::channel('stats')->info('CGO: sample groups changed', [
                    'comparison_group_id' => $comparisonGroup->id,
                    'added' => array_values($added),
                    'removed' => array_values($removed),
                    'old' => $old,
                    'new' => $new,
                ]);
                $this->triggerInferentialStatistics($comparisonGroup);
            } else {
                \Log::channel('stats')->info('CGO: order changed, skipping', [
                    'comparison_group_id' => $comparisonGroup->id,
                ]);
            }
        }
    }

    protected function triggerInferentialStatistics(ComparisonGroup $comparisonGroup): void
    {
        // get sample groups from the comparison group
        $sampleGroupIds = $comparisonGroup->sample_group_ids ?? [];

        if (empty($sampleGroupIds)) {
            return;
        }

        // get uploads with samples in this comparison group
        $uploads = AnalysisUpload::where('study_id', $comparisonGroup->study_id)
            ->whereHas('samples', function ($q) use ($sampleGroupIds) {
                $q->whereIn('sample_group_id', $sampleGroupIds);
            })
            ->get();

        // get unique contexts from uploads
        $contextsByKey = [];
        foreach ($uploads as $upload) {
            $context = $upload->getContext();
            $key = serialize($context);
            $contextsByKey[$key] = $context; // overwrites duplicates
        }
        $contexts = array_values($contextsByKey);

        \Log::channel('stats')->info('ComparisonGroupObserver: triggering inferential statistics', [
            'count' => count($contexts),
            'comparison_group_id' => $comparisonGroup->id,
            'contexts' => $contexts,
        ]);

        // trigger inferential statistics for each context
        foreach ($contexts as $context) {
            StatisticalAnalysisService::triggerStatisticsJob(null, $comparisonGroup->id, $context);
        }
    }
}
