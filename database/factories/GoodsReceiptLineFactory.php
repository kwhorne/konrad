<?php

namespace Database\Factories;

use App\Models\GoodsReceipt;
use App\Models\Product;
use App\Models\PurchaseOrderLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GoodsReceiptLine>
 */
class GoodsReceiptLineFactory extends Factory
{
    public function definition(): array
    {
        $product = Product::factory()->create();

        return [
            'goods_receipt_id' => GoodsReceipt::factory(),
            'purchase_order_line_id' => null,
            'product_id' => $product->id,
            'description' => $product->name,
            'quantity_ordered' => 0,
            'quantity_received' => fake()->randomFloat(2, 1, 100),
            'unit_cost' => fake()->randomFloat(4, 10, 500),
            'sort_order' => 0,
        ];
    }

    public function fromPurchaseOrderLine(PurchaseOrderLine $line): static
    {
        return $this->state(fn (array $attributes) => [
            'purchase_order_line_id' => $line->id,
            'product_id' => $line->product_id,
            'description' => $line->description,
            'quantity_ordered' => $line->quantity,
            'unit_cost' => $line->unit_price,
        ]);
    }

    public function withProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
            'description' => $product->name,
        ]);
    }

    public function fullReceipt(): static
    {
        return $this->state(function (array $attributes) {
            $ordered = $attributes['quantity_ordered'] ?? 100;

            return [
                'quantity_ordered' => $ordered,
                'quantity_received' => $ordered,
            ];
        });
    }

    public function partialReceipt(float $received = 50): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity_ordered' => 100,
            'quantity_received' => $received,
        ]);
    }

    public function overReceipt(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity_ordered' => 100,
            'quantity_received' => 120,
        ]);
    }
}
