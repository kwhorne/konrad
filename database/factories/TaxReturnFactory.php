<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TaxReturn>
 */
class TaxReturnFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = fake()->unique()->numberBetween(2000, 2100);
        $accountingProfit = fake()->randomFloat(2, -500000, 2000000);
        $taxableIncome = max(0, $accountingProfit);
        $taxPayable = $taxableIncome * 0.22;

        return [
            'fiscal_year' => $year,
            'period_start' => "{$year}-01-01",
            'period_end' => "{$year}-12-31",
            'accounting_profit' => $accountingProfit,
            'permanent_differences' => fake()->randomFloat(2, -50000, 50000),
            'temporary_differences_change' => fake()->randomFloat(2, -30000, 30000),
            'taxable_income' => $taxableIncome,
            'tax_rate' => 22.00,
            'tax_payable' => $taxPayable,
            'deferred_tax_change' => fake()->randomFloat(2, -10000, 10000),
            'total_tax_expense' => $taxPayable,
            'losses_brought_forward' => 0,
            'losses_used' => 0,
            'losses_carried_forward' => 0,
            'status' => 'draft',
            'created_by' => User::factory(),
        ];
    }

    public function ready(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'ready',
        ]);
    }

    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'submitted',
        ]);
    }

    public function withLosses(float $amount = 100000): static
    {
        return $this->state(fn (array $attributes) => [
            'losses_brought_forward' => $amount,
        ]);
    }
}
