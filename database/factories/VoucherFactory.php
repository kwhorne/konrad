<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Voucher>
 */
class VoucherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'voucher_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'description' => fake()->sentence(),
            'voucher_type' => 'manual',
            'total_debit' => 0,
            'total_credit' => 0,
            'is_balanced' => true,
            'is_posted' => false,
            'created_by' => User::factory(),
        ];
    }

    public function posted(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_posted' => true,
            'posted_at' => now(),
        ]);
    }

    public function unbalanced(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_balanced' => false,
        ]);
    }

    public function invoice(): static
    {
        return $this->state(fn (array $attributes) => [
            'voucher_type' => 'invoice',
        ]);
    }

    public function payment(): static
    {
        return $this->state(fn (array $attributes) => [
            'voucher_type' => 'payment',
        ]);
    }

    public function supplierInvoice(): static
    {
        return $this->state(fn (array $attributes) => [
            'voucher_type' => 'supplier_invoice',
        ]);
    }

    public function supplierPayment(): static
    {
        return $this->state(fn (array $attributes) => [
            'voucher_type' => 'supplier_payment',
        ]);
    }
}
