<?php

namespace App\Livewire;

use App\Enums\CreSystem;
use App\Models\Molecule;
use App\Services\ChemblService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\On;
use Livewire\Component;

class MoleculeSearch extends Component
{
    // saved molecule id used for init
    #[Modelable]
    public $moleculeId;

    // chembl id
    public $moleculeChemblId;

    public $moleculeName;

    public $query = '';

    public $results = [];

    public $isFocused = false;

    public $molecule = null;

    public $cannotFindMolecule = false;

    public $customMoleculeName = '';

    private ChemblService $chemblService;

    public function boot(ChemblService $chemblService)
    {
        $this->chemblService = $chemblService;
    }

    public function mount($moleculeId = null)
    {
        // init with molecule
        $this->moleculeId = $moleculeId;
        if ($this->moleculeId) {
            $this->molecule = Molecule::find($this->moleculeId);
            if ($this->molecule) {
                $this->moleculeName = $this->molecule->pref_name;
                $this->query = $this->molecule->pref_name;
                $this->moleculeChemblId = $this->molecule->chembl_id;
            }
        }
    }

    public function updatedQuery()
    {
        // wipe selection when query changes
        if ($this->molecule && $this->query !== $this->molecule->pref_name) {
            $this->molecule = null;
            $this->moleculeId = null;
            $this->moleculeChemblId = null;
            $this->moleculeName = null;
            $this->customMoleculeName = '';
        }

        $this->searchMolecules();
    }

    public function searchMolecules()
    {
        if (trim($this->query) === '') {
            $this->results = [];

            return;
        }

        if (strlen($this->query) < 3) {
            $this->results = [];

            return;
        }

        $query = mb_strtolower($this->query);
        $userId = auth()->id();

        $this->results = DB::table('molecules')
            ->where(function ($q) use ($query) {
                $q->whereRaw('LOWER(pref_name) LIKE ?', ['%'.$query.'%'])
                    ->orWhereRaw('EXISTS (SELECT 1 FROM jsonb_array_elements_text(synonyms::jsonb) AS elem WHERE LOWER(elem) LIKE ?)', ['%'.$query.'%']);
            })
            ->where(function ($q) use ($userId) {
                // custom molecules are only shown to the user who created them
                if ($userId) {
                    $q->where('is_custom', false)
                        ->orWhere('user_id', $userId);
                } else {
                    $q->where('is_custom', false);
                }
            })
            ->orderByRaw('
                CASE 
                    WHEN LOWER(pref_name) = ? THEN 1
                    WHEN LOWER(pref_name) LIKE ? THEN 2
                    ELSE 3
                END
            ', [$query, $query.'%'])
            ->orderBy('pref_name')
            ->take(15)
            ->get()
            ->map(function ($item) {
                return (array) $item;
            })
            ->toArray();
    }

    public function selectMolecule($molecule_id)
    {
        $this->molecule = Molecule::find($molecule_id);

        // update selection
        $this->moleculeId = $this->molecule->id;
        $this->moleculeChemblId = $this->molecule->chembl_id;
        $this->moleculeName = $this->molecule->pref_name;
        $this->query = $this->molecule->pref_name;
        $this->customMoleculeName = '';
        $this->cannotFindMolecule = false;

        $this->isFocused = false;

        // dispatch to let parent know
        $this->dispatch('moleculeSelected', [
            'chemblId' => $this->moleculeChemblId,
            'moleculeId' => $this->moleculeId,
        ]);
    }

    public function selectCustomMolecule()
    {
        if (trim($this->customMoleculeName) === '') {
            return;
        }

        // Create custom molecule because Chembl does not have it
        $customMolecule = Molecule::create([
            'chembl_id' => null,
            'pref_name' => $this->customMoleculeName,
            'is_custom' => true,
            'user_id' => auth()->id(),
        ]);

        $this->molecule = $customMolecule;
        $this->moleculeId = $customMolecule->id;
        $this->moleculeChemblId = null;
        $this->moleculeName = $customMolecule->pref_name;
        $this->query = $customMolecule->pref_name;

        $this->isFocused = false;

        // dispatch to let parent know about custom molecule
        $this->dispatch('moleculeSelected', [
            'chemblId' => null,
            'moleculeId' => $this->moleculeId,
        ]);
    }

    public function assignDoxycycline()
    {
        $doxycycline = Molecule::where('chembl_id', 'CHEMBL1200699')->first();
        $this->selectMolecule($doxycycline->id);
    }

    public function assignTamoxifen()
    {
        $tamoxifen = Molecule::where('chembl_id', 'CHEMBL83')->first();
        $this->selectMolecule($tamoxifen->id);
    }

    #[On('assignCreSystem')]
    public function assignCreSystem($creSystem)
    {
        Log::info('Cre system selected: '.$creSystem);
        if ($creSystem === CreSystem::DOXYCYCLINE->value) {
            $this->assignDoxycycline();
        } elseif ($creSystem === CreSystem::TAMOXIFEN->value) {
            $this->assignTamoxifen();
        }
    }

    public function onFocus()
    {
        // get results
        $this->searchMolecules();

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
        return view('livewire.molecule-search');
    }
}
