<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InvoicePayment>
 */
class InvoicePaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $paymentMethod = PaymentMethod::where('is_active', true)->first()
            ?? PaymentMethod::factory()->create();

        return [
            'invoice_id' => Invoice::factory(),
            'payment_method_id' => $paymentMethod->id,
            'payment_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'amount' => fake()->randomFloat(2, 100, 10000),
            'reference' => fake()->optional()->bothify('PAY-####'),
            'notes' => fake()->optional()->sentence(),
            'created_by' => User::factory(),
        ];
    }

    public function forInvoice(Invoice $invoice): static
    {
        return $this->state(fn (array $attributes) => [
            'invoice_id' => $invoice->id,
            'amount' => $invoice->balance,
        ]);
    }

    public function partial(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => fake()->randomFloat(2, 100, 500),
        ]);
    }
}
