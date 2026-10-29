<?php

namespace App\Livewire\Datacenter\Browser;

use App\Models\Study;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class OntologyBrowser extends Component
{
    use WithPagination;

    private const CATEGORY_FILTERS = [
        'all' => 'All',
        'thematic' => 'Thematic Areas',
        'tissue' => 'Tissue Interactions',
        'cell' => 'Cell Types',
        'disease' => 'Disease (MONDO)',
    ];

    private const TAB_BY_FILTER = [
        'thematic' => 'Thematic Areas',
        'tissue' => 'Tissue Interactions',
        'cell' => 'Cell Types',
    ];

    private const FILTER_TYPES = [
        'all',
        'micro_ct',
        'dexa',
        'bone_histo',
        'mechanical_testing',
    ];

    private const SEX_FILTERS = [
        'all',
        'M',
        'F',
    ];

    public string $query = '';

    public int $perPage = 10;

    public string $categoryFilter = 'all';

    public string $filterType = 'all';

    public string $sexFilter = 'all';

    protected $queryString = [
        'query' => ['except' => ''],
        'categoryFilter' => ['as' => 'category', 'except' => 'all'],
        'filterType' => ['as' => 'filter', 'except' => 'all'],
        'sexFilter' => ['as' => 'sex', 'except' => 'all'],
    ];

    public function updatedQuery(): void
    {
        $this->resetPage();
    }

    public function applySearch(): void
    {
        $this->resetPage();
    }

    public function setCategoryFilter(string $filter): void
    {
        if (! array_key_exists($filter, self::CATEGORY_FILTERS)) {
            return;
        }

        $this->categoryFilter = $filter;
        $this->resetPage();
    }

    public function setFilterType(string $filterType): void
    {
        $this->filterType = in_array($filterType, self::FILTER_TYPES, true)
            ? $filterType
            : 'all';

        $this->resetPage();
    }

    public function setSexFilter(string $sexFilter): void
    {
        $this->sexFilter = in_array($sexFilter, self::SEX_FILTERS, true)
            ? $sexFilter
            : 'all';

        $this->resetPage();
    }

    public function render()
    {
        $searchTerm = trim($this->query);

        $studies = Study::query()
            ->publiclyVisible()
            ->with([
                'user',
                'tags',
                'diseaseLines',
                'sampleGroups',
                'analysisUploads.analysisType',
                'analysisUploads.specimenType',
                'analysisUploads.region',
                'analysisUploads.mechanicalTestingDetails',
            ])
            ->where(function (Builder $query): void {
                $query->whereHas('tags')
                    ->orWhereHas('diseaseLines');
            })
            ->when($this->categoryFilter === 'disease', function (Builder $query): void {
                $query->whereHas('diseaseLines');
            })
            ->when(isset(self::TAB_BY_FILTER[$this->categoryFilter]), function (Builder $query): void {
                $query->whereHas('tags', function (Builder $tagQuery): void {
                    $tagQuery->where('tab', self::TAB_BY_FILTER[$this->categoryFilter]);
                });
            })
            ->when($searchTerm !== '', function (Builder $query) use ($searchTerm): void {
                $like = '%'.strtolower($searchTerm).'%';

                $query->where(function (Builder $nested) use ($like): void {
                    $nested->whereHas('tags', function (Builder $tagQuery) use ($like): void {
                        $tagQuery->where(function (Builder $inner) use ($like): void {
                            $inner->whereRaw('LOWER(name) LIKE ?', [$like])
                                ->orWhereRaw('LOWER(description) LIKE ?', [$like])
                                ->orWhereRaw('LOWER(tab) LIKE ?', [$like]);
                        });
                    })->orWhereHas('diseaseLines', function (Builder $diseaseQuery) use ($like): void {
                        $diseaseQuery->where(function (Builder $inner) use ($like): void {
                            $inner->whereRaw('LOWER(label) LIKE ?', [$like])
                                ->orWhereRaw('LOWER(description) LIKE ?', [$like])
                                ->orWhereRaw('LOWER(ontology) LIKE ?', [$like])
                                ->orWhereRaw('LOWER(short_form) LIKE ?', [$like])
                                ->orWhereRaw('LOWER(obo_id) LIKE ?', [$like]);
                        });
                    });
                });
            })
            ->when($this->filterType !== 'all', function (Builder $query): void {
                $query->whereHas('analysisUploads.analysisType', function (Builder $analysisTypeQuery): void {
                    $analysisTypeQuery->where('code', $this->filterType);
                });
            })
            ->when($this->sexFilter !== 'all', function (Builder $query): void {
                $query->whereHas('sampleGroups', function (Builder $sampleGroupQuery): void {
                    $sampleGroupQuery->where('sex', $this->sexFilter);
                });
            })
            ->orderByDesc('completion_date')
            ->orderByDesc('id')
            ->paginate($this->perPage);

        return view('livewire.datacenter.browser.ontology-browser', [
            'studies' => $studies,
            'categoryFilters' => self::CATEGORY_FILTERS,
        ]);
    }
}
