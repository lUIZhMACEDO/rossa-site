<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *  This Model used to be called DrugTreatment, then GeneticDrugTreatment
 */
class InducibleDrugTreatment extends Model
{
    protected $fillable = [
        'dose',
        'dose_unit',
        'duration',
        'duration_unit',
        'frequency',
        'frequency_other',
        'drug_route',
        'drug_route_other',
        'impact',
        'animal_model_id',
        'molecule_id',
    ];

    public static $category_options = [
        'small_molecule' => 'Small molecule',
        'biologic' => 'Biologic (recombinant protein, antibody, peptide)',
        'synthetic' => 'Synthetic Anti-Sense Oligonucleotide',
        'sh_rna' => 'shRNA',
        'cas_guide_rna' => 'Cas/Guide RNA',
        'other' => 'Other',
    ];

    public static $drug_route_options = [
        'intraperitoneal_injection' => 'Intraperitoneal Injection',
        'subcutaneous_injection' => 'Subcutaneous Injection',
        'oral_gavage' => 'Oral Gavage',
        'intravenous_injection' => 'Intravenous Injection',
        'drinking_water' => 'Drinking water',
        'food_chow' => 'Food Chow',
        'viral_transmission' => 'Viral Transmission',
        'hypothalamus_infusion' => 'Hypothalamus Infusion',
        'other' => 'Other',
    ];

    public static $impact_options = [
        'agonist' => 'Agonist',
        'antagonist' => 'Antagonist',
        'unknown' => 'Unknown',
        'non_specific' => 'Non-specific broad impact',
    ];

    public static $drug_unit_options = [
        'ng/kg' => 'ng/kg',
        'ug/kg' => 'ug/kg',
        'g/kg' => 'g/kg',
        'ml/kg' => 'ml/kg',
        'cells' => 'cells',
        'vg/kg' => 'vg/kg',
        'mg/kg' => 'mg/kg',
    ];

    public static $drug_duration_unit_options = [
        'minutes' => 'Minutes',
        'hours' => 'Hours',
        'days' => 'Days',
        'weeks' => 'Weeks',
    ];

    public static $drug_frequency_unit_options = [
        'dose' => 'Dose',
        'dose_day' => 'Dose(s)/day',
        'dose_2_days' => 'Dose/every 2 days',
        'dose_day_5dw' => 'Dose per day/5days a week',
        'ng_hour' => 'Ng/hour',
        'continuous' => 'Continuous (food/water)',
        'other' => 'Other',
    ];

    public static $tamoxifen_molecular_targets = [
        'cre_er' => 'Cre ER Positive (CreER+)',
        'cre_ert' => 'Cre ERT Positive (CreERT+)',
        'cre_ert2' => 'Cre ERT2 Positive (CreERT2+)',
    ];

    public static $doxycycline_molecular_targets = [
        'tet_on' => 'Tet-On rtTA (Tet-On)',
        'tet_off' => 'Tet-Off tTA (Tet-Off)',
    ];

    public function drugRouteDisplay()
    {
        return $this->drug_route === 'other' ?
            $this->drug_route_other :
            $this::$drug_route_options[$this->drug_route];
    }

    public function impactDisplay()
    {
        return $this->impact === 'other' ?
            $this->impact_other :
            $this::$impact_options[$this->impact] ?? '-';
    }

    public function drugFrequencyUnitDisplay()
    {
        return $this->drug_frequency_unit === 'other' ?
            $this->drug_frequency_other :
            $this::$drug_frequency_unit_options[$this->drug_frequency_unit] ?? '';
    }

    public function drugDoseUnitDisplay()
    {
        return $this->drug_dose_unit === 'other' ?
            $this->drug_dose_unit_other :
            $this::$drug_unit_options[$this->drug_dose_unit];
    }

    public function drugDurationUnitDisplay()
    {
        return $this->drug_duration_unit === 'other' ?
            $this->drug_duration_unit_other :
            $this::$drug_duration_unit_options[$this->drug_duration_unit];
    }

    public function study(): BelongsTo
    {
        return $this->belongsTo(Study::class);
    }

    public function animalModel(): BelongsTo
    {
        return $this->belongsTo(AnimalModel::class);
    }

    public function molecule(): BelongsTo
    {
        return $this->belongsTo(Molecule::class);
    }
}
