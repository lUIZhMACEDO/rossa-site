<?php

namespace App\Livewire\Datacenter\Browser;

use App\Models\DescriptiveStatistic;
use App\Models\Measurement;
use App\Models\Study;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class ParameterBrowser extends Component
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

        $studies = Study::query()
            ->publiclyVisible()
            ->with([
                'user',
                'sampleGroups',
                'analysisUploads.analysisType',
                'analysisUploads.specimenType',
                'analysisUploads.region',
                'analysisUploads.mechanicalTestingDetails',
            ])
            ->whereHas('sampleGroups', function (Builder $query) use ($searchTerm): void {
                $query->whereIn('id', $this->matchingSampleGroupIds($searchTerm));
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

        $measurementsByStudy = $this->measurementsForStudies($studies->getCollection(), $searchTerm);

        return view('livewire.datacenter.browser.parameter-browser', [
            'studies' => $studies,
            'measurementsByStudy' => $measurementsByStudy,
        ]);
    }

    private function matchingSampleGroupIds(string $searchTerm): Builder
    {
        return DescriptiveStatistic::query()
            ->select('sample_group_id')
            ->whereHas('measurement', function (Builder $query) use ($searchTerm): void {
                $query->where('graph_exclude', false);

                if ($searchTerm === '') {
                    return;
                }

                $like = '%'.strtolower($searchTerm).'%';

                $query->where(function (Builder $nested) use ($like): void {
                    $nested->whereRaw('LOWER(display_name) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(name) LIKE ?', [$like]);
                });
            })
            ->when($this->filterType !== 'all', function (Builder $query): void {
                $query->whereHas('analysisType', function (Builder $analysisTypeQuery): void {
                    $analysisTypeQuery->where('code', $this->filterType);
                });
            })
            ->distinct();
    }

    private function measurementsForStudies(Collection $studies, string $searchTerm): Collection
    {
        if ($studies->isEmpty()) {
            return collect();
        }

        $sampleGroupIds = $studies->flatMap(fn (Study $study) => $study->sampleGroups->pluck('id'))->unique()->values();

        $measurementIds = DescriptiveStatistic::query()
            ->whereIn('sample_group_id', $sampleGroupIds)
            ->when($this->filterType !== 'all', function (Builder $query): void {
                $query->whereHas('analysisType', function (Builder $analysisTypeQuery): void {
                    $analysisTypeQuery->where('code', $this->filterType);
                });
            })
            ->whereHas('measurement', function (Builder $query) use ($searchTerm): void {
                $query->where('graph_exclude', false);

                if ($searchTerm === '') {
                    return;
                }

                $like = '%'.strtolower($searchTerm).'%';

                $query->where(function (Builder $nested) use ($like): void {
                    $nested->whereRaw('LOWER(display_name) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(name) LIKE ?', [$like]);
                });
            })
            ->pluck('measurement_id', 'sample_group_id');

        $measurements = Measurement::query()
            ->with('analysisType')
            ->whereIn('id', $measurementIds->values()->unique())
            ->get()
            ->keyBy('id');

        $sampleGroupStudyMap = $studies->flatMap(function (Study $study) {
            return $study->sampleGroups->mapWithKeys(fn ($group) => [$group->id => $study->id]);
        });

        return $measurementIds
            ->map(function (int $measurementId, int $sampleGroupId) use ($measurements, $sampleGroupStudyMap) {
                $studyId = $sampleGroupStudyMap[$sampleGroupId] ?? null;
                $measurement = $measurements->get($measurementId);

                if (! $studyId || ! $measurement) {
                    return null;
                }

                return [
                    'study_id' => $studyId,
                    'measurement' => $measurement,
                ];
            })
            ->filter()
            ->groupBy('study_id')
            ->map(fn (Collection $items) => $items->pluck('measurement')->unique('id')->values());
    }
}
