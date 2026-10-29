<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DescriptiveStatistic extends Model
{
    protected $fillable = [
        'statistics_job_id',
        'analysis_type_id',
        'specimen_type_id',
        'analyte_category_id',
        'region_id',
        'test_method',
        'bone_tested',
        'measurement_id',
        'sample_group_id',
        'n',
        'mean',
        'median',
        'sd',
        'variance',
        'sem',
        'cv',
        'min',
        'max',
        'range',
        'q1',
        'q3',
        'iqr',
        'outliers_2sd',
        'outliers_3sd',
    ];

    protected $casts = [
        'n' => 'integer',
        'mean' => 'decimal:4',
        'median' => 'decimal:4',
        'sd' => 'decimal:4',
        'variance' => 'decimal:4',
        'sem' => 'decimal:4',
        'cv' => 'decimal:4',
        'min' => 'decimal:4',
        'max' => 'decimal:4',
        'range' => 'decimal:4',
        'q1' => 'decimal:4',
        'q3' => 'decimal:4',
        'iqr' => 'decimal:4',
        'outliers_2sd' => 'array',
        'outliers_3sd' => 'array',
    ];

    // job that created this statistic
    public function statisticsJob(): BelongsTo
    {
        return $this->belongsTo(StatisticsJob::class);
    }

    // measurement this statistic is for
    public function measurement(): BelongsTo
    {
        return $this->belongsTo(Measurement::class);
    }

    // sample group this statistic is for
    public function sampleGroup(): BelongsTo
    {
        return $this->belongsTo(SampleGroup::class);
    }

    // context relationships
    public function analysisType(): BelongsTo
    {
        return $this->belongsTo(AnalysisType::class);
    }

    public function specimenType(): BelongsTo
    {
        return $this->belongsTo(SpecimenType::class);
    }

    public function analyteCategory(): BelongsTo
    {
        return $this->belongsTo(AnalyteCategory::class);
    }

    // scope to filter by measurement
    public function scopeForMeasurement($query, $measurementId)
    {
        return $query->where('measurement_id', $measurementId);
    }

    // scope to filter by sample group
    public function scopeForGroup($query, $groupId)
    {
        return $query->where('sample_group_id', $groupId);
    }

    // scope to filter by context
    public function scopeForContext($query, array $context)
    {
        return $query
            ->where('analysis_type_id', $context['analysis_type_id'])
            ->where('specimen_type_id', $context['specimen_type_id'])
            ->where('analyte_category_id', $context['analyte_category_id'])
            ->where('region_id', $context['region_id'])
            ->where('test_method', $context['test_method'])
            ->where('bone_tested', $context['bone_tested']);
    }

    // helper methods
    public function getContext(): array
    {
        return [
            'analysis_type_id' => $this->analysis_type_id,
            'specimen_type_id' => $this->specimen_type_id,
            'analyte_category_id' => $this->analyte_category_id,
            'region_id' => $this->region_id,
            'test_method' => $this->test_method,
            'bone_tested' => $this->bone_tested,
        ];
    }

    // formatted mean ± SD string
    public function getMeanSdAttribute(): string
    {
        if ($this->mean === null || $this->sd === null) {
            return 'N/A';
        }

        return number_format($this->mean, 2).' ± '.number_format($this->sd, 2);
    }

    // formatted mean ± SEM string
    public function getMeanSemAttribute(): string
    {
        if ($this->mean === null || $this->sem === null) {
            return 'N/A';
        }

        return number_format($this->mean, 2).' ± '.number_format($this->sem, 2);
    }
}
