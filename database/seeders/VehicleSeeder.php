<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $vehicles = [
            ['name' => 'Saline (0.9% NaCl)',                  'composition' => '0.9% sodium chloride in water'],
            ['name' => 'PBS',                                  'composition' => 'Phosphate-buffered saline'],
            ['name' => 'Acidified saline (PTH vehicle)',       'composition' => 'Saline with 0.1% BSA, ~pH 4'],
            ['name' => 'Citrate buffer',                       'composition' => null],
            ['name' => 'Corn oil',                             'composition' => null],
            ['name' => 'Sesame oil',                           'composition' => null],
            ['name' => '0.5% methylcellulose',                 'composition' => '0.5% methylcellulose in water'],
            ['name' => '0.5% methylcellulose + 0.1% Tween-80', 'composition' => null],
            ['name' => '5% DMSO in corn oil',                  'composition' => null],
            ['name' => '2-hydroxypropyl-β-cyclodextrin',       'composition' => 'HP-β-CD in water'],
            ['name' => 'Sterile water',                        'composition' => null],
        ];

        foreach ($vehicles as $v) {
            Vehicle::firstOrCreate(
                ['name' => $v['name']],
                ['composition' => $v['composition'], 'is_canonical' => true]
            );
        }
    }
}
