<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\StockLocation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    public function definition(): array
    {
        $contact = Contact::factory()->supplier()->create();
        $orderDate = fake()->dateTimeBetween('-30 days', 'now');
        $expectedDate = (clone $orderDate)->modify('+14 days');

        return [
            'contact_id' => $contact->id,
            'stock_location_id' => StockLocation::factory(),
            'status' => 'draft',
            'order_date' => $orderDate,
            'expected_date' => $expectedDate,
            'supplier_reference' => fake()->optional()->bothify('SUP-####'),
            'shipping_address' => fake()->optional()->address(),
            'notes' => fake()->optional()->paragraph(),
            'internal_notes' => null,
            'subtotal' => 0,
            'vat_total' => 0,
            'total' => 0,
            'created_by' => User::factory(),
            'approved_by' => null,
            'approved_at' => null,
            'sent_at' => null,
            'sort_order' => 0,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    public function pendingApproval(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending_approval',
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_by' => User::factory(),
            'approved_at' => now(),
        ]);
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sent',
            'approved_by' => User::factory(),
            'approved_at' => now()->subDay(),
            'sent_at' => now(),
        ]);
    }

    public function partiallyReceived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'partially_received',
            'approved_by' => User::factory(),
            'approved_at' => now()->subDays(2),
            'sent_at' => now()->subDay(),
        ]);
    }

    public function received(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'received',
            'approved_by' => User::factory(),
            'approved_at' => now()->subDays(3),
            'sent_at' => now()->subDays(2),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }
}
