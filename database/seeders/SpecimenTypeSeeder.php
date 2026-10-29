<?php

namespace Database\Seeders;

use App\Models\SpecimenType;
use Illuminate\Database\Seeder;

class SpecimenTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $specimen_types = [
            ['name' => 'Whole Body', 'specimen_category' => 'bone'],
            ['name' => 'Femur', 'specimen_category' => 'bone'],
            ['name' => 'Tibia', 'specimen_category' => 'bone'],
            ['name' => 'Vertebra', 'specimen_category' => 'bone'],
            // ['name' => 'Blood', 'specimen_category' => 'fluid'],
            ['name' => 'Serum', 'specimen_category' => 'fluid'],
            ['name' => 'Plasma', 'specimen_category' => 'fluid'],
            ['name' => 'Urine', 'specimen_category' => 'fluid'],
        ];

        foreach ($specimen_types as $type) {
            SpecimenType::firstOrCreate(
                ['name' => $type['name']],
                ['specimen_category' => $type['specimen_category']]
            );
        }
    }
}
