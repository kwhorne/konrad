<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockLevel>
 */
class StockLevelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'stock_location_id' => StockLocation::factory(),
            'quantity_on_hand' => fake()->randomFloat(2, 0, 1000),
            'quantity_reserved' => 0,
            'average_cost' => fake()->randomFloat(4, 10, 500),
            'last_counted_at' => null,
        ];
    }

    public function withStock(float $quantity = 100): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity_on_hand' => $quantity,
        ]);
    }

    public function withReservation(float $reserved): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity_reserved' => $reserved,
        ]);
    }

    public function empty(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity_on_hand' => 0,
            'quantity_reserved' => 0,
        ]);
    }

    public function counted(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_counted_at' => now(),
        ]);
    }
}
