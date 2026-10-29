<?php

namespace Database\Seeders\Measurements;

use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Regions...');

        $regions = [
            ['code' => 'cortical', 'label' => 'Cortical'],
            ['code' => 'trabecular', 'label' => 'Trabecular'],
        ];

        foreach ($regions as $regionData) {
            Region::firstOrCreate(
                ['code' => $regionData['code']],
                [
                    'label' => $regionData['label'],
                ]
            );
        }

        $this->command->info('Regions seeded successfully.');
    }
}
