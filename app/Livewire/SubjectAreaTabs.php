<?php

namespace App\Livewire;

use App\Exceptions\StudyLockedException;
use App\Models\DiseaseLine;
use App\Models\Study;
use App\Models\Tag;
use Livewire\Attributes\On;
use Livewire\Component;

class SubjectAreaTabs extends Component
{
    public Study $study;

    public $TABS = [
        'thematic-tab' => 'Thematic Areas',
        'tissue-tab' => 'Tissue Interactions',
        'cell-tab' => 'Cell Types',
        'disease-tab' => 'Disease Search',
    ];

    public $TABS_SINGULAR = [
        'thematic-tab' => 'Thematic Area',
        'tissue-tab' => 'Tissue Interaction',
        'cell-tab' => 'Cell Type',
        'disease-tab' => 'Disease',
    ];

    public string $activeTab = 'thematic-tab';

    // tags
    public array $tags = [];

    public array $disease_iri = [];

    public string $otherTagInput = '';

    public function mount(Study $study)
    {
        $this->study = $study;
        $this->tags = $study->tags->pluck('id')->toArray();
        $this->disease_iri = $study->diseaseLines->pluck('iri')->toArray();
    }

    #[On('diseaseIriUpdated')]
    public function setDiseaseIri($diseaseIri): void
    {
        $this->disease_iri = $diseaseIri ?? [];
    }

    public function setActiveTab($value)
    {
        $this->activeTab = $value;
    }

    public function addOtherTag($tabName)
    {
        $this->ensureStudyUnlocked();

        $this->validate([
            'otherTagInput' => 'required|string|max:255',
        ]);

        // Create a custom tag for this study scoped to the specific tab
        $tag = Tag::firstOrCreate(
            [
                'name' => $this->otherTagInput,
                'study_id' => $this->study->id,
                'tab' => $tabName,
            ],
            [
                'description' => 'Custom tag for study',
            ]
        );

        // Add to selected tags if not already there
        if (! in_array($tag->id, $this->tags)) {
            $this->tags[] = $tag->id;
        }

        // Clear input
        $this->otherTagInput = '';
    }

    public function nextSection()
    {
        // Move to next tab without saving
        if ($this->activeTab !== array_key_last($this->TABS)) {
            $tabKeys = array_keys($this->TABS);
            $currentIndex = array_search($this->activeTab, $tabKeys);
            $this->activeTab = $tabKeys[$currentIndex + 1] ?? $this->activeTab;
        }
    }

    public function save()
    {
        $this->ensureStudyUnlocked();

        $data = $this->validate([
            'tags' => 'nullable|array',
            'tags.*' => 'integer|exists:tags,id',
            'disease_iri' => 'nullable|array',
            'disease_iri.*' => 'string',
        ]);

        // Sync tags with the study
        $this->study->tags()->sync($data['tags'] ?? []);

        // Handle disease lines
        if ($this->disease_iri) {
            $diseaseLines = DiseaseLine::whereIn('iri', $this->disease_iri)->get();
            $this->study->diseaseLines()->sync($diseaseLines->pluck('id')->toArray());
        } else {
            $this->study->diseaseLines()->detach();
        }

        // If last item, we go to investigators page, else go to nex tab
        if ($this->activeTab === array_key_last($this->TABS)) {
            session()->flash('success', 'Subject areas updated successfully.');

            return redirect()->route('study.investigators', $this->study);
        } else {
            $tabKeys = array_keys($this->TABS);
            $currentIndex = array_search($this->activeTab, $tabKeys);
            $this->activeTab = $tabKeys[$currentIndex + 1] ?? $this->activeTab;
        }
    }

    protected function ensureStudyUnlocked(): void
    {
        if ($this->study->isLockedForEditing(auth()->user())) {
            throw new StudyLockedException;
        }
    }

    public function render()
    {
        return view('livewire.subject-area-tabs');
    }
}
