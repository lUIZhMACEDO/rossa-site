<?php

namespace App\Livewire\Collections;

use App\Enums\PermissionType;
use App\Models\Collection;
use App\Services\Collections\ComparisonGroupGenerator;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class ComparisonGroupGeneratorButton extends Component
{
    public Collection $collection;

    // Editable: which sample-group attribute pairs experimental with control.
    public string $pairBy = 'sex';

    public ?array $result = null;

    public ?string $error = null;

    public function mount(Collection $collection)
    {
        Gate::authorize(PermissionType::MANAGE_COLLECTIONS->value);
        $this->collection = $collection;
    }

    private function buildScheme(): array
    {
        return ['pair_by' => $this->pairBy];
    }

    /**
     * Preview of the comparisons made per study, using placeholder values.
     */
    public function getPreviewProperty(): array
    {
        $readable = ['M' => 'Male', 'F' => 'Female'];

        // The pairing values shown depend on pair_by; sex is the only supported
        // dimension for now, so preview its two values.
        $values = $this->pairBy === 'sex' ? ['M', 'F'] : [];

        $rows = [];
        foreach ($values as $v) {
            $label = $readable[$v] ?? $v;
            $rows[] = [
                'pair' => $label,
                'control' => "Adora2b WT {$label}",
                'experimental' => "Adora2b KO {$label}",
            ];
        }

        return $rows;
    }

    public function runGeneration()
    {
        Gate::authorize(PermissionType::MANAGE_COLLECTIONS->value);

        $this->result = null;
        $this->error = null;

        try {
            $generator = new ComparisonGroupGenerator($this->buildScheme());
            $this->result = $generator->generate($this->collection->fresh());
        } catch (\Throwable $e) {
            $this->error = get_class($e).': '.$e->getMessage();
            report($e);
        }
    }

    public function render()
    {
        return view('livewire.collections.comparison-group-generator-button');
    }
}
