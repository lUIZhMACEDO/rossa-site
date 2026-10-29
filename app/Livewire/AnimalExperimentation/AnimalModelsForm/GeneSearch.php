<?php

namespace App\Livewire\AnimalExperimentation\AnimalModelsForm;

use App\Enums\Species;
use App\Models\Gene;
use Livewire\Attributes\On;
use Livewire\Component;

class GeneSearch extends Component
{
    public $species;

    public $geneId;

    public $formField;

    public $geneAllianceId;

    public $geneSymbol;

    public $displayGeneSpecies;

    public $query = '';

    public $results = [];

    public $isFocused = false;

    public $gene = null;

    public bool $lockSpecies = false;

    public ?string $animalSpecies = null;

    public ?string $instanceId = null;

    public ?string $excludeInstanceId = null;

    public array $excludeGeneIds = [];

    public function mount($formField = 'gene_id', $geneId = null)
    {
        $this->geneId = $geneId;
        $this->formField = $formField;

        if ($this->lockSpecies) {
            $this->applyLockedSpecies($this->animalSpecies, false);
        }

        if ($this->geneId) {
            $this->gene = Gene::find($this->geneId);
            if ($this->gene) {
                if (! $this->lockSpecies) {
                    $this->species = explode(':', $this->gene->alliance_id)[0];
                }
                $this->geneSymbol = $this->gene->symbol;
                $this->displayGeneSpecies = explode(':', $this->gene->alliance_id)[0];
                $this->query = $this->gene->symbol;
                $this->geneAllianceId = $this->gene->alliance_id;

                // Keep Alpine genotype labels in sync after validation redirects.
                $this->dispatch('geneSelected', [
                    'allianceId' => $this->geneAllianceId,
                    'geneId' => $this->geneId,
                    'symbol' => $this->geneSymbol,
                    'instanceId' => $this->instanceId,
                ]);
            }
        }
    }

    public function updatedSpecies()
    {
        $this->clearGeneSelection(false);
        $this->isFocused = false;
    }

    public function updatedQuery()
    {
        $this->searchGenes();
    }

    public function searchGenes()
    {
        if (trim($this->query) === '') {
            $this->results = [];

            return;
        }

        if ($this->lockSpecies && ! $this->species) {
            $this->results = [];

            return;
        }

        $results = Gene::searchBySymbol($this->query, $this->species);
        if ($this->excludeGeneIds) {
            $exclude = array_map('intval', $this->excludeGeneIds);
            $results = array_values(array_filter($results, function ($gene) use ($exclude) {
                return ! in_array((int) ($gene['id'] ?? 0), $exclude, true);
            }));
        }

        $this->results = $results;
    }

    public function selectGene($allianceGeneId)
    {
        $this->gene = Gene::updateOrCreateFromAlliance($allianceGeneId);

        if (! $this->gene) {
            return;
        }

        if (in_array((int) $this->gene->id, array_map('intval', $this->excludeGeneIds), true)) {
            return;
        }

        $this->geneId = $this->gene->id;
        $this->geneAllianceId = $this->gene->alliance_id;
        $this->geneSymbol = $this->gene->symbol;
        $this->displayGeneSpecies = explode(':', $this->geneAllianceId)[0];
        $this->query = $this->gene->symbol;

        $this->isFocused = false;

        $this->dispatch('geneSelected', [
            'allianceId' => $this->geneAllianceId,
            'geneId' => $this->geneId,
            'symbol' => $this->geneSymbol,
            'instanceId' => $this->instanceId,
        ]);
    }

    #[On('speciesChanged')]
    public function speciesChanged($species)
    {
        if (! $this->lockSpecies) {
            return;
        }

        $animalSpecies = is_array($species) ? ($species[0] ?? null) : $species;
        $this->animalSpecies = $animalSpecies;
        $this->applyLockedSpecies($animalSpecies, true);
    }

    #[On('geneSelected')]
    public function onPeerGeneSelected($payload = [])
    {
        if (! $this->excludeInstanceId) {
            return;
        }

        $instanceId = $payload['instanceId'] ?? null;
        if ($instanceId !== $this->excludeInstanceId) {
            return;
        }

        $geneId = $payload['geneId'] ?? null;
        $this->excludeGeneIds = $geneId ? [(int) $geneId] : [];

        if ($this->geneId && in_array((int) $this->geneId, $this->excludeGeneIds, true)) {
            $this->clearGeneSelection(true);
        }
    }

    public function onFocus()
    {
        $this->searchGenes();
        $this->isFocused = true;
    }

    public function onBlur()
    {
        if ($this->isFocused) {
            $this->dispatch('checkClickTarget');
        }
    }

    public function handleClickOutside()
    {
        $this->isFocused = false;
    }

    private function applyLockedSpecies(?string $animalSpecies, bool $clearSelection): void
    {
        $enum = $animalSpecies ? Species::tryFrom($animalSpecies) : null;
        $prefix = $enum?->alliancePrefix();

        if ($this->species !== $prefix) {
            $this->species = $prefix;
            if ($clearSelection) {
                $this->clearGeneSelection(true);
            }
        }
    }

    private function clearGeneSelection(bool $dispatch): void
    {
        $hadSelection = (bool) $this->geneId;
        $this->gene = null;
        $this->geneId = null;
        $this->geneAllianceId = null;
        $this->geneSymbol = null;
        $this->displayGeneSpecies = null;
        $this->isFocused = false;

        if ($dispatch && $hadSelection) {
            $this->dispatch('geneSelected', [
                'allianceId' => null,
                'geneId' => null,
                'symbol' => null,
                'instanceId' => $this->instanceId,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.animal-experimentation.animal-models-form.gene-search');
    }
}
