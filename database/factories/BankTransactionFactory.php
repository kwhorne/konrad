<?php

namespace Database\Factories;

use App\Models\BankStatement;
use App\Models\BankTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BankTransaction>
 */
class BankTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isCredit = fake()->boolean();
        $amount = fake()->randomFloat(2, 100, 50000);

        return [
            'bank_statement_id' => BankStatement::factory(),
            'transaction_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'description' => fake()->sentence(4),
            'reference' => fake()->optional(0.3)->numerify('############'),
            'amount' => $isCredit ? $amount : -$amount,
            'transaction_type' => $isCredit ? BankTransaction::TYPE_CREDIT : BankTransaction::TYPE_DEBIT,
            'running_balance' => fake()->randomFloat(2, 10000, 500000),
            'match_status' => BankTransaction::MATCH_STATUS_UNMATCHED,
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }

    public function credit(?float $amount = null): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $amount ?? fake()->randomFloat(2, 100, 50000),
            'transaction_type' => BankTransaction::TYPE_CREDIT,
        ]);
    }

    public function debit(?float $amount = null): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => -abs($amount ?? fake()->randomFloat(2, 100, 50000)),
            'transaction_type' => BankTransaction::TYPE_DEBIT,
        ]);
    }

    public function withKid(?string $kid = null): static
    {
        return $this->state(fn (array $attributes) => [
            'reference' => $kid ?? fake()->numerify('############'),
        ]);
    }

    public function unmatched(): static
    {
        return $this->state(fn (array $attributes) => [
            'match_status' => BankTransaction::MATCH_STATUS_UNMATCHED,
            'match_confidence' => null,
        ]);
    }

    public function autoMatched(?float $confidence = null): static
    {
        return $this->state(fn (array $attributes) => [
            'match_status' => BankTransaction::MATCH_STATUS_AUTO_MATCHED,
            'match_confidence' => $confidence ?? fake()->randomFloat(2, 0.80, 1.0),
        ]);
    }

    public function manualMatched(): static
    {
        return $this->state(fn (array $attributes) => [
            'match_status' => BankTransaction::MATCH_STATUS_MANUAL_MATCHED,
            'match_confidence' => 1.0,
        ]);
    }

    public function ignored(): static
    {
        return $this->state(fn (array $attributes) => [
            'match_status' => BankTransaction::MATCH_STATUS_IGNORED,
        ]);
    }

    public function forStatement(BankStatement $statement): static
    {
        return $this->state(fn (array $attributes) => [
            'bank_statement_id' => $statement->id,
            'company_id' => $statement->company_id,
            'transaction_date' => fake()->dateTimeBetween(
                $statement->from_date,
                $statement->to_date
            ),
        ]);
    }
}
