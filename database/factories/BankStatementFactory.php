<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\BankStatement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BankStatement>
 */
class BankStatementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fromDate = fake()->dateTimeBetween('-3 months', '-1 month');
        $toDate = fake()->dateTimeBetween($fromDate, 'now');

        return [
            'file_path' => 'bank-statements/'.fake()->uuid().'.csv',
            'original_filename' => 'kontoutskrift_'.fake()->date('Ymd').'.csv',
            'bank_name' => fake()->randomElement(['DNB', 'Nordea', 'SpareBank 1', 'Sbanken']),
            'account_number' => fake()->numerify('####.##.#####'),
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'opening_balance' => fake()->randomFloat(2, 10000, 500000),
            'closing_balance' => fake()->randomFloat(2, 10000, 500000),
            'status' => BankStatement::STATUS_PENDING,
            'transaction_count' => 0,
            'matched_count' => 0,
            'unmatched_count' => 0,
            'created_by' => User::factory(),
        ];
    }

    public function withBankAccount(): static
    {
        return $this->state(fn (array $attributes) => [
            'bank_account_id' => Account::factory()->state([
                'account_number' => '1920',
                'name' => 'Bank',
                'account_type' => 'asset',
            ]),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BankStatement::STATUS_PENDING,
        ]);
    }

    public function matching(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BankStatement::STATUS_MATCHING,
        ]);
    }

    public function matched(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BankStatement::STATUS_MATCHED,
        ]);
    }

    public function reconciled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BankStatement::STATUS_RECONCILED,
        ]);
    }

    public function finalized(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BankStatement::STATUS_FINALIZED,
            'finalized_by' => User::factory(),
            'finalized_at' => now(),
        ]);
    }

    public function dnb(): static
    {
        return $this->state(fn (array $attributes) => [
            'bank_name' => 'DNB',
        ]);
    }

    public function nordea(): static
    {
        return $this->state(fn (array $attributes) => [
            'bank_name' => 'Nordea',
        ]);
    }
}
