<?php

namespace App\Console\Commands;

use App\Enums\AnalysisTypeEnum;
use App\Enums\CoreFacilityStatus;
use App\Models\CoreFacility;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Console\Command;

class ExampleCoreFacilities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cf:seed {number} {email} {--truncate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the example core facilities. Example: seed:core-facilities 10';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // truncate if --truncate is passed
        if ($this->option('truncate')) {
            CoreFacility::truncate();
        }

        // get user by email
        $user = User::where('email', $this->argument('email'))->first();
        if (! $user) {
            $this->error('User not found');

            return;
        }

        $count = $this->argument('number');
        $faker = Faker::create();

        // links for testing
        $testLinks = [
            'https://www.google.com/', // 200
            'https://example.com/non-existent-page-12345', // 404
            'https://httpbin.org/status/500', // 500
            'https://non-existent-domain-abcde12345.com/', // connection failed
            'https://httpbin.org/status/300', // 300
        ];

        // make loading bar
        $bar = $this->output->createProgressBar($count);

        for ($i = 0; $i < $count; $i++) {
            // get random analysis types
            $availableTypes = AnalysisTypeEnum::getStandardTypes();
            $randomTypes = collect($availableTypes)->random(rand(1, 3))->values()->all();

            // make random core facility
            $facility = new CoreFacility([
                'facility_name' => $faker->company.' Facility',
                'web_address' => $testLinks[array_rand($testLinks)],
                'institution' => $faker->company,
                'contact_name' => $faker->name,
                'contact_email' => $faker->safeEmail,
                'analysis_types' => $randomTypes,
            ]);
            $facility->user_id = $user->id;
            $facility->status = CoreFacilityStatus::PENDING;
            $facility->last_refreshed_at = now();
            $facility->save();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Successfully seeded $count core facilities.");
    }
}
