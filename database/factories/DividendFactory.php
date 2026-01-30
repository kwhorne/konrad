<?php

namespace Database\Factories;

use App\Models\ShareClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Dividend>
 */
class DividendFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = fake()->numberBetween(2020, 2025);

        return [
            'fiscal_year' => $year,
            'declaration_date' => fake()->dateTimeBetween("{$year}-01-01", "{$year}-06-30"),
            'record_date' => fake()->dateTimeBetween("{$year}-03-01", "{$year}-06-30"),
            'payment_date' => fake()->dateTimeBetween("{$year}-04-01", "{$year}-07-31"),
            'share_class_id' => ShareClass::factory(),
            'amount_per_share' => fake()->randomFloat(4, 0.5, 50),
            'total_amount' => fake()->randomFloat(2, 10000, 1000000),
            'dividend_type' => 'ordinary',
            'status' => 'declared',
            'created_by' => User::factory(),
        ];
    }

    public function ordinary(): static
    {
        return $this->state(fn (array $attributes) => [
            'dividend_type' => 'ordinary',
        ]);
    }

    public function extraordinary(): static
    {
        return $this->state(fn (array $attributes) => [
            'dividend_type' => 'extraordinary',
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }
}
