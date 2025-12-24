<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\QuoteStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quote>
 */
class QuoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $contact = Contact::factory()->customer()->create();
        $quoteDate = fake()->dateTimeBetween('-30 days', 'now');
        $validUntil = (clone $quoteDate)->modify('+30 days');

        return [
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'contact_id' => $contact->id,
            'quote_status_id' => QuoteStatus::where('code', 'draft')->first()?->id,
            'created_by' => User::factory(),
            'quote_date' => $quoteDate,
            'valid_until' => $validUntil,
            'payment_terms_days' => $contact->payment_terms_days ?? 30,
            'customer_name' => $contact->company_name,
            'customer_address' => $contact->address,
            'customer_postal_code' => $contact->postal_code,
            'customer_city' => $contact->city,
            'customer_country' => $contact->country ?? 'Norge',
            'subtotal' => 0,
            'discount_total' => 0,
            'vat_total' => 0,
            'total' => 0,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'quote_status_id' => QuoteStatus::where('code', 'sent')->first()?->id,
        ]);
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'quote_status_id' => QuoteStatus::where('code', 'accepted')->first()?->id,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'quote_status_id' => QuoteStatus::where('code', 'expired')->first()?->id,
            'valid_until' => now()->subDays(7),
        ]);
    }
}
