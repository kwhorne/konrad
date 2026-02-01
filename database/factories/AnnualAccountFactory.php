<?php

namespace Database\Factories;

use App\Models\AnnualAccount;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AnnualAccount>
 */
class AnnualAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = fake()->numberBetween(2020, 2025);
        $revenue = fake()->randomFloat(2, 100000, 50000000);
        $operatingProfit = $revenue * fake()->randomFloat(2, 0.05, 0.20);
        $profitBeforeTax = $operatingProfit - fake()->randomFloat(2, 0, 50000);
        $netProfit = $profitBeforeTax * 0.78;
        $totalAssets = fake()->randomFloat(2, 500000, 30000000);
        $totalEquity = $totalAssets * fake()->randomFloat(2, 0.20, 0.60);
        $totalLiabilities = $totalAssets - $totalEquity;

        return [
            'fiscal_year' => $year,
            'period_start' => Carbon::create($year, 1, 1),
            'period_end' => Carbon::create($year, 12, 31),
            'company_size' => AnnualAccount::SIZE_SMALL,
            'revenue' => $revenue,
            'operating_profit' => $operatingProfit,
            'profit_before_tax' => $profitBeforeTax,
            'net_profit' => $netProfit,
            'total_assets' => $totalAssets,
            'total_equity' => $totalEquity,
            'total_liabilities' => $totalLiabilities,
            'average_employees' => fake()->numberBetween(1, 20),
            'status' => 'draft',
            'created_by' => User::factory(),
        ];
    }

    public function forYear(int $year): static
    {
        return $this->state(fn (array $attributes) => [
            'fiscal_year' => $year,
            'period_start' => Carbon::create($year, 1, 1),
            'period_end' => Carbon::create($year, 12, 31),
        ]);
    }

    public function small(): static
    {
        return $this->state(fn (array $attributes) => [
            'company_size' => AnnualAccount::SIZE_SMALL,
            'revenue' => 50000000,
            'total_assets' => 25000000,
            'average_employees' => 30,
        ]);
    }

    public function medium(): static
    {
        return $this->state(fn (array $attributes) => [
            'company_size' => AnnualAccount::SIZE_MEDIUM,
            'revenue' => 150000000,
            'total_assets' => 100000000,
            'average_employees' => 100,
        ]);
    }

    public function large(): static
    {
        return $this->state(fn (array $attributes) => [
            'company_size' => AnnualAccount::SIZE_LARGE,
            'revenue' => 400000000,
            'total_assets' => 200000000,
            'average_employees' => 300,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'board_approval_date' => now(),
            'approved_by' => User::factory(),
        ]);
    }

    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'submitted',
            'board_approval_date' => now()->subDays(7),
            'submitted_at' => now(),
            'approved_by' => User::factory(),
        ]);
    }

    public function withBalancedEquation(): static
    {
        return $this->state(function (array $attributes) {
            $totalAssets = 1000000;
            $totalEquity = 400000;
            $totalLiabilities = 600000;

            return [
                'total_assets' => $totalAssets,
                'total_equity' => $totalEquity,
                'total_liabilities' => $totalLiabilities,
            ];
        });
    }

    public function withUnbalancedEquation(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'total_assets' => 1000000,
                'total_equity' => 400000,
                'total_liabilities' => 500000,
            ];
        });
    }

    public function withFinancials(float $revenue, float $profit, float $assets): static
    {
        return $this->state(fn (array $attributes) => [
            'revenue' => $revenue,
            'operating_profit' => $profit,
            'profit_before_tax' => $profit,
            'net_profit' => $profit * 0.78,
            'total_assets' => $assets,
            'total_equity' => $assets * 0.4,
            'total_liabilities' => $assets * 0.6,
        ]);
    }
}
