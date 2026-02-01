<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockLocation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockTransaction>
 */
class StockTransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'stock_location_id' => StockLocation::factory(),
            'to_stock_location_id' => null,
            'transaction_type' => 'receipt',
            'quantity' => fake()->randomFloat(2, 1, 100),
            'unit_cost' => fake()->randomFloat(4, 10, 500),
            'total_cost' => null,
            'quantity_before' => 0,
            'quantity_after' => 0,
            'reference_type' => null,
            'reference_id' => null,
            'notes' => fake()->optional()->sentence(),
            'created_by' => User::factory(),
            'is_posted' => true,
            'posted_at' => now(),
            'transaction_date' => now(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function ($transaction) {
            if ($transaction->total_cost === null) {
                $transaction->total_cost = abs($transaction->quantity) * $transaction->unit_cost;
            }
            $transaction->quantity_after = $transaction->quantity_before + $transaction->quantity;
        });
    }

    public function receipt(): static
    {
        return $this->state(fn (array $attributes) => [
            'transaction_type' => 'receipt',
            'quantity' => abs(fake()->randomFloat(2, 1, 100)),
        ]);
    }

    public function issue(): static
    {
        return $this->state(fn (array $attributes) => [
            'transaction_type' => 'issue',
            'quantity' => -abs(fake()->randomFloat(2, 1, 100)),
        ]);
    }

    public function adjustment(): static
    {
        return $this->state(fn (array $attributes) => [
            'transaction_type' => fake()->randomElement(['adjustment_in', 'adjustment_out']),
        ]);
    }

    public function transfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'transaction_type' => 'transfer_out',
            'to_stock_location_id' => StockLocation::factory(),
        ]);
    }

    public function unposted(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_posted' => false,
            'posted_at' => null,
        ]);
    }
}
