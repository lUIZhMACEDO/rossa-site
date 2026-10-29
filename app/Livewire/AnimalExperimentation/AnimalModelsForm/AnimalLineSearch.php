<?php

namespace App\Livewire\AnimalExperimentation\AnimalModelsForm;

use App\Models\AnimalLine;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class AnimalLineSearch extends Component
{
    public $studyId;

    public $animalLineId;

    public $animalLineLabel;

    public $query = '';

    public $results = [];

    // for handling focus and blur events
    public $isFocused = false;

    public $formField = 'animal_line_id';

    public $textFieldName = 'animal_line';

    public $species = '';

    public bool $hasError = false;

    public $initialQuery = '';

    public function mount($studyId = null, $animalLineId = null, $formField = 'animal_line_id')
    {
        // init with animal line
        $this->studyId = $studyId;
        $this->animalLineId = $animalLineId;
        $this->formField = $formField;
        if (! $this->textFieldName) {
            $this->textFieldName = 'animal_line';
        }
        if ($animalLineId) {
            $animalLine = AnimalLine::find($animalLineId);
            if ($animalLine) {
                $this->animalLineLabel = $animalLine->label;
                $this->query = $animalLine->label;
            }
        } elseif (filled($this->initialQuery)) {
            $this->query = $this->initialQuery;
            $this->animalLineLabel = $this->initialQuery;
        }

        // get basic animal lines
        $this->loadInitialResults();
    }

    #[Computed]
    public function selectedAnimalLine()
    {
        if ($this->animalLineId) {
            return AnimalLine::find($this->animalLineId);
        }

        return null;
    }

    private function loadInitialResults()
    {
        // first 10
        $this->results = AnimalLine::query()
            ->when($this->species, function ($query) {
                $query->where('species', $this->species);
            })
            ->orderBy('label')
            ->whereNull('study_id')
            ->limit(10)
            ->get();
    }

    public function updatedSpecies($value)
    {
        $this->clearSelection();
        $this->searchAnimalLines();
    }

    #[On('speciesChanged')]
    public function speciesChanged($species)
    {
        $species = is_array($species) ? ($species[0] ?? '') : $species;
        if ((string) $this->species === (string) $species) {
            return;
        }

        $this->species = $species;
        $this->clearSelection();
        $this->searchAnimalLines();
    }

    public function updatedQuery()
    {
        // get results
        $this->searchAnimalLines();

        // update custom input on query change
        if ($this->query && $this->query !== $this->animalLineLabel) {
            $this->dispatchCustomInput();
        }
    }

    public function searchAnimalLines()
    {
        if (trim($this->query) === '') {
            $this->loadInitialResults();

            return;
        }

        // search for public and custom lines
        $this->results = AnimalLine::where(function ($query) {
            $query->whereNull('study_id')
                ->orWhere('study_id', $this->studyId);
        })
            ->where(function ($query) {
                $query->where('label', 'ilike', '%'.$this->query.'%')
                    ->orWhereRaw('synonyms::text ilike ?', ['%'.$this->query.'%'])
                    ->orWhere('obo_id', 'ilike', '%'.$this->query.'%');
            })
            ->when($this->species, function ($query) {
                $query->where('species', $this->species);
            })
            ->orderBy('label')
            ->limit(20)
            ->get();
    }

    private function dispatchCustomInput()
    {
        // when user types custom text, send it as custom line
        $this->animalLineId = null;
        $this->animalLineLabel = $this->query;

        $this->dispatch('animalLineSelected', [
            'id' => null, // null indicates custom
            'label' => $this->query,
        ]);
    }

    public function selectAnimalLine($animalLineId)
    {
        $animalLine = AnimalLine::find($animalLineId);
        if (! $animalLine) {
            return;
        }

        // update selection
        $this->animalLineId = $animalLineId;
        $this->animalLineLabel = $animalLine->label;
        $this->query = $animalLine->label;

        // handle focus - should blur the input
        $this->isFocused = false;

        // dispatch to let parent know
        $this->dispatch('animalLineSelected', [
            'id' => $animalLineId,
            'label' => $animalLine->label,
        ]);
    }

    #[On('updateAnimalLineSelection')]
    public function updateSelection($animalLineData)
    {
        // when the user saves the animal model, and custom animal line is created - update with id
        $this->animalLineId = $animalLineData['id'];
        $this->animalLineLabel = $animalLineData['label'];
        $this->query = $animalLineData['label']; // show label in the input
    }

    public function clearSelection()
    {
        // wipe and blur
        $this->animalLineId = null;
        $this->animalLineLabel = null;
        $this->query = '';
        $this->isFocused = false;

        // update parent
        $this->dispatch('animalLineSelected', [
            'id' => null,
            'label' => null,
        ]);
    }

    public function onFocus()
    {
        // get results
        if ($this->query) {
            $this->searchAnimalLines();
        } else {
            $this->loadInitialResults();
        }

        // show results
        $this->isFocused = true;
    }

    public function onBlur()
    {
        // only check click target if not blurred
        if ($this->isFocused) {
            $this->dispatch('checkClickTarget');
        }
    }

    public function handleClickOutside()
    {
        // handling blur via click
        $this->isFocused = false;
    }

    public function render()
    {
        return view('livewire.animal-experimentation.animal-models-form.animal-line-search');
    }
}
