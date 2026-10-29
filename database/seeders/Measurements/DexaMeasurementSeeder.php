<?php

namespace Database\Seeders\Measurements;

use App\Enums\AnalysisTypeEnum;
use App\Models\AnalysisType;
use App\Models\Measurement;
use Illuminate\Database\Seeder;

class DexaMeasurementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $this->command->info('Seeding DEXA measurements...');

        $instrument = AnalysisType::firstWhere('code', AnalysisTypeEnum::DEXA->value);

        $dexa_measurements = [
            [
                'name' => 'bmd',
                'display_name' => 'Bone Mineral Density',
                'unit' => 'g/cm^2',
                'synonyms' => ['BMD', 'BMD (g/cm^2)', 'BMD (g/cm2)', 'BMD (g/cm2)', 'Bone Mineral Density'],
                'grouping' => 'DEXA',
            ],
            [
                'name' => 'bmc',
                'display_name' => 'Bone Mineral Content',
                'unit' => 'g',
                'synonyms' => ['BMC', 'BMC (g)', 'BMC (g)', 'Bone Mineral Content'],
                'grouping' => 'DEXA',
            ],
            [
                'name' => 'ba',
                'display_name' => 'Bone Area',
                'unit' => 'cm^2',
                'synonyms' => ['BA', 'BA (cm^2)', 'BA (cm2)', 'Bone Area'],
                'grouping' => 'DEXA',
            ],
            [
                'name' => 'fm',
                'display_name' => 'Fat Mass',
                'unit' => 'g',
                'synonyms' => ['FM', 'FM (g)', 'FM (g)', 'Fat Mass'],
                'grouping' => 'DEXA',
            ],
            [
                'name' => 'lm',
                'display_name' => 'Lean Mass',
                'unit' => 'g',
                'synonyms' => ['LM', 'LM (g)', 'LM (g)', 'Lean Mass'],
                'grouping' => 'DEXA',
            ],
            [
                'name' => 'body_length',
                'display_name' => 'Body Length',
                'unit' => 'cm',
                'synonyms' => ['Body Length', 'Body Length (cm)', 'Body Length (cm)', 'Body Length'],
                'grouping' => 'DEXA',
            ],
        ];

        $dexa_measurements_count = count($dexa_measurements);
        $this->command->info("$dexa_measurements_count DEXA measurements found.");

        foreach ($dexa_measurements as $measurementData) {
            $m = Measurement::updateOrCreate(
                [
                    'name' => $measurementData['name'],
                    'analysis_type_id' => $instrument->id,
                    'specimen_type_id' => null,
                ],
                [
                    'display_name' => $measurementData['display_name'],
                    'unit' => $measurementData['unit'],
                    'synonyms' => $measurementData['synonyms'],
                    'grouping' => $measurementData['grouping'],
                ]
            );

        }

        $this->command->info('DEXA measurements seeded successfully.');
    }
}
