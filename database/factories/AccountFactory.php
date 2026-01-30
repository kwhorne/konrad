<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $accountNumber = fake()->unique()->numberBetween(1000, 8999);
        $class = (string) floor($accountNumber / 1000);

        return [
            'account_number' => (string) $accountNumber,
            'name' => fake()->words(3, true),
            'account_class' => $class,
            'account_type' => match ($class) {
                '1' => 'asset',
                '2' => fake()->randomElement(['liability', 'equity']),
                '3' => 'revenue',
                '4', '5', '6', '7' => 'expense',
                '8' => fake()->randomElement(['revenue', 'expense']),
                default => 'expense',
            },
            'is_system' => false,
            'is_active' => true,
        ];
    }

    public function asset(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_number' => (string) fake()->unique()->numberBetween(1000, 1999),
            'account_class' => '1',
            'account_type' => 'asset',
        ]);
    }

    public function liability(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_number' => (string) fake()->unique()->numberBetween(2000, 2499),
            'account_class' => '2',
            'account_type' => 'liability',
        ]);
    }

    public function equity(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_number' => (string) fake()->unique()->numberBetween(2500, 2999),
            'account_class' => '2',
            'account_type' => 'equity',
        ]);
    }

    public function revenue(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_number' => (string) fake()->unique()->numberBetween(3000, 3999),
            'account_class' => '3',
            'account_type' => 'revenue',
        ]);
    }

    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_number' => (string) fake()->unique()->numberBetween(4000, 7999),
            'account_class' => (string) fake()->numberBetween(4, 7),
            'account_type' => 'expense',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_system' => true,
        ]);
    }
}
