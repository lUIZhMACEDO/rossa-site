<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CellOntologySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvPath = database_path('data/cell_types/cell_ontology.csv');

        if (! File::exists($csvPath)) {
            $this->command->error("CSV file not found at: {$csvPath}");

            return;
        }

        $file = fopen($csvPath, 'r');

        // Skip the header row
        $header = fgetcsv($file);

        $cells = [];
        $rowNumber = 1;

        while (($row = fgetcsv($file)) !== false) {
            $rowNumber++;

            // Skip empty rows (rows where first column is empty)
            if (empty($row[0])) {
                continue;
            }

            $cells[] = [
                'cell_name' => $row[0],
                'display_name' => $row[1] ?? null,
                'cl_term_name' => $row[2] ?? null,
                'cl_id' => $row[3] ?? null,
                'ontology_status' => $row[4] ?? null,
                'tooltip' => $row[5] ?? null,
                'group' => $row[6] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        fclose($file);

        // Upsert in chunks to handle large datasets efficiently
        // Upsert based on unique combination of cl_id and cell_name
        foreach (array_chunk($cells, 100) as $chunk) {
            DB::table('cells')->upsert(
                $chunk,
                ['cl_id', 'cell_name'], // Unique keys
                ['display_name', 'cl_term_name', 'ontology_status', 'tooltip', 'group', 'updated_at'] // Columns to update
            );
        }

        $this->command->info('Successfully imported/updated '.count($cells).' cell records.');

        // Create tags from cell groups
        $this->createTagsFromGroups();

        // Create tags from subject area categories
        $this->createSubjectAreaTags();
    }

    /**
     * Create tags based on cell groups and link them to cells
     */
    protected function createTagsFromGroups(): void
    {
        // Get all cells with their groups
        $cells = DB::table('cells')->get();

        $tagsCreated = 0;

        foreach ($cells as $cell) {
            // Upsert tag for each cell in the group
            DB::table('tags')->upsert(
                [
                    'name' => $cell->display_name,
                    'description' => "Cell type group: {$cell->group}",
                    'tab' => 'Cell Types',
                    'cell_id' => $cell->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                ['name', 'cell_id'], // Unique keys
                ['description', 'tab', 'updated_at'] // Columns to update
            );
            $tagsCreated++;
        }

        $this->command->info("Successfully created/updated {$tagsCreated} tags linked to cells.");
    }

    /**
     * Create tags from subject area categories (migrated from Study model)
     */
    protected function createSubjectAreaTags(): void
    {
        $subjectAreas = [
            'Thematic Areas' => [
                'aging' => 'Aging',
                'autophagy' => 'Autophagy',
                'bone_development' => 'Bone Development',
                'bone_metabolism' => 'Bone Metabolism',
                'bone_remodeling' => 'Bone Remodeling',
                'circadian_rhythm' => 'Circadian Rhythm',
                'drug_evaluation' => 'Drug Evaluation',
                'gene_evaluation' => 'Gene Evaluation',
                'gene_regulation' => 'Gene Regulation',
                'inflammation' => 'Inflammation',
                'mechanical_regulation' => 'Mechanical Regulation',
                'microbiome' => 'Microbiome',
                'nutrition' => 'Nutrition',
                'sarcopenia' => 'Sarcopenia',
                'senescence' => 'Senescence',
                'sexual_dimorphism' => 'Sexual Dimorphism',
                'systems_biology' => 'Systems Biology',
            ],
            'Tissue Interactions' => [
                'adipose_tissue' => 'Adipose Tissue',
                'digestive_system' => 'Digestive System',
                'endocrine_system' => 'Endocrine System',
                'immune_system' => 'Immune System',
                'kidney' => 'Kidney',
                'liver' => 'Liver',
                'muscular_system' => 'Muscular System',
                'nervous_system' => 'Nervous System',
                'vascular_system' => 'Vascular System',
            ],
        ];

        $tagsCreated = 0;

        foreach ($subjectAreas as $category => $areas) {
            foreach ($areas as $key => $displayName) {
                // Use updateOrCreate for subject area tags (no database constraint required)
                DB::table('tags')->updateOrInsert(
                    [
                        'name' => $displayName,
                        'tab' => $category,
                        'cell_id' => null,
                    ],
                    [
                        'description' => "Subject area: {$category}",
                        'updated_at' => now(),
                    ]
                );
                $tagsCreated++;
            }
        }

        $this->command->info("Successfully created/updated {$tagsCreated} subject area tags.");
    }
}
