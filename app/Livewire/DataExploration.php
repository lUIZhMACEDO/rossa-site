<?php

namespace App\Livewire;

use App\Enums\UploadSteps;
use App\Models\Measurement;
use App\Models\Study;
use App\Support\DataExplorationProfiler;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Livewire\Attributes\Computed;
use Livewire\Component;

class DataExploration extends Component
{
    // CONST
    private const DATA_TYPE_GROUP_STATS = 'groupStats';

    private const DATA_TYPE_BOX_PLOTS = 'boxPlots';

    // STATE
    public Study $study;

    public bool $publicAccess = false;

    public ?string $selectedContext = null;

    public ?int $activeComparisonGroupId = null;

    public string $dataType = self::DATA_TYPE_GROUP_STATS;

    // LIFECYCLE
    public function mount(Study $study, bool $publicAccess = false)
    {
        $this->study = $study;
        $this->publicAccess = $publicAccess;
        $this->study->loadMissing([
            'sampleGroups.animalModel.animalLine',
            'sampleGroups.animalModel.parents.animalLine',
            'sampleGroups.animalModel.geneticCategory',
            'sampleGroups.animalModel.geneticMethod',
            'sampleGroups.drugAdministrationPlan.events.molecule',
            'sampleGroups.gonadectomyProcedure',
        ]);

        $this->initializeState();
    }

    private function initializeState(): void
    {
        $this->dataType = $this->normalizeDataType($this->dataType);
        $this->syncSelectedContextToAvailable();
        $this->syncActiveComparisonGroup();
    }

    // COMPUTED
    // get uploads grouped by context
    #[Computed]
    public function availableContexts()
    {
        $uploads = $this->study->analysisUploads()
            ->with(['analysisType', 'specimenType', 'analyteCategory', 'region', 'mechanicalTestingDetails'])
            ->get();

        // group by context and count
        $grouped = $uploads->groupBy(function ($upload) {
            return json_encode($upload->getContext());
        });

        return $grouped->map(function ($group, $contextJson) {
            $firstUpload = $group->first();
            $context = json_decode($contextJson, true);
            $completeUploads = $group->where('current_step', UploadSteps::COMPLETE->value)->count();
            $incompleteUploads = $group->count() - $completeUploads;

            return [
                'context' => $context,
                'contextJson' => $contextJson,
                'completeCount' => $completeUploads,
                'incompleteCount' => $incompleteUploads,
                'description' => $firstUpload->getContextDescription(),
                'contextParts' => $firstUpload->getContextParts(),
                'upload' => $firstUpload,
            ];
        })->values()
            ->sortBy(fn ($item) => $item['description'])
            ->sortBy(fn ($item) => $item['upload']->analysisType->name)
            ->toArray();
    }

    #[Computed]
    public function studyFigures()
    {
        return $this->study->studyFigures()->orderBy('id')->get();
    }

    // get uploads for the selected context
    #[Computed]
    public function filteredAnalysisUploads()
    {
        $context = $this->selectedContextArray();
        if (! $context) {
            return collect();
        }

        $profiler = $this->profiler();

        return $profiler->profile('context_uploads_load', [
            'study_id' => $this->study->id,
            'context_hash' => md5((string) $this->selectedContext),
        ], function () use ($context) {
            $query = $this->study->analysisUploads()
                ->where('current_step', UploadSteps::COMPLETE->value);

            $this->applyContextFilter($query, $context);

            return $query
                ->with(['measurements', 'analysisType', 'specimenType', 'analyteCategory', 'region'])
                ->get();
        });
    }

    // get filtered measurements (only plottable ones)
    #[Computed]
    public function filteredMeasurements()
    {
        if (! $this->selectedContext) {
            return collect();
        }

        $uploadIds = $this->filteredAnalysisUploads->pluck('id');

        if ($uploadIds->isEmpty()) {
            return collect();
        }

        $profiler = $this->profiler();

        return $profiler->profile('context_measurements_load', [
            'study_id' => $this->study->id,
            'upload_count' => $uploadIds->count(),
            'context_hash' => md5((string) $this->selectedContext),
        ], function () use ($uploadIds) {
            return Measurement::whereHas('measurementValues.sample', function ($q) use ($uploadIds) {
                $q->whereIn('analysis_upload_id', $uploadIds);
            })
                ->where('graph_exclude', false)
                ->get();
        });
    }

