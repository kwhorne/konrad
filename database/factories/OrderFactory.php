<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\OrderStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $contact = Contact::factory()->customer()->create();
        $orderDate = fake()->dateTimeBetween('-30 days', 'now');
        $deliveryDate = (clone $orderDate)->modify('+14 days');

        return [
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'contact_id' => $contact->id,
            'order_status_id' => OrderStatus::where('code', 'draft')->first()?->id,
            'created_by' => User::factory(),
            'order_date' => $orderDate,
            'delivery_date' => $deliveryDate,
            'customer_reference' => fake()->optional()->bothify('REF-####'),
            'payment_terms_days' => $contact->payment_terms_days ?? 30,
            'customer_name' => $contact->company_name,
            'customer_address' => $contact->address,
            'customer_postal_code' => $contact->postal_code,
            'customer_city' => $contact->city,
            'customer_country' => $contact->country ?? 'Norge',
            'delivery_address' => $contact->address,
            'delivery_postal_code' => $contact->postal_code,
            'delivery_city' => $contact->city,
            'delivery_country' => $contact->country ?? 'Norge',
            'subtotal' => 0,
            'discount_total' => 0,
            'vat_total' => 0,
            'total' => 0,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_status_id' => OrderStatus::where('code', 'confirmed')->first()?->id,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_status_id' => OrderStatus::where('code', 'in_progress')->first()?->id,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_status_id' => OrderStatus::where('code', 'completed')->first()?->id,
        ]);
    }
}
