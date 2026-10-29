<?php

namespace Database\Seeders;

use App\Models\GeneticCategory;
use App\Models\GeneticMethod;
use Illuminate\Database\Seeder;

class GeneticMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $geneticCategories = [
            [
                'name' => 'Global Knockout/Knockin - Gene Targeting',
                'description' => 'Global targeted knockout or knock-in to alter an endogenous gene\'s function',
                'template' => 'gene_targeting',
                'methods' => [
                    ['name' => 'Knockout', 'abbreviation' => 'KO'],
                    ['name' => 'Knock-in', 'abbreviation' => 'KI'],
                ],
            ],
            [
                'name' => 'Global Knockout - Spontaneous Mutation',
                'description' => 'Spontaneous, Chemical or X-Ray Induced Mutation (randomly generated point mutation or deletion that has been mapped)',
                'template' => 'spontaneous_mutation',
                'methods' => [
                    ['name' => 'Spontaneous Mutation', 'abbreviation' => 'Sm'],
                    ['name' => 'Chemical Mutation', 'abbreviation' => 'Ci'],
                    ['name' => 'X-Ray Induced Mutation', 'abbreviation' => 'XR'],
                ],
            ],
            [
                'name' => 'Global Knockout - Insertional Mutagenesis',
                'description' => 'Gene Trap, Retroviral, or Transposon Mediated Mutagenesis',
                'template' => 'insertional_mutagenesis',
                'methods' => [
                    ['name' => 'Gene Trap', 'abbreviation' => 'Gt'],
                    ['name' => 'Endogenous Retroviral', 'abbreviation' => 'Et'],
                    ['name' => 'Transposon', 'abbreviation' => 'Tp'],
                    ['name' => 'Viral Insertion', 'abbreviation' => 'Vi'],
                ],
            ],
            [
                'name' => 'Conditional Knockout/Knockin',
                'description' => 'Conditional Knockout or Knockin to alter an endogenous gene\'s function in a specific cell lineage typically when intercrossed with Cre transgenic animal (tissue/lineage specific loss of function)',
                'template' => 'conditional_knockout_knockin',
                'methods' => [
                    ['name' => 'Conditional Knockout', 'abbreviation' => 'cKO'],
                    ['name' => 'Conditional Knockin', 'abbreviation' => 'cKI'],
                ],
                'can_cre' => true,
            ],
            [
                'name' => 'Conditional Knockin - Safe Harbor',
                'description' => 'Conditional Knock-in into a "Safe Harbor" site such as the Rosa Locus',
                'template' => 'safe_harbor_knockin',
                'methods' => [
                    ['name' => 'Conditional Knockin - Safe Harbor', 'abbreviation' => 'SHKI'],
                ],
                'can_cre' => true,
            ],
            [
                'name' => 'Standard Transgenic',
                'description' => 'Random Genome Integration of a Transgene (i.e. over expression of a coding sequence controlled by tissue specific regulatory sequences)',
                'template' => 'standard_transgenic',
                'methods' => [
                    ['name' => 'Transgenic', 'abbreviation' => 'Tg'],
                ],
                'can_drug_induce' => true,
            ],
            [
                'name' => 'Custom Animal Model',
                'description' => 'Use for genetically modified animal models that do not fit the predefined categories.',
                'template' => 'custom_animal_model',
                'methods' => [
                    ['name' => 'Custom Animal Model', 'abbreviation' => 'Custom'],
                ],
            ],
            [
                'name' => 'Global - Double Knockout',
                'description' => 'Offspring from crossing two global single-knockout lines, producing double-knockout mice and intermediate genotype combinations.',
                'template' => 'double_knockout',
                'methods' => [
                    ['name' => 'Double Knockout', 'abbreviation' => 'DKO'],
                ],
            ],
        ];

        foreach ($geneticCategories as $category) {
            $gc = GeneticCategory::updateOrCreate(
                ['name' => $category['name']],
                [
                    'template' => $category['template'],
                    'description' => $category['description'],
                    'can_cre' => $category['can_cre'] ?? false,
                    'can_drug_induce' => $category['can_drug_induce'] ?? false,
                ]
            );

            foreach ($category['methods'] as $method) {
                GeneticMethod::updateOrCreate(
                    ['name' => $method['name'], 'genetic_category_id' => $gc->id],
                    [
                        'allele_abbreviation' => $method['abbreviation'],
                        'description' => $method['name'].' method under '.$gc->name,
                    ]
                );
            }
        }
    }
}
