<?php

namespace Database\Factories;

use App\Models\PaymentMethod;
use App\Models\SupplierInvoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SupplierPayment>
 */
class SupplierPaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'supplier_invoice_id' => SupplierInvoice::factory(),
            'payment_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'amount' => fake()->randomFloat(2, 100, 10000),
            'payment_method_id' => PaymentMethod::factory(),
            'reference' => fake()->optional()->numerify('REF-####'),
            'created_by' => User::factory(),
        ];
    }
}
