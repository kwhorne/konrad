<?php

namespace Database\Factories;

use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Module>
 */
class ModuleFactory extends Factory
{
    protected $model = Module::class;

    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(2),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'is_premium' => true,
            'price_monthly' => fake()->randomElement([29900, 49900, 99900, 149900]),
            'stripe_price_id' => null,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }

    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_premium' => true,
        ]);
    }

    public function standard(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_premium' => false,
            'price_monthly' => 0,
        ]);
    }
}
