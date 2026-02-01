<?php

namespace Database\Factories;

use App\Models\AnnualAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CashFlowStatement>
 */
class CashFlowStatementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $profitBeforeTax = fake()->randomFloat(2, 100000, 5000000);
        $openingCash = fake()->randomFloat(2, 50000, 500000);

        return [
            'annual_account_id' => AnnualAccount::factory(),
            'profit_before_tax' => $profitBeforeTax,
            'tax_paid' => $profitBeforeTax * 0.22,
            'depreciation' => fake()->randomFloat(2, 10000, 200000),
            'change_in_inventory' => fake()->randomFloat(2, -50000, 50000),
            'change_in_receivables' => fake()->randomFloat(2, -100000, 100000),
            'change_in_payables' => fake()->randomFloat(2, -50000, 50000),
            'other_operating_items' => 0,
            'purchase_of_fixed_assets' => fake()->randomFloat(2, 0, 500000),
            'sale_of_fixed_assets' => fake()->randomFloat(2, 0, 100000),
            'purchase_of_investments' => 0,
            'sale_of_investments' => 0,
            'other_investing_items' => 0,
            'proceeds_from_borrowings' => fake()->randomFloat(2, 0, 1000000),
            'repayment_of_borrowings' => fake()->randomFloat(2, 0, 300000),
            'share_capital_increase' => 0,
            'dividends_paid' => fake()->randomFloat(2, 0, 200000),
            'other_financing_items' => 0,
            'opening_cash_balance' => $openingCash,
            'created_by' => User::factory(),
        ];
    }

    public function withProfitBeforeTax(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'profit_before_tax' => $amount,
            'tax_paid' => $amount * 0.22,
        ]);
    }

    public function withOpeningBalance(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'opening_cash_balance' => $amount,
        ]);
    }

    public function zeroed(): static
    {
        return $this->state(fn (array $attributes) => [
            'profit_before_tax' => 0,
            'tax_paid' => 0,
            'depreciation' => 0,
            'change_in_inventory' => 0,
            'change_in_receivables' => 0,
            'change_in_payables' => 0,
            'other_operating_items' => 0,
            'purchase_of_fixed_assets' => 0,
            'sale_of_fixed_assets' => 0,
            'purchase_of_investments' => 0,
            'sale_of_investments' => 0,
            'other_investing_items' => 0,
            'proceeds_from_borrowings' => 0,
            'repayment_of_borrowings' => 0,
            'share_capital_increase' => 0,
            'dividends_paid' => 0,
            'other_financing_items' => 0,
            'opening_cash_balance' => 0,
        ]);
    }
}
