<?php

namespace App\Observers;

use App\Enums\UploadSteps;
use App\Models\AnalysisUpload;
use App\Models\ComparisonGroup;
use App\Models\SampleGroup;

class SampleGroupObserver
{
    public function deleted(SampleGroup $sampleGroup): void
    {
        \Log::channel('stats')->info('SGO: deleted', ['sample_group_id' => $sampleGroup->id]);

        // handle impacts on uploads
        $impactedUploads = AnalysisUpload::where('study_id', $sampleGroup->study_id)
            ->whereIn('current_step', [
                UploadSteps::COMPLETE,
                UploadSteps::SCAN_DATASETS,
                UploadSteps::REVIEW,
                UploadSteps::GROUP_SAMPLES,
            ])
            ->whereJsonContains('sample_group_ids', (string) $sampleGroup->id)
            ->get();

        \Log::channel('stats')->info('SGO: impacted uploads', ['count' => $impactedUploads->count(), 'upload_ids' => $impactedUploads->pluck('id')]);
        foreach ($impactedUploads as $upload) {
            // remove the sample group from array
            $upload->sample_group_ids = array_values(
                array_diff($upload->sample_group_ids ?? [], [(string) $sampleGroup->id])
            );

            // delete samples if upload has persisted samples
            if (in_array($upload->current_step, [UploadSteps::COMPLETE, UploadSteps::SCAN_DATASETS], true)) {
                $upload->samples()->where('sample_group_id', $sampleGroup->id)->delete();
            }

            // filter out sample group entries from group cache
            if ($upload->group_cache !== null) {
                $cache = collect($upload->group_cache)
                    ->map(function ($sampleData) use ($sampleGroup) {
                        // null out the sample group assignment
                        if (($sampleData['sample_group_id'] ?? null) == (string) $sampleGroup->id) {
                            $sampleData['sample_group_id'] = null;
                        }

                        return $sampleData;
                    })
                    ->toArray();

                $upload->group_cache = $cache;
            }

            // set to group samples step
            $upload->current_step = UploadSteps::GROUP_SAMPLES;
            $upload->save();
        }

        // handle impacts on comparison groups
        $impactedComparisonGroups = ComparisonGroup::where('study_id', $sampleGroup->study_id)
            ->whereJsonContains('sample_group_ids', (string) $sampleGroup->id)
            ->get();
        \Log::channel('stats')->info('SGO: impacted comparison groups', ['count' => $impactedComparisonGroups->count(), 'comparison_group_ids' => $impactedComparisonGroups->pluck('id')]);

        foreach ($impactedComparisonGroups as $cg) {
            $newIds = array_values(array_diff($cg->sample_group_ids, [(string) $sampleGroup->id]));

            // delete if less than 2 sample groups
            if (count($newIds) < 2) {
                $cg->delete();
                \Log::channel('stats')->info('SGO: deleted comparison group', ['comparison_group_id' => $cg->id]);

                continue;
            }

            // check for duplicates
            $sortedNew = collect($newIds)->sort()->values()->toArray();
            $isDuplicate = ComparisonGroup::where('study_id', $cg->study_id)
                ->where('id', '!=', $cg->id)
                ->get()
                ->contains(fn ($other) => collect($other->sample_group_ids)->sort()->values()->toArray() === $sortedNew
                );

            if ($isDuplicate) {
                $cg->delete();
                \Log::channel('stats')->info('SGO: deleted duplicate comparison group', ['comparison_group_id' => $cg->id]);
            } else {
                $cg->sample_group_ids = $newIds;
                $cg->save();
            }
        }

        // stats should be handled - descriptive stats will cascade delete
        // inferential stats will be recalculated via adjustments of comparison groups
    }
}
