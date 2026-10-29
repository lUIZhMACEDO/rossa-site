<?php

namespace App\Models;

use App\Traits\LocksWithStudy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DrugAdministrationPlan extends Model
{
    use HasFactory, LocksWithStudy;

    protected $fillable = [
        'study_id',
        'plan_name',
        'description',
        'animal_age_at_day_zero',
        'is_vehicle_control',
        'control_for_plan_id',
    ];

    protected $casts = [
        'animal_age_at_day_zero' => 'integer',
        'is_vehicle_control' => 'boolean',
    ];

    /**
     * Get the study that owns this plan
     */
    public function study(): BelongsTo
    {
        return $this->belongsTo(Study::class);
    }

    /**
     * Get all administration events for this plan
     *
     * @return HasMany<DrugAdministrationEvent, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(DrugAdministrationEvent::class, 'plan_id')->orderBy('event_order');
    }

    /**
     * Get sample groups using this plan
     */
    public function sampleGroups(): HasMany
    {
        return $this->hasMany(SampleGroup::class, 'drug_administration_plan_id');
    }

    /**
     * Get a list of all the drug names used in this plan
     */
    public function getDrugNames()
    {
        return $this->events()->with('molecule')
            ->get()
            ->pluck('molecule.pref_name')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Get the auto-generated plan number within the study
     */
    public function getPlanNumberAttribute(): int
    {
        $plans = $this->study->drugAdministrationPlans()
            ->orderBy('id')
            ->pluck('id')
            ->toArray();

        return array_search($this->id, $plans) + 1;
    }

    /**
     * Get the full display name with plan number
     * e.g., "Drug Administration Plan 1: Sclerostin Antibody - High Dose"
     */
    public function getFullDisplayNameAttribute(): string
    {
        return "Drug Administration Plan {$this->plan_number}: {$this->plan_name}";
    }

    /**
     * Get timeline preview as formatted string
     */
    public function getTimelinePreview(): string
    {
        $timeline = [];

        foreach ($this->events as $event) {
            $timeline[] = $event->getTimelineString($this->animal_age_at_day_zero);
        }

        return implode("\n", $timeline);
    }

    /**
     * Calculate the end age of the animal based on the latest event
     */
    public function getEndAgeAttribute(): ?int
    {
        $latestEndDay = $this->events->max('end_day') ?? $this->events->max('start_day');

        if ($latestEndDay === null) {
            return null;
        }

        // Convert days to weeks and add to start age
        return $this->animal_age_at_day_zero + (int) ceil($latestEndDay / 7);
    }

    /**
     * Detect treatment type based on events
     */
    public function getTreatmentTypeAttribute(): string
    {
        $eventCount = $this->events->count();

        if ($eventCount === 0) {
            return 'No events';
        }

        if ($eventCount === 1) {
            return 'Single drug';
        }

        // Check if events overlap (combination) or are sequential
        $events = $this->events->sortBy('start_day');
        $overlapping = false;

        foreach ($events as $index => $event) {
            if ($index === 0) {
                continue;
            }

            $previousEvent = $events[$index - 1];

            // Check if current event starts before previous ends
            if ($event->start_day < ($previousEvent->end_day ?? $previousEvent->start_day)) {
                $overlapping = true;
                break;
            }
        }

        return $overlapping ? 'Combination therapy' : 'Sequential therapy';
    }

    /**
     * The vehicle-control plan generated from this treatment plan.
     */
    public function vehicleControl()
    {
        return $this->hasOne(self::class, 'control_for_plan_id');
    }

    /**
     * If this is a control plan, the treatment plan it controls.
     */
    public function controlForPlan()
    {
        return $this->belongsTo(self::class, 'control_for_plan_id');
    }
}
