<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TaxAdjustment>
 */
class TaxAdjustmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $accountingAmount = fake()->randomFloat(2, 0, 100000);
        $taxAmount = fake()->randomFloat(2, 0, 100000);

        return [
            'fiscal_year' => fake()->numberBetween(2020, 2025),
            'adjustment_type' => fake()->randomElement(['permanent', 'temporary_deductible', 'temporary_taxable']),
            'category' => fake()->randomElement(['entertainment', 'fines', 'depreciation_difference', 'provisions', 'other']),
            'description' => fake()->sentence(),
            'accounting_amount' => $accountingAmount,
            'tax_amount' => $taxAmount,
            'difference' => $accountingAmount - $taxAmount,
            'created_by' => User::factory(),
        ];
    }

    public function permanent(): static
    {
        return $this->state(fn (array $attributes) => [
            'adjustment_type' => 'permanent',
        ]);
    }

    public function temporaryDeductible(): static
    {
        return $this->state(fn (array $attributes) => [
            'adjustment_type' => 'temporary_deductible',
            'category' => 'provisions',
        ]);
    }

    public function temporaryTaxable(): static
    {
        return $this->state(fn (array $attributes) => [
            'adjustment_type' => 'temporary_taxable',
            'category' => 'unrealized_gains',
        ]);
    }

    public function forYear(int $year): static
    {
        return $this->state(fn (array $attributes) => [
            'fiscal_year' => $year,
        ]);
    }

    public function withDifference(float $accountingAmount, float $taxAmount): static
    {
        return $this->state(fn (array $attributes) => [
            'accounting_amount' => $accountingAmount,
            'tax_amount' => $taxAmount,
            'difference' => $accountingAmount - $taxAmount,
        ]);
    }

    public function depreciationDifference(): static
    {
        return $this->state(fn (array $attributes) => [
            'adjustment_type' => 'permanent',
            'category' => 'depreciation_difference',
        ]);
    }
}
