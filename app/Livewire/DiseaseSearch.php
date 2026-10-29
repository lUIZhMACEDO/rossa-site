<?php

namespace App\Livewire;

use App\Exceptions\StudyLockedException;
use App\Models\DiseaseLine;
use App\Models\Study;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class DiseaseSearch extends Component
{
    public $query = '';

    public $results = [];

    public $selected;

    public $form = '';

    public $studyId;

    public $study;

    public $diseaseLines = [];

    public function mount($studyId = null)
    {
        $this->studyId = $studyId;

        if ($this->studyId) {
            $this->study = Study::with('diseaseLines')->where('id', $this->studyId)->first();

            if ($this->study) {
                $this->selected = $this->study->diseaseLines;
                $this->diseaseLines = $this->study->diseaseLines;
            } else {
                $this->selected = collect();
                $this->diseaseLines = collect();
            }
        } else {
            $this->selected = collect();
            $this->diseaseLines = collect();
            $this->study = null;
        }

        $this->notifyParent();
    }

    // Tell any parent livewire component to update its disease_iri property
    private function notifyParent(): void
    {
        $selected = $this->selected ?? collect();
        $iris = collect($selected)->pluck('iri')->filter()->values()->all();

        $this->dispatch('diseaseIriUpdated', diseaseIri: $iris);
    }

    public function submitQuery()
    {
        $this->query = trim($this->query);

        $this->validate([
            'query' => 'required|string|min:3',
        ]);

        $this->results = DiseaseLine::where('label', 'ilike', '%'.$this->query.'%')
            ->orWhere('obo_id', 'ilike', '%'.$this->query.'%')
            ->limit(50)
            ->get()
            ->toArray();
    }

    public function selectDisease($iri)
    {
        $this->ensureStudyUnlocked();

        $doc = collect($this->results)->firstWhere('iri', $iri);

        if (! $doc) {
            session()->flash('error', 'Disease not found in current search results.');

            return;
        }

        // Fetch full term info from OLS API using obo_id
        $oboId = $doc['obo_id'] ?? null;

        $dbXrefs = null;
        if ($oboId) {
            $response = Http::withoutVerifying()->get('https://www.ebi.ac.uk/ols4/api/ontologies/mondo/terms', [
                'obo_id' => $oboId,
            ]);

            if (! $response->failed()) {
                $data = $response->json();
                // get dbxrefs
                $dbXrefs = $data['_embedded']['terms'][0]['obo_xref'] ?? null;
            }
        }

        // Create or update the disease line with the full info including xrefs
        $disease = DiseaseLine::updateOrCreate(
            ['iri' => $iri],
            [
                'label' => $doc['label'],
                'obo_id' => $oboId,
                'description' => $doc['description'][0] ?? null,
                'ontology' => $doc['ontology_name'] ?? 'mondo',
                'short_form' => $doc['short_form'] ?? null,
                'type' => $doc['type'] ?? null,
                'xrefs' => $dbXrefs,
            ]
        );

        // Attach to current study if not already attached
        if ($this->studyId && $disease) {
            $study = Study::where('id', $this->studyId)->first();
            if ($study && ! $study->diseaseLines->contains($disease->id)) {
                $study->diseaseLines()->attach($disease->id);
            }
        }

        // Track selected in UI (toggle selection)
        if (! $this->selected->contains('iri', $disease->iri)) {
            $this->selected->push($disease);
        } else {
            $this->selected = $this->selected->reject(fn ($item) => $item->iri === $disease->iri);
        }

        $this->notifyParent();

        $this->results = [];
        $this->query = '';
    }

    public function removeDisease($iri)
    {
        $this->ensureStudyUnlocked();

        $disease = DiseaseLine::where('iri', $iri)->first();

        if ($disease && $this->studyId) {
            $study = Study::find($this->studyId);

            if ($study) {
                // Detach from pivot table
                $study->diseaseLines()->detach($disease->id);

                // Optional: delete if no other studies are attached
                // Point of review: Should we keep disease line information anyways?
                // if ($disease->studies()->count() === 0) {
                //     $disease->delete();
                // }
            }
        }

        // Remove from UI
        $this->selected = $this->selected->reject(fn ($item) => $item['iri'] === $iri);

        $this->notifyParent();
    }

    public function render()
    {
        return view('livewire.disease-search');
    }

    private function ensureStudyUnlocked(): void
    {
        if ($this->study && $this->study->isLockedForEditing(auth()->user())) {
            throw new StudyLockedException;
        }
    }
}
