<?php

namespace App\Livewire;

use App\Models\GeneticAnimalTarget;
use App\Models\InducibleDrugTreatment;
use App\Models\Study;
use Livewire\Component;

class InducibleDrugTreatmentTable extends Component
{
    public Study $study;

    public $type;

    public $geneticAnimalTarget;

    public $autofillName;

    public $readonlyName = false;

    public $doseUnitDropdownOptions = [
        'ng/kg' => 'ng/kg',
        'ug/kg' => 'ug/kg',
        'mg/kg' => 'mg/kg',
        'g/kg' => 'g/kg',
        'ml/kg' => 'ml/kg',
    ];

    public $durationUnitDropdownOptions = [
        'minutes' => 'minutes',
        'hours' => 'hours',
        'days' => 'days',
        'weeks' => 'weeks',
    ];

    public $frequencyDropdownOptions = [
        'one dose' => 'one dose',
        'one dose/day' => 'one dose/day',
        'one dose/every 2 days' => 'one dose/every 2 days',
        'Two doses/day' => 'Two doses/day',
        'One dose per day/5 days a week' => 'One dose per day/5 days a week',
        'Ng/hour' => 'Ng/hour',
        'Continuous' => 'Continuous (food/water)',
        'Other' => 'Other',
    ];

    public $newTreatment = [
        'drugName' => '',
        'drugDose' => '',
        'drugDoseUnit' => '',
        'drugDuration' => '',
        'drugDurationUnit' => '',
        'drugFrequency' => '',
        'drugFrequencyOther' => '',
        'drugRoute' => '',
    ];

    public $routeDropdownOptions = [
        'Oral' => 'Oral',
        'Intraperitoneal' => 'Intraperitoneal (IP)',
        'Intravenous' => 'Intravenous (IV)',
        'Intramuscular' => 'Intramuscular (IM)',
        'Subcutaneous' => 'Subcutaneous (SC)',
        'Rectal' => 'Rectal',
    ];

    protected $rules = [
        'newTreatment.drugName' => 'required',
        'newTreatment.drugDose' => 'required|numeric',
        'newTreatment.drugDoseUnit' => 'required',
        'newTreatment.drugDuration' => 'required|numeric',
        'newTreatment.drugDurationUnit' => 'required',
        'newTreatment.drugFrequency' => 'required',
        'newTreatment.drugRoute' => 'required',
    ];

    public function mount(Study $study, $type, $autofillName = '', ?GeneticAnimalTarget $geneticAnimalTarget = null)
    {
        $this->study = $study;
        $this->type = $type;
        $this->autofillName = $autofillName;
        $this->newTreatment['drugName'] = $autofillName;
        $this->geneticAnimalTarget = $geneticAnimalTarget;
    }

    public function addTreatment()
    {
        $this->validate();

        InducibleDrugTreatment::create([
            'study_id' => $this->study->id,
            'genetic_animal_target_id' => $this->geneticAnimalTarget ? $this->geneticAnimalTarget->id : null,
            'type' => $this->type,
            'drug_name' => $this->newTreatment['drugName'],
            'drug_dose' => $this->newTreatment['drugDose'],
            'drug_dose_unit' => $this->newTreatment['drugDoseUnit'],
            'drug_duration' => $this->newTreatment['drugDuration'],
            'drug_duration_unit' => $this->newTreatment['drugDurationUnit'],
            'drug_frequency' => $this->newTreatment['drugFrequency'],
            'drug_frequency_other' => $this->newTreatment['drugFrequencyOther'],
            'drug_route' => $this->newTreatment['drugRoute'],
        ]);

        session()->flash('message', 'Drug treatment added successfully.');

        // Reset the form
        $this->newTreatment = [
            'drugName' => '',
            'drugDose' => '',
            'drugDoseUnit' => '',
            'drugDuration' => '',
            'drugDurationUnit' => '',
            'drugFrequency' => '',
            'drugFrequencyOther' => '',
            'drugRoute' => '',
        ];
    }

    public function removeTreatment($id)
    {
        InducibleDrugTreatment::where('id', $id)->delete();
        session()->flash('message', 'Drug treatment removed successfully.');
    }

    public function render()
    {
        $treatments = InducibleDrugTreatment::where('study_id', $this->study->id)
            ->where('type', $this->type)
            ->where('genetic_animal_target_id', $this->geneticAnimalTarget ? $this->geneticAnimalTarget->id : null)
            ->get();

        return view('livewire.inducible-drug-treatment-table', [
            'treatments' => $treatments,
        ]);
    }
}
