<?php

namespace App\Livewire;

use App\Models\InterventionCategory;
use App\Models\OtherInterventionPlan;
use App\Models\Study;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * @property-read Collection<int, InterventionCategory> $categories
 */
class OtherInterventionPlanForm extends Component
{
    public Study $study;

    // Null when creating a new plan; set when editing an existing one.
    public ?OtherInterventionPlan $plan = null;

    // --- Plan Information fields ---
    public string $planName = '';

    public ?int $animalAgeAtDayZero = null;

    public ?string $description = null;

    // --- Events, one array entry per Event card ---
    public array $events = [];

    public function mount(Study $study, ?int $planId = null): void
    {
        $this->study = $study;

        if ($planId) {
            $plan = OtherInterventionPlan::with('events')->findOrFail($planId);

            if ($plan->study_id !== $study->id) {
                abort(404);
            }

            $this->plan = $plan;
        }

        if ($this->plan) {
            $this->planName = $this->plan->plan_name;
            $this->animalAgeAtDayZero = $this->plan->animal_age_at_day_zero;
            $this->description = $this->plan->description;

            $this->events = $this->plan->events->map(fn ($event) => [
                'id' => $event->id,
                'intervention_category_id' => $event->intervention_category_id,
                'intervention_specific_type_id' => $event->intervention_specific_type_id,
                'category_other' => $event->category_other,
                'specific_type_other' => $event->specific_type_other,
                'name' => $event->name,
                'method_description' => $event->method_description,
                'source_details' => $event->source_details,
                'frequency' => (string) $event->frequency,
                'frequency_unit' => $event->frequency_unit,
                'frequency_unit_other' => $event->frequency_unit_other,
                'start_day' => $event->start_day,
                'end_day' => $event->end_day,
            ])->all();
        }

        if (empty($this->events)) {
            $this->addEvent();
        }
    }

    /**
     * All categories with their specific types eager loaded, used to drive
     * both dropdowns and to resolve "Other" flags without extra queries.
     */
    #[Computed]
    public function categories(): Collection
    {
        return InterventionCategory::with('specificTypes')
            ->orderBy('sort_order')
            ->get();
    }

    public function addEvent(): void
    {
        $firstCategory = $this->categories->first();
        $firstType = $firstCategory?->specificTypes->first();

        $this->events[] = [
            'id' => null,
            'intervention_category_id' => $firstCategory?->id,
            'intervention_specific_type_id' => $firstType?->id,
            'category_other' => '',
            'specific_type_other' => '',
            'name' => '',
            'method_description' => '',
            'source_details' => '',
            'frequency' => '1',
            'frequency_unit' => 'single_dose',
            'frequency_unit_other' => '',
            'start_day' => 0,
            'end_day' => null,
        ];
    }

    public function removeEvent(int $index): void
    {
        if (count($this->events) <= 1) {
            return; // a plan always needs at least one event
        }

        unset($this->events[$index]);
        $this->events = array_values($this->events);
    }

    /**
     * Fires from the Category <select>'s wire:change. Resets Specific Type
     * to the new category's first option (mirrors the mockup's behavior).
     */
    public function categoryChanged(int $index): void
    {
        $categoryId = $this->events[$index]['intervention_category_id'];
        $category = $this->categories->firstWhere('id', (int) $categoryId);
        $firstType = $category?->specificTypes->first();

        $this->events[$index]['intervention_specific_type_id'] = $firstType?->id;
        $this->events[$index]['category_other'] = '';
        $this->events[$index]['specific_type_other'] = '';
    }

    /**
     * True when the event's selected category is the standalone "Other" category.
     */
    public function categoryIsOther(array $event): bool
    {
        $category = $this->categories->firstWhere('id', (int) ($event['intervention_category_id'] ?? null));

        return $category?->name === 'Other';
    }

    /**
     * True when the event's selected specific type is that category's
     * "Other (describe below)" row.
     */
    public function specificTypeIsOther(array $event): bool
    {
        $category = $this->categories->firstWhere('id', (int) ($event['intervention_category_id'] ?? null));
        $type = $category?->specificTypes->firstWhere('id', (int) ($event['intervention_specific_type_id'] ?? null));

        return (bool) $type?->is_other;
    }

