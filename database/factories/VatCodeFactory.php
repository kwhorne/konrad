<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VatCode>
 */
class VatCodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->numerify('##'),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'category' => 'salg_norge',
            'direction' => 'output',
            'rate' => 25,
            'affects_base' => true,
            'affects_tax' => true,
            'sign' => 1,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    public function output(): static
    {
        return $this->state(fn (array $attributes) => [
            'direction' => 'output',
            'category' => 'salg_norge',
            'sign' => 1,
        ]);
    }

    public function input(): static
    {
        return $this->state(fn (array $attributes) => [
            'direction' => 'input',
            'category' => 'kjop_norge',
            'sign' => -1,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
