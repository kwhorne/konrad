<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockLocation>
 */
class StockLocationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('LOC-###'),
            'name' => fake()->randomElement(['Hovedlager', 'Utelager', 'Lager A', 'Lager B', 'Reservelager']),
            'description' => fake()->optional()->sentence(),
            'address' => fake()->optional()->streetAddress(),
            'parent_id' => null,
            'location_type' => 'warehouse',
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function warehouse(): static
    {
        return $this->state(fn (array $attributes) => [
            'location_type' => 'warehouse',
        ]);
    }

    public function bin(): static
    {
        return $this->state(fn (array $attributes) => [
            'location_type' => 'bin',
            'code' => fake()->unique()->bothify('BIN-###'),
            'name' => 'Hylle '.fake()->bothify('?-##'),
        ]);
    }

    public function zone(): static
    {
        return $this->state(fn (array $attributes) => [
            'location_type' => 'zone',
            'code' => fake()->unique()->bothify('ZONE-###'),
            'name' => 'Sone '.fake()->randomLetter(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