    protected function rules(): array
    {
        $rules = [
            'planName' => ['required', 'string', 'max:255'],
            'animalAgeAtDayZero' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'events' => ['required', 'array', 'min:1'],
            'events.*.intervention_category_id' => ['required', 'exists:intervention_categories,id'],
            'events.*.intervention_specific_type_id' => ['nullable', 'exists:intervention_specific_types,id'],
            'events.*.name' => ['required', 'string', 'max:255'],
            'events.*.method_description' => ['required', 'string'],
            'events.*.source_details' => ['nullable', 'string', 'max:255'],
            'events.*.frequency' => ['required', 'numeric', 'min:0'],
            'events.*.frequency_unit' => ['required', 'string'],
            'events.*.frequency_unit_other' => ['nullable', 'string', 'max:255'],
            'events.*.start_day' => ['required', 'integer'],
            'events.*.end_day' => ['nullable', 'integer', 'gte:events.*.start_day'],
        ];

        return $rules;
    }

    /**
     * Conditional "Other" text fields can't be expressed as static rule
     * strings (they depend on a sibling event's category/type selection),
     * so they're validated by hand after the base rules pass.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ($this->events as $index => $event) {
                if ($this->categoryIsOther($event) && blank($event['category_other'] ?? null)) {
                    $validator->errors()->add("events.$index.category_other", 'Please describe the category.');
                }

                if ($this->specificTypeIsOther($event) && blank($event['specific_type_other'] ?? null)) {
                    $validator->errors()->add("events.$index.specific_type_other", 'Please describe the specific type.');
                }

                if ($event['frequency_unit'] === 'other' && blank($event['frequency_unit_other'] ?? null)) {
                    $validator->errors()->add("events.$index.frequency_unit_other", 'Please describe the frequency unit.');
                }
            }
        });
    }

    public function save()
    {
        $this->validate();

        DB::transaction(function () {
            $plan = OtherInterventionPlan::updateOrCreate(
                ['id' => $this->plan?->id],
                [
                    'study_id' => $this->study->id,
                    'plan_name' => $this->planName,
                    'description' => $this->description,
                    'animal_age_at_day_zero' => $this->animalAgeAtDayZero,
                ]
            );

            $keptEventIds = [];

            foreach ($this->events as $order => $event) {
                $savedEvent = $plan->events()->updateOrCreate(
                    ['id' => $event['id']],
                    [
                        'intervention_category_id' => $event['intervention_category_id'],
                        'intervention_specific_type_id' => $event['intervention_specific_type_id'],
                        'category_other' => $this->categoryIsOther($event) ? $event['category_other'] : null,
                        'specific_type_other' => $this->specificTypeIsOther($event) ? $event['specific_type_other'] : null,
                        'name' => $event['name'],
                        'method_description' => $event['method_description'],
                        'source_details' => $event['source_details'],
                        'frequency' => $event['frequency'],
                        'frequency_unit' => $event['frequency_unit'],
                        'frequency_unit_other' => $event['frequency_unit'] === 'other' ? $event['frequency_unit_other'] : null,
                        'start_day' => $event['start_day'],
                        'end_day' => $event['end_day'],
                        'event_order' => $order,
                    ]
                );

                $keptEventIds[] = $savedEvent->id;
            }

            // Remove events that existed on the plan but were deleted in this form submission.
            $plan->events()->whereNotIn('id', $keptEventIds)->delete();

            $this->plan = $plan;
        });

        return redirect()
            ->route('study.animal-experimentation.other-intervention', $this->study)
            ->with('success', $this->plan->wasRecentlyCreated
                ? 'Intervention plan created successfully.'
                : 'Intervention plan updated successfully.');
    }

    public function cancel()
    {
        return redirect()->route('study.animal-experimentation.other-intervention', $this->study);
    }

    public function render()
    {
        return view('livewire.other-intervention-plan-form');
    }
}
