<?php

namespace Database\Seeders;

use App\Models\InterventionCategory;
use App\Models\InterventionSpecificType;
use Illuminate\Database\Seeder;

class InterventionTaxonomySeeder extends Seeder
{
    /**
     * Taxonomy as designed and tested against real scenarios (radiation,
     * treadmill exercise, fecal transplant, lactation, high-fat diet,
     * circadian disruption, and a fully novel case).
     */
    protected array $taxonomy = [
        'Mechanical / Loading' => [
            'Treadmill Exercise',
            'Resistance Exercise',
            'Limb Loading',
            'Hindlimb Unloading (Disuse)',
            'Whole-Body Vibration',
        ],
        'Microbiome / Gut' => [
            'Fecal Microbiota Transplant',
            'Probiotic Treatment',
            'Prebiotic Treatment',
            'Antibiotic-Induced Depletion',
            'Germ-Free / Conventionalization',
        ],
        'Nutrition / Diet' => [
            'Caloric Restriction',
            'High-Fat / Obesogenic Diet',
            'Vitamin D / Calcium Modified Diet',
            'Protein Restriction',
        ],
        'Radiation / Physical Stress' => [
            'Whole-Body Irradiation',
            'Localized Irradiation',
            'Hyperbaric / Hypoxic Exposure',
            'Cold / Heat Exposure',
        ],
        'Reproductive / Maternal' => [
            'Lactation',
            'Pregnancy / Gestational',
        ],
        'Cell-Based' => [
            'Cell Transplantation',
            'Bone Marrow Ablation',
        ],
        'Chronobiology' => [
            'Circadian Disruption (Light Cycle Shift)',
            'Sleep Deprivation',
        ],
        'Environmental / Husbandry' => [
            'Simulated Microgravity / Spaceflight',
            'Altered Housing Density',
            'Altered Ambient Temperature',
        ],
    ];

    public function run(): void
    {
        $categorySort = 0;

        foreach ($this->taxonomy as $categoryName => $specificTypes) {
            $category = InterventionCategory::updateOrCreate(
                ['name' => $categoryName],
                ['sort_order' => $categorySort]
            );
            $categorySort += 10;

            $typeSort = 0;
            foreach ($specificTypes as $typeName) {
                InterventionSpecificType::updateOrCreate(
                    ['intervention_category_id' => $category->id, 'name' => $typeName],
                    ['sort_order' => $typeSort, 'is_other' => false]
                );
                $typeSort += 10;
            }

            // Every real category gets its own "Other" escape hatch, always last.
            InterventionSpecificType::updateOrCreate(
                ['intervention_category_id' => $category->id, 'name' => 'Other (describe below)'],
                ['sort_order' => 9999, 'is_other' => true]
            );
        }

        // Standalone "Other" category, for interventions that don't fit any
        // category above. Its only specific type is itself "Other".
        $otherCategory = InterventionCategory::updateOrCreate(
            ['name' => 'Other'],
            ['sort_order' => 9999]
        );

        InterventionSpecificType::updateOrCreate(
            ['intervention_category_id' => $otherCategory->id, 'name' => 'Other (describe below)'],
            ['sort_order' => 0, 'is_other' => true]
        );
    }
}
