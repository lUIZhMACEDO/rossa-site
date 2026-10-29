<?php

namespace App\Livewire;

use App\Models\ComparisonGroup;
use App\Services\StudySummaryDataService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class MeasurementModal extends Component
{
    // STATE - owned by this component (not parent)
    public ?int $measurementId = null;

    // measurement list for navigation
    public array $cgMeasurements = [];

    // PROPS - passed from parent
    public ?int $comparisonGroupId = null;

    public Collection $analysisUploadIds;

    public ?array $filterContext = null;

    public function mount(
        ?int $comparisonGroupId = null,
        ?Collection $analysisUploadIds = null,
        ?array $filterContext = null
    ): void {
        $this->comparisonGroupId = $comparisonGroupId;
        $this->analysisUploadIds = $analysisUploadIds ?? collect();
        $this->filterContext = $filterContext;
    }

    // listen for openMeasurementModal event from parent/stats-table
    #[On('openMeasurementModal')]
    public function open(int $measurementId, array $cgMeasurements = []): void
    {
        $this->measurementId = $measurementId;

        // store measurements if provided
        if (! empty($cgMeasurements)) {
            $this->cgMeasurements = $cgMeasurements;
        }

        $this->dispatch('show-measurement-modal');
    }

    public function close(): void
    {
        $this->measurementId = null;
    }

    public function next(): void
    {
        $measurements = $this->cgMeasurements;
        $currentIndex = collect($measurements)->search(fn ($m) => $m['id'] === $this->measurementId);

        if ($currentIndex !== false && $currentIndex < count($measurements) - 1) {
            $this->measurementId = $measurements[$currentIndex + 1]['id'];
        }
    }

    public function prev(): void
    {
        $measurements = $this->cgMeasurements;
        $currentIndex = collect($measurements)->search(fn ($m) => $m['id'] === $this->measurementId);

        if ($currentIndex !== false && $currentIndex > 0) {
            $this->measurementId = $measurements[$currentIndex - 1]['id'];
        }
    }

    #[Computed]
    public function modalPayload(): ?array
    {
        if (! $this->measurementId || ! $this->comparisonGroupId) {
            return null;
        }

        $comparisonGroup = ComparisonGroup::find($this->comparisonGroupId);
        if (! $comparisonGroup) {
            return null;
        }

        /** @var StudySummaryDataService $service */
        $service = app(StudySummaryDataService::class);

        return $service->buildModalPayload(
            $this->measurementId,
            $this->comparisonGroupId,
            $this->analysisUploadIds,
            $this->filterContext,
            $this->cgMeasurements
        );
    }

    #[Computed]
    public function currentIndex(): int
    {
        $index = collect($this->cgMeasurements)->search(fn ($m) => $m['id'] === $this->measurementId);

        return $index !== false ? (int) $index : 0;
    }

    #[Computed]
    public function hasPrev(): bool
    {
        return $this->currentIndex > 0;
    }

    #[Computed]
    public function hasNext(): bool
    {
        return $this->currentIndex < count($this->cgMeasurements) - 1;
    }

    public function render()
    {
        return view('livewire.measurement-modal');
    }
}
