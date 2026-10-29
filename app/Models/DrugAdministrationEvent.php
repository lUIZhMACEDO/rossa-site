<?php

namespace App\Models;

use App\Traits\LocksWithStudy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrugAdministrationEvent extends Model
{
    use HasFactory, LocksWithStudy;

    protected $fillable = [
        'plan_id',
        'molecule_id',
        'drug_source_type',
        'drug_source_details',
        'dose',
        'dose_unit',
        'route',
        'route_other',
        'start_day',
        'end_day',
        'frequency',
        'frequency_unit',
        'frequency_unit_other',
        'vehicle_id',
        'vehicle_other',
        'administration_volume',
        'administration_volume_unit',
        'is_vehicle',
        'event_order',
    ];

    protected $casts = [
        'dose' => 'decimal:4',
        'frequency' => 'decimal:2',
        'start_day' => 'integer',
        'end_day' => 'integer',
        'event_order' => 'integer',
        'administration_volume' => 'decimal:3',
        'is_vehicle' => 'boolean',
    ];

    // Route options (same as your existing InducibleDrugTreatment)
    public static $route_options = [
        'intraperitoneal_injection' => 'Intraperitoneal injection (IP)',
        'subcutaneous_injection' => 'Subcutaneous injection (SC)',
        'intramuscular_injection' => 'Intramuscular injection (IM)',
        'intravenous_injection' => 'Intravenous injection (IV)',
        'oral_gavage' => 'Oral gavage',
        'drinking_water' => 'Drinking water',
        'food' => 'Food/chow',
        'other' => 'Other',
    ];

    // Frequency unit options
    public static $frequency_unit_options = [
        'dose' => 'Single dose',
        'per day' => 'Per day',
        'per week' => 'Per week',
        'every X days' => 'Every X days',
        'other' => 'Other',
    ];

    // Dose unit options
    public static $dose_unit_options = [
        'ng/kg' => 'ng/kg',
        'μg/kg' => 'μg/kg',
        'mg/kg' => 'mg/kg',
        'g/kg' => 'g/kg',
        'ml/kg' => 'ml/kg',
        'other' => 'Other',
    ];

    // Drug source type options
    public static $drug_source_type_options = [
        'commercial' => 'Commercial Supplier',
        'custom' => 'Custom/In-house Synthesis',
        'academic' => 'Academic Collaborator',
        'gift' => 'Gift/Donation',
        'other' => 'Other',
    ];

    /**
     * Get the plan this event belongs to
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(DrugAdministrationPlan::class, 'plan_id');
    }

    /**
     * Get the molecule if linked to ChEMBL database
     */
    public function molecule(): BelongsTo
    {
        return $this->belongsTo(Molecule::class);
    }

    /**
     * Get the vehicle (formulation) this drug was resuspended in
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    protected function getStudyForLock(): ?Study
    {
        $this->loadMissing('plan.study');

        $study = $this->plan?->study;

        return $study instanceof Study ? $study : null;
    }

    /**
     * Get computed duration (end_day - start_day)
     */
    public function getDuration()
    {

        // Single dose event
        if ($this->end_day === null) {
            return 0;
        }

        return $this->end_day - ($this->start_day ?? 0);
    }

    /**
     * Get the display name for this drug
     */
    public function getDrugDisplayNameAttribute(): string
    {
        if ($this->molecule) {
            return $this->molecule->name ?? $this->molecule->pref_name ?? 'Unknown';
        }

        return 'Unknown';
    }

    /**
     * Get the route display name
     */
    public function getRouteDisplayAttribute(): string
    {
        if ($this->route === 'other' && $this->route_other) {
            return $this->route_other;
        }

        return self::$route_options[$this->route] ?? $this->route;
    }

    /**
     * Get the route abbreviation (IP, SC, etc.)
     */
    public function getRouteAbbreviationAttribute(): string
    {
        $abbreviations = [
            'intraperitoneal_injection' => 'IP',
            'subcutaneous_injection' => 'SC',
            'intramuscular_injection' => 'IM',
            'intravenous_injection' => 'IV',
            'oral_gavage' => 'PO',
            'drinking_water' => 'water',
            'food' => 'food',
        ];

        return $abbreviations[$this->route] ?? $this->route;
    }

    /**
     * Calculate duration in days
     */
    public function getDurationDaysAttribute(): int
    {
        if ($this->end_day === null) {
            return 0; // Single dose
        }

        return $this->end_day - $this->start_day;
    }

    /**
     * Human-readable frequency, e.g. "daily", "2x/week", "1 every 2 days".
     */
    public function getFrequencyDisplayAttribute(): string
    {
        $frequency = $this->formatFrequencyValue($this->frequency);

        return match ($this->frequency_unit) {
            'per day' => $this->frequency == 1 ? 'daily' : "{$frequency}x/day",
            'per week' => "{$frequency}x/week",
            'dose' => 'single dose',
            'every X days' => $this->frequency_unit_other
                ? "{$frequency} every {$this->frequency_unit_other} days"
                : "{$frequency} every X days",
            'other' => $this->frequency_unit_other
                ? "{$frequency} {$this->frequency_unit_other}"
                : $frequency,
            default => trim("{$frequency} {$this->frequency_unit}"),
        };
    }

    private function formatFrequencyValue(mixed $frequency): string
    {
        if ($frequency === null || $frequency === '') {
            return '';
        }

        return rtrim(rtrim(number_format((float) $frequency, 2, '.', ''), '0'), '.') ?: '0';
    }

    /**
     * Get timeline string for display
     * e.g., "Day 0-28 (8-12 weeks old): PTH(1-34) 80 μg/kg SC, daily"
     */
    public function getTimelineString(int $animalStartAge): string
    {
        // Calculate age range
        $startAge = $animalStartAge + (int) floor($this->start_day / 7);
        $endAge = $animalStartAge + (int) ceil(($this->end_day ?? $this->start_day) / 7);

        // Build day range string
        if ($this->end_day === null || $this->end_day === $this->start_day) {
            $dayRange = "Day {$this->start_day}";
            $ageRange = "{$startAge} weeks old";
        } else {
            $dayRange = "Day {$this->start_day}-{$this->end_day}";
            $ageRange = "{$startAge}-{$endAge} weeks old";
        }

        $frequencyStr = $this->frequency_display;

        // Vehicle (control) events have no active drug — describe the vehicle instead
        if ($this->is_vehicle) {
            $vehicleName = $this->vehicle?->name ?? $this->vehicle_other ?? 'Vehicle';
            $volume = $this->administration_volume
                ? ' '.rtrim(rtrim(number_format($this->administration_volume, 2, '.', ''), '0'), '.')." {$this->administration_volume_unit}"
                : '';

            return "{$dayRange} ({$ageRange}): {$vehicleName}{$volume} {$this->route_abbreviation}, {$frequencyStr}";
        }

        return "{$dayRange} ({$ageRange}): {$this->drug_display_name} {$this->dose} {$this->dose_unit} {$this->route_abbreviation}, {$frequencyStr}";
    }

    /**
     * Check if this is a single dose event
     */
    public function isSingleDose(): bool
    {
        return $this->end_day === null || $this->end_day === $this->start_day;
    }
}
