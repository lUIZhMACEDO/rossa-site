<?php

namespace App\Livewire\Collections;

use App\Enums\PermissionType;
use App\Models\Collection;
use App\Services\Collections\SampleGroupGenerator;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class SampleGroupGeneratorButton extends Component
{
    public Collection $collection;

    // Editable scheme, surfaced so it can be reviewed and adjusted before running.
    public int $age = 12;

    public array $sexes = ['M', 'F'];

    public string $nameTemplate = '{gene} {group} {sex}';

    // Two rows: the experimental (KO) and control (WT) definitions.
    public array $groups = [
        ['label' => 'KO', 'model' => 'experimental', 'genotype' => '-/-', 'control' => false],
        ['label' => 'WT', 'model' => 'control',      'genotype' => '+/+', 'control' => true],
    ];

    public ?array $result = null;

    public ?string $error = null;

    public function mount(Collection $collection)
    {
        Gate::authorize(PermissionType::MANAGE_COLLECTIONS->value);
        $this->collection = $collection;
    }

    private function buildScheme(): array
    {
        return [
            'age' => $this->age,
            'name_template' => $this->nameTemplate,
            'split_by' => ['sex' => array_values(array_filter($this->sexes))],
            'groups' => $this->groups,
        ];
    }

    /**
     * Preview of what will be created for one study, using a placeholder gene.
     */
    public function getPreviewProperty(): array
    {
        $rows = [];
        $readable = ['M' => 'Male', 'F' => 'Female'];

        foreach ($this->groups as $group) {
            foreach (array_filter($this->sexes) as $sex) {
                $rows[] = [
                    'name' => strtr($this->nameTemplate, [
                        '{gene}' => 'Adora2b',
                        '{group}' => $group['label'],
                        '{sex}' => $readable[$sex] ?? $sex,
                    ]),
                    'model' => $group['model'],
                    'sex' => $sex,
                    'genotype' => $group['genotype'],
                    'age' => $this->age,
                    'control' => $group['control'],
                ];
            }
        }

        return $rows;
    }

    public function runGeneration()
    {
        Gate::authorize(PermissionType::MANAGE_COLLECTIONS->value);

        $this->result = null;
        $this->error = null;

        try {
            $generator = new SampleGroupGenerator($this->buildScheme());
            $this->result = $generator->generate($this->collection->fresh());
        } catch (\Throwable $e) {
            $this->error = get_class($e).': '.$e->getMessage();
            report($e);
        }
    }

    public function render()
    {
        return view('livewire.collections.sample-group-generator-button');
    }
}
