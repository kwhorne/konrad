<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use App\Models\VatRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderLine>
 */
class OrderLineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $vatRate = VatRate::where('is_active', true)->first() ?? VatRate::factory()->create();

        return [
            'order_id' => Order::factory(),
            'quote_line_id' => null,
            'product_id' => null,
            'description' => fake()->sentence(4),
            'quantity' => fake()->randomFloat(2, 1, 10),
            'unit' => fake()->randomElement(['stk', 'timer', 'kg', 'm']),
            'unit_price' => fake()->randomFloat(2, 100, 5000),
            'discount_percent' => fake()->boolean(30) ? fake()->randomFloat(2, 1, 20) : 0,
            'vat_rate_id' => $vatRate->id,
            'vat_percent' => $vatRate->rate,
            'sort_order' => 0,
        ];
    }

    public function withProduct(): static
    {
        return $this->state(function (array $attributes) {
            $product = Product::factory()->create();

            return [
                'product_id' => $product->id,
                'description' => $product->name,
                'unit_price' => $product->price,
            ];
        });
    }

    public function withDiscount(float $percent = 10): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_percent' => $percent,
        ]);
    }

    public function noVat(): static
    {
        return $this->state(fn (array $attributes) => [
            'vat_rate_id' => null,
            'vat_percent' => 0,
        ]);
    }
}
