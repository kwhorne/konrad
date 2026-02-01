<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeferredTaxItem>
 */
class DeferredTaxItemFactory extends Factory
{
    public const TAX_RATE = 0.22;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $accountingValue = fake()->randomFloat(2, 0, 500000);
        $taxValue = fake()->randomFloat(2, 0, 500000);
        $temporaryDifference = $accountingValue - $taxValue;

        return [
            'fiscal_year' => fake()->numberBetween(2020, 2025),
            'item_type' => fake()->randomElement(['asset', 'liability']),
            'category' => fake()->randomElement(['fixed_assets', 'receivables', 'provisions', 'inventory', 'other']),
            'description' => fake()->sentence(),
            'accounting_value' => $accountingValue,
            'tax_value' => $taxValue,
            'temporary_difference' => $temporaryDifference,
            'deferred_tax' => abs($temporaryDifference) * self::TAX_RATE,
            'created_by' => User::factory(),
        ];
    }

    public function asset(): static
    {
        return $this->state(fn (array $attributes) => [
            'item_type' => 'asset',
        ]);
    }

    public function liability(): static
    {
        return $this->state(fn (array $attributes) => [
            'item_type' => 'liability',
        ]);
    }

    public function forYear(int $year): static
    {
        return $this->state(fn (array $attributes) => [
            'fiscal_year' => $year,
        ]);
    }

    public function withValues(float $accountingValue, float $taxValue): static
    {
        $temporaryDifference = $accountingValue - $taxValue;

        return $this->state(fn (array $attributes) => [
            'accounting_value' => $accountingValue,
            'tax_value' => $taxValue,
            'temporary_difference' => $temporaryDifference,
            'deferred_tax' => abs($temporaryDifference) * self::TAX_RATE,
        ]);
    }

    public function fixedAssets(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'fixed_assets',
        ]);
    }

    public function provisions(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'provisions',
        ]);
    }
}
