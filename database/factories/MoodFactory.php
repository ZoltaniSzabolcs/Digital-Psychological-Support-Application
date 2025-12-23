<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mood>
 */
class MoodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $score = $this->faker->numberBetween(1, 10);

        // Logic to match emoji to score roughly
        $emoji = match (true) {
            $score >= 8 => '🤩',
            $score >= 6 => '🙂',
            $score >= 4 => '😐',
            $score >= 2 => '😢',
            default => '😭',
        };

        return [
            'user_id' => User::factory(), // Defaults to creating a new user if not passed
            'score' => $score,
            'emoji' => $emoji,
            'tags' => json_encode($this->faker->randomElements(['tired', 'anxious', 'happy', 'stressed', 'energetic'], 2)),
            'suicidal_thought_flag' => false,
            'notes' => $this->faker->sentence(),
            'created_at' => $this->faker->dateTimeBetween('-30 days', 'now'), // Spread over last month
        ];
    }

    // State for Crisis Entry
    public function crisis()
    {
        return $this->state(fn (array $attributes) => [
            'score' => 1,
            'emoji' => '😭',
            'suicidal_thought_flag' => true,
            'notes' => 'I cannot handle this anymore.',
        ]);
    }
}
