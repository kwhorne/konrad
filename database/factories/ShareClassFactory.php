<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ShareClass>
 */
class ShareClassFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Ordinære aksjer', 'A-aksjer', 'B-aksjer', 'Preferanseaksjer']),
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'par_value' => fake()->randomFloat(2, 1, 100),
            'total_shares' => fake()->numberBetween(100, 100000),
            'has_voting_rights' => true,
            'has_dividend_rights' => true,
            'voting_weight' => 1.00,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function withoutVotingRights(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_voting_rights' => false,
            'voting_weight' => 0,
        ]);
    }

    public function withoutDividendRights(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_dividend_rights' => false,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
