<?php

namespace App\Livewire\Datacenter\Browser;

use App\Models\DrugAdministrationPlan;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class DrugBrowser extends Component
{
    use WithPagination;

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

    public string $filterType = 'all';

    public string $sexFilter = 'all';

    protected $queryString = [
        'query' => ['except' => ''],
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

        $drugPlans = DrugAdministrationPlan::query()
            ->with([
                'study.user',
                'study.sampleGroups',
                'study.analysisUploads.analysisType',
                'study.analysisUploads.specimenType',
                'study.analysisUploads.region',
                'study.analysisUploads.mechanicalTestingDetails',
                'events.molecule',
            ])
            ->whereHas('study', fn (Builder $query) => $query->publiclyVisible())
            ->whereHas('events', function (Builder $query): void {
                $query->where('is_vehicle', false)->whereNotNull('molecule_id');
            })
            ->when($searchTerm !== '', function (Builder $query) use ($searchTerm): void {
                $like = '%'.strtolower($searchTerm).'%';

                $query->where(function (Builder $nested) use ($like): void {
                    $nested->whereRaw('LOWER(plan_name) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(description) LIKE ?', [$like])
                        ->orWhereHas('events.molecule', function (Builder $moleculeQuery) use ($like): void {
                            $moleculeQuery->where(function (Builder $inner) use ($like): void {
                                $inner->whereRaw('LOWER(pref_name) LIKE ?', [$like])
                                    ->orWhereRaw('LOWER(chembl_id) LIKE ?', [$like]);
                            });
                        });
                });
            })
            ->when($this->filterType !== 'all', function (Builder $query): void {
                $query->whereHas('study.analysisUploads.analysisType', function (Builder $analysisTypeQuery): void {
                    $analysisTypeQuery->where('code', $this->filterType);
                });
            })
            ->when($this->sexFilter !== 'all', function (Builder $query): void {
                $query->whereHas('study.sampleGroups', function (Builder $sampleGroupQuery): void {
                    $sampleGroupQuery->where('sex', $this->sexFilter);
                });
            })
            ->orderByDesc('study_id')
            ->orderByDesc('id')
            ->paginate($this->perPage);

        return view('livewire.datacenter.browser.drug-browser', [
            'drugPlans' => $drugPlans,
        ]);
    }
}
