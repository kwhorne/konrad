<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\VatRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchaseOrderLine>
 */
class PurchaseOrderLineFactory extends Factory
{
    public function definition(): array
    {
        $product = Product::factory()->create();

        return [
            'purchase_order_id' => PurchaseOrder::factory(),
            'product_id' => $product->id,
            'description' => $product->name,
            'quantity' => fake()->randomFloat(2, 1, 100),
            'unit' => 'stk',
            'unit_price' => fake()->randomFloat(2, 50, 5000),
            'discount_percent' => 0,
            'vat_rate_id' => VatRate::first()?->id,
            'vat_percent' => 25,
            'quantity_received' => 0,
            'sort_order' => 0,
        ];
    }

    public function withProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
            'description' => $product->name,
            'unit_price' => $product->cost_price ?? fake()->randomFloat(2, 50, 5000),
        ]);
    }

    public function partiallyReceived(float $received = 50): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => 100,
            'quantity_received' => $received,
        ]);
    }

    public function fullyReceived(): static
    {
        return $this->state(function (array $attributes) {
            $quantity = $attributes['quantity'] ?? 100;

            return [
                'quantity_received' => $quantity,
            ];
        });
    }

    public function withDiscount(float $percent = 10): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_percent' => $percent,
        ]);
    }
}
