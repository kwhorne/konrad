<?php

namespace Database\Factories;

use App\Models\TaxDepreciationSchedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TaxDepreciationSchedule>
 */
class TaxDepreciationScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $group = fake()->randomElement(array_keys(TaxDepreciationSchedule::DEPRECIATION_GROUPS));
        $groupInfo = TaxDepreciationSchedule::DEPRECIATION_GROUPS[$group];
        $openingBalance = fake()->randomFloat(2, 0, 500000);
        $additions = fake()->randomFloat(2, 0, 100000);
        $disposals = fake()->randomFloat(2, 0, min($openingBalance, 50000));
        $basis = $openingBalance + $additions - $disposals;
        $depreciation = $basis * ($groupInfo['rate'] / 100);

        return [
            'fiscal_year' => fake()->numberBetween(2020, 2025),
            'depreciation_group' => $group,
            'group_name' => $groupInfo['name'],
            'depreciation_rate' => $groupInfo['rate'],
            'opening_balance' => $openingBalance,
            'additions' => $additions,
            'disposals' => $disposals,
            'basis_for_depreciation' => $basis,
            'depreciation_amount' => min($depreciation, max(0, $basis)),
            'closing_balance' => $basis - min($depreciation, max(0, $basis)),
            'gain_loss_account' => 0,
            'created_by' => User::factory(),
        ];
    }

    public function forYear(int $year): static
    {
        return $this->state(fn (array $attributes) => [
            'fiscal_year' => $year,
        ]);
    }

    public function forGroup(string $group): static
    {
        $groupInfo = TaxDepreciationSchedule::DEPRECIATION_GROUPS[$group] ?? ['name' => $group, 'rate' => 10];

        return $this->state(fn (array $attributes) => [
            'depreciation_group' => $group,
            'group_name' => $groupInfo['name'],
            'depreciation_rate' => $groupInfo['rate'],
        ]);
    }

    public function withBalances(float $opening, float $additions = 0, float $disposals = 0): static
    {
        return $this->state(function (array $attributes) use ($opening, $additions, $disposals) {
            $basis = $opening + $additions - $disposals;
            $rate = $attributes['depreciation_rate'] ?? 20;
            $depreciation = $basis * ($rate / 100);

            return [
                'opening_balance' => $opening,
                'additions' => $additions,
                'disposals' => $disposals,
                'basis_for_depreciation' => $basis,
                'depreciation_amount' => min($depreciation, max(0, $basis)),
                'closing_balance' => $basis - min($depreciation, max(0, $basis)),
            ];
        });
    }
}
