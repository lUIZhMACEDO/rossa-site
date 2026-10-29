<?php

namespace Database\Seeders\Measurements;

use App\Enums\AnalysisTypeEnum;
use App\Models\AnalysisType;
use App\Models\Measurement;
use Illuminate\Database\Seeder;

class MechanicalTestingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Mechanical Testing measurements...');

        $instrument = AnalysisType::firstWhere('code', AnalysisTypeEnum::MECHANICAL_TESTING->value);

        $bending_measurements = [
            [
                'name' => 'yield_load',
                'display_name' => 'Yield Load',
                'unit' => 'N',
                'synonyms' => ['yield_load', 'Yield Load', 'Yield Load (N)', 'yield_load_(N)'],
                'grouping' => 'Bending',
            ],
            [
                'name' => 'maximum_load',
                'display_name' => 'Maximum Load',
                'unit' => 'N',
                'synonyms' => ['maximum_load', 'Maximum Load', 'Maximum Load (N)', 'maximum_load_(N)'],
                'grouping' => 'Bending',
            ],
            [
                'name' => 'bending_stiffness',
                'display_name' => 'Stiffness',
                'unit' => 'N/mm',
                'synonyms' => ['stiffness', 'Stiffness', 'Stiffness (N/mm)', 'stiffness_(N/mm)', 'bending_stiffness'],
                'grouping' => 'Bending',
            ],
            [
                'name' => 'work_to_yield',
                'display_name' => 'Work to Yield',
                'unit' => 'N*mm or mJ',
                'synonyms' => ['work_to_yield', 'Work to Yield', 'Work to Yield (N*mm or mJ)'],
                'grouping' => 'Bending',
            ],
            [
                'name' => 'post_yield_work',
                'display_name' => 'Post-Yield Work',
                'unit' => 'N*mm or mJ',
                'synonyms' => ['post_yield_work', 'Post-Yield Work', 'Post-Yield Work (N*mm or mJ)'],
                'grouping' => 'Bending',
            ],
            [
                'name' => 'total_work',
                'display_name' => 'Total Work',
                'unit' => 'N*mm or mJ',
                'synonyms' => ['total_work', 'Total Work', 'Total Work (N*mm or mJ)'],
                'grouping' => 'Bending',
            ],
            [
                'name' => 'displacement_to_yield',
                'display_name' => 'Displacement to Yield',
                'unit' => 'mm',
                'synonyms' => ['displacement_to_yield', 'Displacement to Yield', 'Displacement to Yield (mm)'],
                'grouping' => 'Bending',
            ],
            [
                'name' => 'postyield_displacement',
                'display_name' => 'Postyield Displacement',
                'unit' => 'mm',
                'synonyms' => ['postyield_displacement', 'Postyield Displacement', 'Postyield Displacement (mm)'],
                'grouping' => 'Bending',
            ],
            [
                'name' => 'total_displacement',
                'display_name' => 'Total Displacement',
                'unit' => 'mm',
                'synonyms' => ['total_displacement', 'Total Displacement', 'Total Displacement (mm)'],
                'grouping' => 'Bending',
            ],
            [
                'name' => 'yield_stress',
                'display_name' => 'Yield Stress',
                'unit' => 'MPa',
                'synonyms' => ['yield_stress', 'Yield Stress', 'Yield Stress (MPa)'],
                'grouping' => 'Bending',
            ],
            [
                'name' => 'ultimate_stress',
                'display_name' => 'Ultimate Stress',
                'unit' => 'MPa',
                'synonyms' => ['ultimate_stress', 'Ultimate Stress', 'Ultimate Stress (MPa)'],
                'grouping' => 'Bending',
            ],
            [
                'name' => 'strain_to_yield',
                'display_name' => 'Strain to Yield',
                'unit' => 'microstrain',
                'synonyms' => ['strain_to_yield', 'Strain to Yield', 'Strain to Yield (microstrain)'],
                'grouping' => 'Bending',
            ],
            [
                'name' => 'total_strain',
                'display_name' => 'Total Strain',
                'unit' => 'microstrain',
                'synonyms' => ['total_strain', 'Total Strain', 'Total Strain (microstrain)'],
                'grouping' => 'Bending',
            ],
            [
                'name' => 'elastic_modulus',
                'display_name' => 'Elastic Modulus',
                'unit' => 'GPa',
                'synonyms' => ['elastic_modulus', 'Elastic Modulus', 'Elastic Modulus (GPa)'],
                'grouping' => 'Bending',
            ],
            [
                'name' => 'resilience',
                'display_name' => 'Resilience',
                'unit' => 'MPa',
                'synonyms' => ['resilience', 'Resilience', 'Resilience (MPa)'],
                'grouping' => 'Bending',
            ],
            [
                'name' => 'toughness',
                'display_name' => 'Toughness',
                'unit' => 'MPa',
                'synonyms' => ['toughness', 'Toughness', 'Toughness (MPa)'],
                'grouping' => 'Bending',
            ],
        ];

        $torsion_measurements = [
            [
                'name' => 'maximum_torque',
                'display_name' => 'Maximum Torque',
                'unit' => 'N*mm',
                'synonyms' => ['maximum_torque', 'Maximum Torque', 'Maximum Torque (N*mm)'],
                'grouping' => 'Torsion',
            ],
            [
                'name' => 'torsional_stiffness',
                'display_name' => 'Torsional Stiffness',
                'unit' => 'N*mm/deg',
                'synonyms' => ['torsional_stiffness', 'Torsional Stiffness', 'Torsional Stiffness (N*mm/deg)'],
                'grouping' => 'Torsion',
            ],
            [
                'name' => 'work_to_fracture',
                'display_name' => 'Work to Fracture',
                'unit' => 'N*mm*Deg',
                'synonyms' => ['work_to_fracture', 'Work to Fracture', 'Work to Fracture (N*mm*Deg)'],
                'grouping' => 'Torsion',
            ],
            [
                'name' => 'rotation_at_failure',
                'display_name' => 'Rotation at Failure',
                'unit' => 'Deg',
                'synonyms' => ['rotation_at_failure', 'Rotation at Failure', 'Rotation at Failure (Deg)'],
                'grouping' => 'Torsion',
            ],
            [
                'name' => 'ultimate_shear_strength',
                'display_name' => 'Ultimate Shear Strength',
                'unit' => 'MPa',
                'synonyms' => ['ultimate_shear_strength', 'Ultimate Shear Strength', 'Ultimate Shear Strength (MPa)'],
                'grouping' => 'Torsion',
            ],
            [
                'name' => 'shear_modulus',
                'display_name' => 'Shear Modulus',
                'unit' => 'GPa',
                'synonyms' => ['shear_modulus', 'Shear Modulus', 'Shear Modulus (GPa)'],
                'grouping' => 'Torsion',
            ],
        ];

        $compression_measurements = [
            [
                'name' => 'yield_force',
                'display_name' => 'Yield Force',
                'unit' => 'N',
                'synonyms' => ['yield_force', 'Yield Force', 'Yield Force (N)'],
                'grouping' => 'Compression',
            ],
            [
                'name' => 'ultimate_force',
                'display_name' => 'Ultimate Force',
                'unit' => 'N',
                'synonyms' => ['ultimate_force', 'Ultimate Force', 'Ultimate Force (N)'],
                'grouping' => 'Compression',
            ],
            [
                'name' => 'compression_stiffness',
                'display_name' => 'Stiffness',
                'unit' => 'N/mm',
                'synonyms' => ['stiffness', 'Stiffness', 'Stiffness (N/mm)', 'compression_stiffness'],
                'grouping' => 'Compression',
            ],
            [
                'name' => 'displacement_at_ultimate_force',
                'display_name' => 'Displacement at Ultimate Force',
                'unit' => 'mm',
                'synonyms' => ['displacement_at_ultimate_force', 'Displacement at Ultimate Force', 'Displacement at Ultimate Force (mm)'],
                'grouping' => 'Compression',
            ],
            [
                'name' => 'energy_to_ultimate_force',
                'display_name' => 'Energy to Ultimate Force',
                'unit' => 'N*mm',
                'synonyms' => ['energy_to_ultimate_force', 'Energy to Ultimate Force', 'Energy to Ultimate Force (N*mm)'],
                'grouping' => 'Compression',
            ],
        ];

        $mechanical_testing_measurements_count = count($bending_measurements) + count($torsion_measurements) + count($compression_measurements);
        $this->command->info("$mechanical_testing_measurements_count Mechanical Testing measurements found.");

        foreach (array_merge($bending_measurements, $torsion_measurements, $compression_measurements) as $measurementData) {
            Measurement::updateOrCreate(
                [
                    'name' => $measurementData['name'],
                    'analysis_type_id' => $instrument->id,
                    'specimen_type_id' => null,
                ],
                [
                    'display_name' => $measurementData['display_name'],
                    'unit' => $measurementData['unit'],
                    'synonyms' => $measurementData['synonyms'],
                    'grouping' => $measurementData['grouping'] ?? null,
                ]
            );
        }

        $this->command->info('Mechanical Testing measurements seeded successfully.');
    }
}
