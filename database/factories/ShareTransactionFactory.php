<?php

namespace Database\Factories;

use App\Models\ShareClass;
use App\Models\Shareholder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ShareTransaction>
 */
class ShareTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $numberOfShares = fake()->numberBetween(10, 1000);
        $pricePerShare = fake()->randomFloat(4, 1, 1000);

        return [
            'transaction_date' => fake()->dateTimeBetween('first day of january this year', 'now'),
            'transaction_type' => 'transfer',
            'share_class_id' => ShareClass::factory(),
            'from_shareholder_id' => Shareholder::factory(),
            'to_shareholder_id' => Shareholder::factory(),
            'number_of_shares' => $numberOfShares,
            'price_per_share' => $pricePerShare,
            'total_amount' => $numberOfShares * $pricePerShare,
            'currency' => 'NOK',
            'created_by' => User::factory(),
        ];
    }

    public function issue(): static
    {
        return $this->state(fn (array $attributes) => [
            'transaction_type' => 'issue',
            'from_shareholder_id' => null,
        ]);
    }

    public function transfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'transaction_type' => 'transfer',
        ]);
    }

    public function redemption(): static
    {
        return $this->state(fn (array $attributes) => [
            'transaction_type' => 'redemption',
            'to_shareholder_id' => null,
        ]);
    }
}
