<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Voucher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VoucherLine>
 */
class VoucherLineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 100, 10000);

        return [
            'voucher_id' => Voucher::factory(),
            'account_id' => Account::factory(),
            'description' => fake()->sentence(),
            'debit' => $amount,
            'credit' => 0,
            'sort_order' => 0,
        ];
    }

    public function debit(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'debit' => $amount,
            'credit' => 0,
        ]);
    }

    public function credit(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'debit' => 0,
            'credit' => $amount,
        ]);
    }
}
