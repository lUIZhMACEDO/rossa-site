<?php

namespace App\Models;

use App\Traits\LocksWithStudy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SampleGroup extends Model
{
    use LocksWithStudy;

    protected $fillable = [
        'study_id',
        'label',
        'animal_model_id',
        'control',
        'genotype',
        'age',
        'sex',
        'gonadectomy_procedure_id',
        'drug_administration_plan_id',
        'cre_status',
    ];

    protected $casts = [
        'control' => 'boolean',
    ];

    public function study()
    {
        return $this->belongsTo(Study::class);
    }

    /**
     * @return BelongsTo<AnimalModel, $this>
     */
    public function animalModel(): BelongsTo
    {
        return $this->belongsTo(AnimalModel::class);
    }

    public function samples()
    {
        return $this->hasMany(Sample::class);
    }

    /**
     * @return BelongsTo<GonadectomyProcedure, $this>
     */
    public function gonadectomyProcedure(): BelongsTo
    {
        return $this->belongsTo(GonadectomyProcedure::class);
    }

    public function genotypeWithAllele()
    {
        if ($this->genotype) {
            if ($this->animalModel?->usesCustomGenotypes()) {
                return $this->genotype;
            }

            return str_replace('-', $this->animalModel?->geneticMethod?->allele_abbreviation ?? 'X', $this->genotype);

        }

        return null;
    }

    public function otherInterventionPlan(): BelongsTo
    {
        return $this->belongsTo(OtherInterventionPlan::class, 'other_intervention_plan_id');
    }

    public function drugAdministrationPlan(): BelongsTo
    {
        return $this->belongsTo(DrugAdministrationPlan::class);
    }

    /**
     * Get the drug plan display with timeline (for display in sample group lists)
     */
    public function getDrugPlanDisplayAttribute(): ?string
    {
        if (! $this->drugAdministrationPlan) {
            return null;
        }

        return $this->drugAdministrationPlan->full_display_name.' - '.$this->drugAdministrationPlan->getTimelinePreview();
    }
}
