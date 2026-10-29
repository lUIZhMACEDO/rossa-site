<?php

namespace App\Livewire\Datacenter\Browser;

use App\Models\AnimalModel;
use App\Services\Datacenter\GeneBrowserDataService;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GeneBrowser extends Component
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

    private const TABS = [
        'browser-view',
        'table-view',
    ];

    private const DISPLAY_MODES = [
        'scatter',
        'table',
    ];

    private const AGE_BUCKETS = [
        'all',
        'le_12',
        '13_24',
        'gt_24',
        'unknown',
    ];

    public string $query = '';

    public int $perPage = 10;

    public string $activeTab = 'browser-view';

    public string $filterType = 'all';

    public string $sexFilter = 'all';

    public string $selectedAnalysisType = 'all';

    public ?int $selectedXAxisMeasurementId = null;

    public ?int $selectedYAxisMeasurementId = null;

    public ?int $selectedSizeMeasurementId = null;

    public string $selectedTestMethod = 'all';

    public string $selectedBoneSite = 'all';

    public string $selectedSex = 'all';

    public string $selectedAgeBucket = 'all';

    public string $scatterDisplayMode = 'scatter';

    protected $queryString = [
        'query' => ['except' => ''],
        'activeTab' => ['as' => 'view', 'except' => 'browser-view'],
        'filterType' => ['as' => 'filter', 'except' => 'all'],
        'sexFilter' => ['as' => 'sex', 'except' => 'all'],
        'selectedAnalysisType' => ['as' => 'analysis', 'except' => 'all'],
        'selectedXAxisMeasurementId' => ['as' => 'x', 'except' => null],
        'selectedYAxisMeasurementId' => ['as' => 'y', 'except' => null],
        'selectedSizeMeasurementId' => ['as' => 'size', 'except' => null],
        'selectedTestMethod' => ['as' => 'test', 'except' => 'all'],
        'selectedBoneSite' => ['as' => 'site', 'except' => 'all'],
        'selectedSex' => ['as' => 'scatter_sex', 'except' => 'all'],
        'selectedAgeBucket' => ['as' => 'age', 'except' => 'all'],
        'scatterDisplayMode' => ['as' => 'mode', 'except' => 'scatter'],
    ];

    public function updatedQuery(): void
    {
        $this->resetPage();
    }

    public function applySearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterType(): void
    {
        if (! in_array($this->filterType, self::FILTER_TYPES, true)) {
            $this->filterType = 'all';
        }

        $this->resetPage();
    }

    public function setFilterType(string $filterType): void
    {
        $this->filterType = in_array($filterType, self::FILTER_TYPES, true)
            ? $filterType
            : 'all';

        $this->resetPage();
    }

    public function updatedSexFilter(): void
    {
        if (! in_array($this->sexFilter, self::SEX_FILTERS, true)) {
            $this->sexFilter = 'all';
        }

        $this->resetPage();
    }

    public function setSexFilter(string $sexFilter): void
    {
        $this->sexFilter = in_array($sexFilter, self::SEX_FILTERS, true)
            ? $sexFilter
            : 'all';

        $this->resetPage();
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = in_array($tab, self::TABS, true) ? $tab : 'browser-view';
    }

    public function setScatterDisplayMode(string $mode): void
    {
        $this->scatterDisplayMode = in_array($mode, self::DISPLAY_MODES, true) ? $mode : 'scatter';
    }

    public function updatedSelectedAgeBucket(): void
    {
        if (! in_array($this->selectedAgeBucket, self::AGE_BUCKETS, true)) {
            $this->selectedAgeBucket = 'all';
        }
    }

    public function downloadScatterCsv(GeneBrowserDataService $dataService): StreamedResponse
    {
        $payload = $dataService->build($this->scatterFilters());
        $rows = collect($payload['scatterRows'] ?? []);

        $date = now()->format('Y-m-d');
        $filename = "gene-browser-comparisons-{$date}.csv";

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'study_identifier',
                'study_title',
                'gene_symbol',
                'gene_name',
                'model',
                'sample_group',
                'control_sample_group',
                'analysis_type',
                'test_method',
                'bone_site',
                'sex',
                'age_weeks',
                'x_value_percent_change',
                'y_value_percent_change',
                'size_value_percent_change',
            ]);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['study_identifier'],
                    $row['study_title'],
                    $row['gene_symbol'],
                    $row['gene_name'],
                    $row['model_label'],
                    $row['sample_group_label'],
                    $row['control_sample_group_label'],
                    $row['analysis_type_name'],
                    $row['test_method_label'],
                    $row['bone_site_label'],
                    $row['sex_label'],
                    $row['age'],
                    $row['x_value'],
                    $row['y_value'],
                    $row['size_value'],
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function render(GeneBrowserDataService $dataService)
    {
        $searchTerm = trim((string) $this->query);

        $animalModels = AnimalModel::query()
            ->with([
                'gene',
                'geneticCategory',
                'geneticMethod',
                'animalLine',
                'study.user',
                'study.sampleGroups',
                'study.analysisUploads.analysisType',
                'study.analysisUploads.specimenType',
                'study.analysisUploads.region',
                'study.analysisUploads.mechanicalTestingDetails',
            ])
            ->whereNotNull('gene_id')
            ->whereNotNull('study_id')
            ->whereHas('study', fn (Builder $query) => $query->publiclyVisible())
            ->when($searchTerm !== '', function (Builder $query) use ($searchTerm): void {
                $lowerSearch = strtolower($searchTerm);
                $like = "%{$lowerSearch}%";

                $query->whereHas('gene', function (Builder $geneQuery) use ($like): void {
                    $geneQuery->where(function (Builder $nested) use ($like): void {
                        $nested->whereRaw('LOWER(symbol) LIKE ?', [$like])
                            ->orWhereRaw('LOWER(name) LIKE ?', [$like])
                            ->orWhereRaw('LOWER(alliance_id) LIKE ?', [$like]);
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

        // Scatter scans all comparison stats; skip it on the study-browser tab.
        $scatterPayload = ['filters' => $this->scatterFilters()];
        if ($this->activeTab === 'table-view') {
            $scatterPayload = $dataService->build($this->scatterFilters());
            $this->syncScatterState($scatterPayload['filters']);
        }

        return view('livewire.datacenter.browser.gene-browser', [
            'animalModels' => $animalModels,
            'scatterData' => $scatterPayload,
        ]);
    }

    private function scatterFilters(): array
    {
        return [
            'query' => $this->query,
            'selectedAnalysisType' => $this->selectedAnalysisType,
            'selectedXAxisMeasurementId' => $this->selectedXAxisMeasurementId,
            'selectedYAxisMeasurementId' => $this->selectedYAxisMeasurementId,
            'selectedSizeMeasurementId' => $this->selectedSizeMeasurementId,
            'selectedTestMethod' => $this->selectedTestMethod,
            'selectedBoneSite' => $this->selectedBoneSite,
            'selectedSex' => $this->selectedSex,
            'selectedAgeBucket' => $this->selectedAgeBucket,
        ];
    }

    private function syncScatterState(array $filters): void
    {
        $this->selectedAnalysisType = $filters['selectedAnalysisType'];
        $this->selectedXAxisMeasurementId = $filters['selectedXAxisMeasurementId'];
        $this->selectedYAxisMeasurementId = $filters['selectedYAxisMeasurementId'];
        $this->selectedSizeMeasurementId = $filters['selectedSizeMeasurementId'];
        $this->selectedTestMethod = $filters['selectedTestMethod'];
        $this->selectedBoneSite = $filters['selectedBoneSite'];
        $this->selectedSex = $filters['selectedSex'];
        $this->selectedAgeBucket = $filters['selectedAgeBucket'];
    }
}
