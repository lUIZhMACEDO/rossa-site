<?php

namespace App\Livewire\Datacenter;

use App\Models\DiseaseLine;
use App\Models\Gene;
use Livewire\Component;
use Livewire\WithPagination;

class RelatedStudies extends Component
{
    use WithPagination;

    public Gene|DiseaseLine $browserType;

    public $studies = [];

    public $filter = '';

    public function mount($browserType): void
    {
        // Assert browserType has a studies relationship (to query related studies)
        if (! method_exists($browserType, 'studies')) {
            throw new \InvalidArgumentException('Browser type must have a studies relationship');
        }

        $this->$browserType = $browserType;
    }

    public function updatedQuery(): void
    {
        $this->search();
    }

    public function search()
    {
        $this->browserType->loadCount('studies');

        $term = trim($this->filter);

        // @TODO: add pagination
        $this->studies = $this->browserType->studies()
            ->select('studies.id', 'studies.identifier', 'studies.title', 'studies.created_at')
            ->when($term !== '', function ($query) use ($term) {
                $query->where(function ($subQuery) use ($term) {
                    $subQuery->where('studies.identifier', 'like', "%{$term}%")
                        ->orWhere('studies.title', 'like', "%{$term}%")
                        ->orWhere('studies.summary', 'like', "%{$term}%");
                });
            })
            ->distinct()
            ->orderByDesc('studies.created_at')
            ->get();
    }

    public function render()
    {
        $this->search();

        return view('livewire.datacenter.related-studies', [
            'studies' => $this->studies,
        ]);
    }
}
