<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InferentialStatistic extends Model
{
    protected $fillable = [
        'statistics_job_id',
        'comparison_group_id',
        'analysis_type_id',
        'specimen_type_id',
        'analyte_category_id',
        'region_id',
        'test_method',
        'bone_tested',
        'measurement_id',
        'test_type',
        'p_value',
        'test_statistic',
        'metadata',
        'groups_tested',
        'status',
    ];

    protected $casts = [
        'p_value' => 'decimal:6',
        'test_statistic' => 'decimal:4',
        'metadata' => 'array',
        'groups_tested' => 'array',
    ];

    // job that created this statistic
    public function statisticsJob(): BelongsTo
    {
        return $this->belongsTo(StatisticsJob::class);
    }

    // comparison group this statistic is for
    public function comparisonGroup(): BelongsTo
    {
        return $this->belongsTo(ComparisonGroup::class);
    }

    /**
     * Measurement this statistic is for.
     *
     * @return BelongsTo<Measurement, $this>
     */
    public function measurement(): BelongsTo
    {
        return $this->belongsTo(Measurement::class);
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

    // scope to filter by test type
    public function scopeByTestType($query, $testType)
    {
        return $query->where('test_type', $testType);
    }

    // scope to only completed tests
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // scope to only pending tests
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // scope to filter by context
    public function scopeForContext($query, array $context)
    {
        return $query
            ->where('analysis_type_id', $context['analysis_type_id'])
            ->where('specimen_type_id', $context['specimen_type_id'])
            ->where('region_id', $context['region_id'])
            ->where('analyte_category_id', $context['analyte_category_id'])
            ->where('test_method', $context['test_method'])
            ->where('bone_tested', $context['bone_tested']);
    }

    // helper methods
    public function getContext(): array
    {
        return [
            'analysis_type_id' => $this->analysis_type_id,
            'specimen_type_id' => $this->specimen_type_id,
            'region_id' => $this->region_id,
            'analyte_category_id' => $this->analyte_category_id,
            'test_method' => $this->test_method,
            'bone_tested' => $this->bone_tested,
        ];
    }

    // check if result is statistically significant at given alpha level
    public function isSignificant(float $alpha = 0.05): bool
    {
        if ($this->p_value === null) {
            return false;
        }

        return (float) $this->p_value < $alpha;
    }

    // formatted p-value string
    public function getFormattedPValueAttribute(): string
    {
        if ($this->p_value === null) {
            return 'N/A';
        }

        $pValue = (float) $this->p_value;

        if ($pValue < 0.001) {
            return '<0.001';
        }

        return number_format($pValue, 3);
    }

    // significance level indicator
    public function getSignificanceLevelAttribute(): string
    {
        if ($this->p_value === null) {
            return 'N/A';
        }

        $pValue = (float) $this->p_value;

        if ($pValue < 0.001) {
            return '***';
        } elseif ($pValue < 0.01) {
            return '**';
        } elseif ($pValue < 0.05) {
            return '*';
        }

        return 'ns';
    }
}
