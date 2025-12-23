<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-1 week', '+2 weeks');
        $end = (clone $start)->modify('+1 hour');

        return [
            'psychologist_id' => User::factory()->psychologist(),
            'patient_id' => User::factory(),
            'start_time' => $start,
            'end_time' => $end,
            'meeting_link' => 'https://meet.google.com/' . $this->faker->bothify('???-????-???'),
            'status' => 'scheduled',
            'psychologist_notes' => $this->faker->paragraph(),
        ];
    }
}
