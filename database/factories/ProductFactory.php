<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####-???')),
            'description' => fake()->optional()->paragraph(),
            'product_group_id' => null,
            'product_type_id' => \App\Models\ProductType::factory(),
            'unit_id' => \App\Models\Unit::factory(),
            'price' => fake()->randomFloat(2, 10, 10000),
            'cost_price' => fake()->optional()->randomFloat(2, 5, 5000),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withGroup(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_group_id' => \App\Models\ProductGroup::factory(),
        ]);
    }
}
