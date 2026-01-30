<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SupplierInvoice>
 */
class SupplierInvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $total = fake()->randomFloat(2, 1000, 50000);

        return [
            'invoice_number' => fake()->unique()->numerify('INV-####'),
            'contact_id' => Contact::factory()->supplier(),
            'invoice_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'due_date' => fake()->dateTimeBetween('now', '+30 days'),
            'subtotal' => $total * 0.8,
            'vat_total' => $total * 0.2,
            'total' => $total,
            'paid_amount' => 0,
            'balance' => $total,
            'status' => 'draft',
            'description' => fake()->optional()->sentence(),
            'created_by' => User::factory(),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_by' => User::factory(),
            'approved_at' => now(),
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_amount' => $attributes['total'],
            'balance' => 0,
            'approved_by' => User::factory(),
            'approved_at' => now(),
        ]);
    }

    public function partiallyPaid(float $paidPercent = 50): static
    {
        return $this->state(function (array $attributes) use ($paidPercent) {
            $paidAmount = $attributes['total'] * ($paidPercent / 100);

            return [
                'status' => 'partially_paid',
                'paid_amount' => $paidAmount,
                'balance' => $attributes['total'] - $paidAmount,
                'approved_by' => User::factory(),
                'approved_at' => now(),
            ];
        });
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => fake()->dateTimeBetween('-30 days', '-1 day'),
            'status' => 'approved',
            'approved_by' => User::factory(),
            'approved_at' => now(),
        ]);
    }
}
