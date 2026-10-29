<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtherInterventionEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_id',
        'intervention_category_id',
        'intervention_specific_type_id',
        'category_other',
        'specific_type_other',
        'name',
        'method_description',
        'source_details',
        'frequency',
        'frequency_unit',
        'frequency_unit_other',
        'start_day',
        'end_day',
        'event_order',
    ];

    protected $casts = [
        'frequency' => 'decimal:2',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(OtherInterventionPlan::class, 'plan_id');
    }

    /**
     * @return BelongsTo<InterventionCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(InterventionCategory::class, 'intervention_category_id');
    }

    /**
     * @return BelongsTo<InterventionSpecificType, $this>
     */
    public function specificType(): BelongsTo
    {
        return $this->belongsTo(InterventionSpecificType::class, 'intervention_specific_type_id');
    }

    /**
     * Resolved category label: the lookup row's name, or category_other when
     * the category itself is "Other" (same resolution pattern as vehicle_id/vehicle_other).
     */
    public function getCategoryLabelAttribute(): ?string
    {
        return $this->category_other ?: $this->category?->name;
    }

    /**
     * Resolved specific type label: the lookup row's name, or specific_type_other
     * when the selected type is that category's "Other" row.
     */
    public function getSpecificTypeLabelAttribute(): ?string
    {
        return $this->specific_type_other ?: $this->specificType?->name;
    }

    public function getFrequencyUnitLabelAttribute(): ?string
    {
        return $this->frequency_unit === 'other'
            ? $this->frequency_unit_other
            : $this->frequency_unit;
    }

    /**
     * Human-readable one-line summary for the Plans list' Timeline Preview,
     * mirroring DrugAdministrationEvent::getTimelineString()'s day/age
     * formatting, adapted to our fields (no dose/route/molecule here).
     */
    public function getTimelineString(int $animalAgeAtDayZero): string
    {
        $startAge = $animalAgeAtDayZero + intdiv($this->start_day, 7);

        if ($this->end_day === null || $this->end_day == $this->start_day) {
            $dayRange = "Day {$this->start_day}";
            $ageRange = "{$startAge} wks";
        } else {
            $endAge = $animalAgeAtDayZero + (int) ceil($this->end_day / 7);
            $dayRange = "Day {$this->start_day}-{$this->end_day}";
            $ageRange = "{$startAge}-{$endAge} wks";
        }

        $frequencyLabel = match ($this->frequency_unit) {
            'single_dose' => 'single dose',
            'per_day' => $this->frequency == 1 ? 'daily' : "{$this->frequency}x/day",
            'per_week' => "{$this->frequency}x/week",
            'continuous' => 'continuous',
            'other' => $this->frequency_unit_other,
            default => $this->frequency_unit,
        };

        return "{$dayRange} ({$ageRange}): {$this->name}, {$frequencyLabel}";
    }
}
