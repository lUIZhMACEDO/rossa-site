<?php

namespace Database\Factories;

use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Model>
 */
class AnalysisUploadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $uid = $this->faker->uuid();

        return [
            'original_filename' => $uid.'.csv',
            'headers' => json_encode(['header1', 'header2', 'header3']),
            'size' => $this->faker->numberBetween(1000, 1000000),
            'mime_type' => 'text/csv',
            'path' => 'fake/'.$uid.'.csv',
        ];
    }
}
