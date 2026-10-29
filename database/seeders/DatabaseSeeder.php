<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Database\Seeders\Measurements\MeasurementSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Core ROSSA Functionality & References
            RoleSeeder::class,
            CountrySeeder::class,
            SpecimenTypeSeeder::class,

            // Contains all general and instrument-specific measurements
            MeasurementSeeder::class,

            // Genetic Methods
            GeneticMethodSeeder::class,

            // Ontology Preloads
            OlsSeeder::class,

            // Subscription Types
            SubscriptionSeeder::class,

            VehicleSeeder::class,

            // Other Intervention Types
            InterventionTaxonomySeeder::class,
        ]);
    }
}
