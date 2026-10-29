<?php

namespace App\Observers;

use App\Enums\UploadSteps;
use App\Models\AnalysisUpload;
use App\Models\ComparisonGroup;
use App\Models\SampleGroup;
use App\Services\StatisticalAnalysisService;

class AnalysisUploadObserver
{
    public function updated(AnalysisUpload $upload): void
    {
        \Log::channel('stats')->info('AUO: updated', [
            'upload_id' => $upload->id,
            'current_step' => $upload->current_step,
            'old_step' => $upload->getOriginal('current_step'),
        ]);

        // does nothing currently
        // $relevantFieldsChanged = $upload->isDirty([
        //     'analysis_type_id',
        //     'specimen_type_id',
        //     'analyte_category_id',
        //     'region_id',
        //     'test_method',
        //     'bone_tested',
        //     'sample_group_ids'
        // ]);

        // trigger for new context/sample groups
        // ignoring if relevant fields changed
        if ($upload->current_step === UploadSteps::COMPLETE) {
            \Log::channel('stats')->info('AUO: upload is complete, triggering stats for new context');
            $this->triggerStatistics($upload);
        }

        // trigger for old context/sample groups - accounts for editing in uploader
        if ($upload->getOriginal('current_step') === UploadSteps::COMPLETE) {
            \Log::channel('stats')->info('AUO: upload was complete, triggering stats for old context');
            $oldContext = [
                'analysis_type_id' => $upload->getOriginal('analysis_type_id'),
                'specimen_type_id' => $upload->getOriginal('specimen_type_id'),
                'analyte_category_id' => $upload->getOriginal('analyte_category_id'),
                'region_id' => $upload->getOriginal('region_id'),
                'test_method' => $upload->getOriginal('test_method'),
                'bone_tested' => $upload->getOriginal('bone_tested'),
            ];
            $oldSampleGroups = $upload->getOriginal('sample_group_ids') ?? [];
            \Log::channel('stats')->info('AUO: old context', [
                'old_context' => $oldContext,
                'old_sample_groups' => $oldSampleGroups,
            ]);

            $this->triggerStatistics($upload, $oldContext, $oldSampleGroups);
        }
    }

    public function deleted(AnalysisUpload $upload): void
    {
        \Log::channel('stats')->info('AUO: deleted', [
            'upload_id' => $upload->id,
            'current_step' => $upload->current_step,
        ]);

        if ($upload->current_step === UploadSteps::COMPLETE) {
            \Log::channel('stats')->info('AUO: deleted complete upload, triggering stats recalculation');
            $this->triggerStatistics($upload);
        } else {
            \Log::channel('stats')->info('AUO: deleted - not complete, skipping');
        }
    }

    // can trigger stats for an upload, context, or list of sample groups
    protected function triggerStatistics(AnalysisUpload $upload, ?array $context = null, ?array $sampleGroupIds = null): void
    {
        // load mechanical testing details for context
        if (! $upload->relationLoaded('mechanicalTestingDetails')) {
            $upload->load('mechanicalTestingDetails');
        }

        // affected context
        $context = $context ?? $upload->getContext();

        // unique affected sample groups
        // can get from col - should only be used on delete and create
        $sampleGroupIds = $sampleGroupIds ?? $upload->sample_group_ids ?? [];

        \Log::channel('stats')->info('AUO: triggering descriptive stats', [
            'upload_id' => $upload->id,
            'count' => count($sampleGroupIds),
            'sample_group_ids' => $sampleGroupIds,
            'context' => $context,
        ]);

        // trigger descriptive stats for each affected sample group
        foreach ($sampleGroupIds as $sampleGroupId) {
            if (SampleGroup::find($sampleGroupId)) {
                StatisticalAnalysisService::triggerStatisticsJob($sampleGroupId, null, $context);
            } else {
                \Log::channel('stats')->info('AUO: sample group not found, likely deleted', ['sample_group_id' => $sampleGroupId]);
            }
        }

        // find comparison groups that contain any of these sample groups
        $comparisonGroupIds = ComparisonGroup::where('study_id', $upload->study_id)
            ->get()
            ->filter(function ($comparisonGroup) use ($sampleGroupIds) {
                $cgSampleGroupIds = $comparisonGroup->sample_group_ids ?? [];

                // check if there's any overlap between the arrays
                return ! empty(array_intersect($sampleGroupIds, $cgSampleGroupIds));
            })->pluck('id')->toArray();

        \Log::channel('stats')->info('AUO: triggering inferential statistics', [
            'upload_id' => $upload->id,
            'count' => count($comparisonGroupIds),
            'comparison_group_ids' => $comparisonGroupIds,
            'context' => $context,
        ]);

        // trigger inferential statistics for each affected comparison group
        foreach ($comparisonGroupIds as $comparisonGroupId) {
            StatisticalAnalysisService::triggerStatisticsJob(null, $comparisonGroupId, $context);
        }
    }
}
