<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'amount' => $this->faker->randomElement([100.00, 150.00, 200.00, 250.00, 300.00]), // Typical RON prices
            'cif_cui' => $this->faker->numerify('RO#######'),
            'series_number' => 'INV-' . $this->faker->unique()->numerify('####'),
            'status' => 'paid',
            'pdf_path' => 'invoices/sample.pdf',
            'created_at' => $this->faker->dateTimeThisYear(),
        ];
    }
}
