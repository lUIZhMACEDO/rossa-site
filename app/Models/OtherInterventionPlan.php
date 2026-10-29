<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OtherInterventionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'study_id',
        'plan_name',
        'description',
        'animal_age_at_day_zero',
        'is_control_plan',
        'control_for_plan_id',
    ];

    protected $casts = [
        'is_control_plan' => 'boolean',
    ];

    public function study(): BelongsTo
    {
        return $this->belongsTo(Study::class);
    }

    /**
     * @return HasMany<OtherInterventionEvent, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(OtherInterventionEvent::class, 'plan_id')->orderBy('event_order');
    }

    /**
     * The plan this one is the control for, if any (mirrors
     * DrugAdministrationPlan::controlForPlan()).
     */
    public function controlForPlan(): BelongsTo
    {
        return $this->belongsTo(self::class, 'control_for_plan_id');
    }

    /**
     * Plans that name this plan as their control (inverse of controlForPlan()).
     */
    public function controlPlans(): HasMany
    {
        return $this->hasMany(self::class, 'control_for_plan_id');
    }

    public function sampleGroups(): HasMany
    {
        return $this->hasMany(SampleGroup::class, 'other_intervention_plan_id');
    }

    /**
     * Latest week of age across all events, for the Plans list card's
     * "End at ~X weeks old" line. Null if no event has an end_day.
     */
    public function getEndAgeAttribute(): ?int
    {
        $maxEndDay = $this->events->max(fn ($event) => $event->end_day ?? $event->start_day);

        if ($maxEndDay === null) {
            return null;
        }

        return $this->animal_age_at_day_zero + (int) ceil($maxEndDay / 7);
    }

    /**
     * Multi-line preview of every event's timeline string, for the Sample
     * Group form's "Selected Plan" box, matching
     * DrugAdministrationPlan::getTimelinePreview()'s exact usage pattern
     * (nl2br(e(...))) in fields.blade.php.
     */
    public function getTimelinePreview(): string
    {
        return $this->events
            ->map(fn ($event) => $event->getTimelineString($this->animal_age_at_day_zero))
            ->implode("\n");
    }
}
