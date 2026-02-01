<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\PurchaseOrder;
use App\Models\StockLocation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GoodsReceipt>
 */
class GoodsReceiptFactory extends Factory
{
    public function definition(): array
    {
        $contact = Contact::factory()->supplier()->create();

        return [
            'purchase_order_id' => null,
            'contact_id' => $contact->id,
            'stock_location_id' => StockLocation::factory(),
            'receipt_date' => now(),
            'supplier_delivery_note' => fake()->optional()->bothify('DN-####'),
            'notes' => fake()->optional()->sentence(),
            'status' => 'draft',
            'created_by' => User::factory(),
            'posted_by' => null,
            'posted_at' => null,
        ];
    }

    public function fromPurchaseOrder(PurchaseOrder $po): static
    {
        return $this->state(fn (array $attributes) => [
            'purchase_order_id' => $po->id,
            'contact_id' => $po->contact_id,
            'stock_location_id' => $po->stock_location_id,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'posted_by' => null,
            'posted_at' => null,
        ]);
    }

    public function posted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'posted',
            'posted_by' => User::factory(),
            'posted_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }
}
