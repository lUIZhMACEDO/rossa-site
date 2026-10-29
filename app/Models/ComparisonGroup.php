<?php

namespace App\Models;

use App\Traits\LocksWithStudy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComparisonGroup extends Model
{
    use LocksWithStudy;

    protected $fillable = [
        'study_id',
        'name',
        'description',
        'sample_group_ids',
        'measurement_overlap_data',
    ];

    protected $casts = [
        'sample_group_ids' => 'array',
        'measurement_overlap_data' => 'array',
    ];

    public function study(): BelongsTo
    {
        return $this->belongsTo(Study::class);
    }

    // collection of sample groups in correct order
    public function sampleGroups()
    {
        if (! $this->sample_group_ids || ! is_array($this->sample_group_ids)) {
            return collect();
        }

        $groups = SampleGroup::with([
            'animalModel.geneticCategory',
            'animalModel.geneticMethod',
            'animalModel.animalLine',
            'animalModel.parents.animalLine',
        ])
            ->whereIn('id', $this->sample_group_ids)
            ->get();

        // sort by the order in array
        return collect($this->sample_group_ids)
            ->map(function ($id) use ($groups) {
                return $groups->firstWhere('id', $id);
            })
            ->filter(); // remove null
    }

    // statistics relationships
    public function inferentialStatistics()
    {
        return $this->hasMany(InferentialStatistic::class);
    }

    public function statisticsJobs()
    {
        return $this->hasMany(StatisticsJob::class)->whereNotNull('comparison_group_id');
    }

    public function latestStatisticsJob()
    {
        return $this->hasOne(StatisticsJob::class)->whereNotNull('comparison_group_id')->latest();
    }
}
