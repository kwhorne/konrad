<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\InvoiceStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $contact = Contact::factory()->customer()->create();
        $invoiceDate = fake()->dateTimeBetween('-30 days', 'now');
        $paymentTermsDays = $contact->payment_terms_days ?? 30;
        $dueDate = (clone $invoiceDate)->modify("+{$paymentTermsDays} days");
        $reminderDays = 14;
        $reminderDate = (clone $dueDate)->modify("+{$reminderDays} days");

        return [
            'invoice_type' => 'invoice',
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'contact_id' => $contact->id,
            'invoice_status_id' => InvoiceStatus::where('code', 'draft')->first()?->id,
            'created_by' => User::factory(),
            'invoice_date' => $invoiceDate,
            'due_date' => $dueDate,
            'payment_terms_days' => $paymentTermsDays,
            'reminder_days' => $reminderDays,
            'reminder_date' => $reminderDate,
            'customer_name' => $contact->company_name,
            'customer_address' => $contact->address,
            'customer_postal_code' => $contact->postal_code,
            'customer_city' => $contact->city,
            'customer_country' => $contact->country ?? 'Norge',
            'subtotal' => 0,
            'discount_total' => 0,
            'vat_total' => 0,
            'total' => 0,
            'paid_amount' => 0,
            'balance' => 0,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'invoice_status_id' => InvoiceStatus::where('code', 'sent')->first()?->id,
            'sent_at' => now(),
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'invoice_status_id' => InvoiceStatus::where('code', 'paid')->first()?->id,
            'paid_at' => now(),
            'paid_amount' => $attributes['total'] ?? 0,
            'balance' => 0,
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'invoice_status_id' => InvoiceStatus::where('code', 'overdue')->first()?->id,
            'invoice_date' => now()->subDays(45),
            'due_date' => now()->subDays(15),
        ]);
    }

    public function creditNote(): static
    {
        return $this->state(fn (array $attributes) => [
            'invoice_type' => 'credit_note',
        ]);
    }
}
