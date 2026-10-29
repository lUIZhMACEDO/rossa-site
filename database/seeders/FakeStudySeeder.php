<?php

namespace Database\Seeders;

use App\Models\AnalysisType;
use App\Models\MeasurementValue;
use App\Models\Sample;
use App\Models\Study;
use App\Models\User;
use Database\Factories\AnalysisUploadFactory;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FakeStudySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Start a faker instance
        $faker = Factory::create();

        // Ensure the user to tie studies to exists
        $user = User::firstOrCreate([
            'email' => 'test@example.com',
        ], [
            'name' => 'Test User',
            'password' => Hash::make('password123'),
        ]);

        $analysisType = AnalysisType::where('code', 'micro_ct')->firstOrFail();

        $measurements = $analysisType->measurements;

        // Create a header row based on the measurements in the focus
        $headers = $measurements->pluck('display_name')->toArray();

        // Create 10 studies, each with 3 datasheets (uploads) that have the specified headers
        $studies = Study::factory(10)
            ->has(
                AnalysisUploadFactory::new()->count(3)->state(
                    fn (array $attributes, Study $study) => [
                        'headers' => $headers,
                    ]
                ),
                'analysisUploads', // Relationship name on Study model
            )
            ->create([
                'user_id' => $user->id,
            ]);

        // For each study and upload in each study, create 20 samples attached to the upload.
        $studies->each(function ($study) use ($measurements, $faker) {
            $study->analysisUploads->each(function ($upload) use ($measurements, $faker) {
                $samples = Sample::factory(20)->create(
                    [
                        'raw_upload_data' => $measurements->map(function ($measurement) use ($faker) {
                            $value = $faker->randomFloat(2, 0, 100);
                            if (! $measurement->unit) {
                                $value = $faker->word();
                            }

                            return $value;
                        }),
                        'analysis_upload_id' => $upload->id,
                    ]
                );

                $samples->each(function ($sample) use ($measurements) {
                    collect($sample->data)->each(function ($v, $i) use ($sample, $measurements) {
                        MeasurementValue::create([
                            'sample_id' => $sample->id,
                            'value' => $v,
                            'measurement_id' => $measurements[$i]->id,
                        ]);
                    });
                });
            });
        });
    }
}