    // get filter context
    #[Computed]
    public function filterContext()
    {
        $uploads = $this->filteredAnalysisUploads;

        return $uploads->isNotEmpty() ? $uploads->first()->getContext() : null;
    }

    #[Computed]
    public function comparisonGroups()
    {
        return $this->study->comparisonGroups()->get();
    }

    // convert selected context JSON string to array
    private function selectedContextArray(): ?array
    {
        if (! $this->selectedContext) {
            return null;
        }

        $context = json_decode($this->selectedContext, true);

        return is_array($context) ? $context : null;
    }

    private function applyContextFilter(Builder|HasMany $query, array $context): void
    {
        foreach (['analysis_type_id', 'specimen_type_id', 'analyte_category_id', 'region_id', 'test_method', 'bone_tested'] as $field) {
            if (! array_key_exists($field, $context)) {
                continue;
            }

            if ($context[$field] === null) {
                $query->whereNull($field);
            } else {
                $query->where($field, $context[$field]);
            }
        }
    }

    // UI/TRANSITIONS
    // property binding selectedContext - watching change
    public function updatedSelectedContext(): void
    {
        // validation handled differently - in syncSelectedContextToAvailable()
        $this->transitionAfterContextSelectionChanged();
    }

    // action for setting comparison group
    public function setActiveComparisonGroup(int $comparisonGroupId): void
    {
        // handle validation here
        if ($this->comparisonGroups->contains('id', $comparisonGroupId)) {
            if ($this->profiler()->enabled()) {
                \Log::channel('stats')->info('DE_PERF_UI_ACTION', [
                    'event' => 'comparison_group_switch_request',
                    'study_id' => $this->study->id,
                    'comparison_group_id' => $comparisonGroupId,
                    'context_hash' => md5((string) $this->selectedContext),
                    'component' => 'DataExploration',
                ]);
            }

            $this->transitionToComparisonGroup($comparisonGroupId);
        }
    }

    // property binding dataType - watching change
    public function updatedDataType(string $value): void
    {
        $this->transitionAfterDataTypeChanged($value);
    }

    public function render()
    {
        return view('livewire.data-exploration');
    }

    // TRANSITION HELPERS
    private function transitionAfterContextSelectionChanged(): void
    {
        // context updated already, need to sync/validate
        $this->syncSelectedContextToAvailable();

        // preserve mode/cg - normalize/sync to validate
        $this->dataType = $this->normalizeDataType($this->dataType);
        $this->syncActiveComparisonGroup();
    }

    private function transitionToComparisonGroup(int $comparisonGroupId): void
    {
        $this->activeComparisonGroupId = $comparisonGroupId;
    }

    private function transitionAfterDataTypeChanged(string $value): void
    {
        // data type updated already, need to normalize/validate
        $this->dataType = $this->normalizeDataType($value);
    }

    // VALIDATION/SYNC HELPERS
    // normalize data type (group stats or box plots select)
    private function normalizeDataType(string $value): string
    {
        return in_array($value, [self::DATA_TYPE_GROUP_STATS, self::DATA_TYPE_BOX_PLOTS], true)
            ? $value
            : self::DATA_TYPE_GROUP_STATS;
    }

    // prevent stale/invalid context selection, fallback to first available
    private function syncSelectedContextToAvailable(): void
    {
        $contexts = collect($this->availableContexts);

        if ($contexts->isEmpty()) {
            $this->selectedContext = null;

            return;
        }

        $availableContextJson = $contexts->pluck('contextJson');

        if (! $this->selectedContext || ! $availableContextJson->contains($this->selectedContext)) {
            $this->selectedContext = $availableContextJson->first();
        }
    }

    // prevent stale/invalid comparison group tab state, fallback to first available
    private function syncActiveComparisonGroup(): void
    {
        $comparisonGroupIds = $this->comparisonGroups->pluck('id');

        if ($comparisonGroupIds->isEmpty()) {
            $this->activeComparisonGroupId = null;

            return;
        }

        if (! $comparisonGroupIds->contains($this->activeComparisonGroupId)) {
            $this->activeComparisonGroupId = (int) $comparisonGroupIds->first();
        }
    }

    // SERVICES
    private function profiler(): DataExplorationProfiler
    {
        return app(DataExplorationProfiler::class);
    }
}
