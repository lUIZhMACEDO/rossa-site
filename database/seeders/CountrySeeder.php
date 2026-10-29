<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $file = database_path('data/countries_2025.csv');
        $countries = array_map('str_getcsv', file($file));
        $header = array_shift($countries);

        foreach ($countries as $country) {
            Country::firstOrCreate([
                'name' => $country[0],
            ], [
                'code' => $country[1],
            ]);
        }
    }
}
