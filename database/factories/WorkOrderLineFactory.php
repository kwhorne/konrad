<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkOrderLine>
 */
class WorkOrderLineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $lineType = fake()->randomElement(['time', 'product']);

        return [
            'work_order_id' => WorkOrder::factory(),
            'line_type' => $lineType,
            'product_id' => $lineType === 'product' ? Product::factory() : null,
            'description' => fake()->sentence(4),
            'quantity' => fake()->randomFloat(2, 0.5, 8),
            'unit_price' => fake()->randomFloat(2, 100, 2000),
            'discount_percent' => fake()->boolean(20) ? fake()->randomFloat(2, 1, 15) : 0,
            'performed_at' => $lineType === 'time' ? fake()->dateTimeBetween('-30 days', 'now') : null,
            'performed_by' => $lineType === 'time' ? User::factory() : null,
            'sort_order' => 0,
        ];
    }

    public function timeEntry(): static
    {
        return $this->state(fn (array $attributes) => [
            'line_type' => 'time',
            'product_id' => null,
            'performed_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'performed_by' => User::factory(),
        ]);
    }

    public function productEntry(): static
    {
        return $this->state(fn (array $attributes) => [
            'line_type' => 'product',
            'product_id' => Product::factory(),
            'performed_at' => null,
            'performed_by' => null,
        ]);
    }

    public function withDiscount(float $percent = 10): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_percent' => $percent,
        ]);
    }
}
