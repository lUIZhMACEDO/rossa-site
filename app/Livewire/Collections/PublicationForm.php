<?php

namespace App\Livewire\Collections;

use App\Models\Collection;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PublicationForm extends Component
{
    public Collection $collection;

    public $publications;

    public $newPublication = [
        'title' => '',
        'doi' => '',
        'pubmed_id' => '',
    ];

    // Nothing here is required — all fields optional per design decision
    protected $rules = [
        'newPublication.title' => 'nullable|string',
        'newPublication.doi' => 'nullable|string',
        'newPublication.pubmed_id' => 'nullable|string',
    ];

    private function authorizePublicationEditing(): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        abort_unless($user !== null && $this->collection->canUserEditMetadata($user), 403);
    }

    public function mount(Collection $collection)
    {
        $this->collection = $collection;
        $this->authorizePublicationEditing();
        $this->publications = $collection->publications;
    }

    public function addPublication()
    {
        $this->authorizePublicationEditing();

        $this->validate();

        // Skip creating a fully-empty row if the user clicks "Add" with nothing filled in
        if (empty(array_filter($this->newPublication))) {
            return;
        }

        $this->collection->publications()->create($this->newPublication);

        $this->newPublication = ['title' => '', 'doi' => '', 'pubmed_id' => ''];
        $this->publications = $this->collection->fresh()->publications;

        session()->flash('lower-success', 'Publication added successfully.');
    }

    public function removePublication($id)
    {
        $this->authorizePublicationEditing();

        $publication = $this->collection->publications()->find($id);

        if ($publication) {
            $publication->delete();
        }

        $this->publications = $this->collection->fresh()->publications;

        session()->flash('lower-success', 'Publication removed successfully.');
    }

    public function render()
    {
        return view('livewire.collections.publication-form');
    }
}
