<?php

namespace Database\Factories;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Feedback>
 */
class FeedbackFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'feedback' => fake()->paragraph(rand(2, 5)),
            'user_id' => User::factory(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'previous_url' => fake()->url(),
            'resolved' => fake()->boolean(30), // 30% chance of being resolved
        ];
    }

    /**
     * Indicate that the feedback is resolved.
     */
    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'resolved' => true,
        ]);
    }

    /**
     * Indicate that the feedback is unresolved.
     */
    public function unresolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'resolved' => false,
        ]);
    }

    /**
     * Indicate that the feedback has no associated user (anonymous feedback).
     */
    public function anonymous(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }
}
