<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockLocation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockReservation>
 */
class StockReservationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'stock_location_id' => StockLocation::factory(),
            'quantity' => fake()->randomFloat(2, 1, 50),
            'reference_type' => 'App\\Models\\OrderLine',
            'reference_id' => fake()->randomNumber(5),
            'status' => 'active',
            'expires_at' => null,
            'created_by' => User::factory(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function fulfilled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'fulfilled',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    public function expiring(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->addDays(7),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }
}
