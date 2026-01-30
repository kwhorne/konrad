<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProjectLine>
 */
class ProjectLineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'product_id' => null,
            'description' => fake()->sentence(4),
            'quantity' => fake()->randomFloat(2, 1, 10),
            'unit_price' => fake()->randomFloat(2, 100, 5000),
            'discount_percent' => fake()->boolean(30) ? fake()->randomFloat(2, 1, 20) : 0,
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
}
