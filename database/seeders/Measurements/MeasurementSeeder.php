<?php

namespace Database\Seeders\Measurements;

use App\Models\Measurement;
use Illuminate\Database\Seeder;

class MeasurementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Regions/Zones');
        $this->call([
            RegionSeeder::class,
        ]);
        $this->command->info('Regions/Zones seeded successfully.');

        $this->command->info('Seeding general measurements...');
        $this->seedGeneralMeasurements();
        $this->command->info('Seeding instrument-specific measurements...');

        $this->call([
            AnalysisTypesSeeder::class,
            DexaMeasurementSeeder::class,
            MicroCTMeasurementSeeder::class,
            BoneHistoSeeder::class,
            MechanicalTestingSeeder::class,
            ClinicalChemistrySeeder::class,
        ]);
    }

    public function seedGeneralMeasurements()
    {
        $generalMeasurements = [
            ['name' => 'sample_identifier', 'display_name' => 'Sample Identifier', 'graph_exclude' => true, 'unit' => '', 'synonyms' => ['Sample Identifier', 'Sample ID', 'Sample Name', 'sample_name', 'sample_id', 'id', 'ID']],
            ['name' => 'age', 'display_name' => 'Age', 'graph_exclude' => false, 'unit' => 'weeks', 'synonyms' => ['age', 'Age (weeks)', 'Age in weeks']],
            ['name' => 'weight', 'display_name' => 'Body Weight', 'graph_exclude' => false, 'unit' => 'g', 'synonyms' => ['BW', 'bw', 'Bw', 'BW (g)', 'BW(g)', 'bw (g)', 'bw(g)', 'Body Weight', 'body weight', 'Body weight', 'BODY WEIGHT', 'Body Weight (g)', 'body weight (g)', 'Body Weight(g)', 'Bodyweight', 'bodyweight', 'Bodyweight (g)', 'bodyweight (g)', 'Body Wt', 'body wt', 'Body Wt (g)', 'body wt (g)', 'Wt', 'wt', 'Weight', 'weight', 'Weight (g)', 'weight (g)', 'Animal Weight', 'animal weight', 'Animal Weight (g)']],
        ];

        $this->command->info(count($generalMeasurements).' general measurements found.');

        foreach ($generalMeasurements as $measurementData) {
            Measurement::updateOrCreate(
                ['name' => $measurementData['name']],
                [
                    'display_name' => $measurementData['display_name'],
                    'unit' => $measurementData['unit'],
                    'synonyms' => $measurementData['synonyms'],
                    'graph_exclude' => $measurementData['graph_exclude'],
                ]
            );
        }

        $this->command->info('General measurements seeded successfully.');
    }
}
