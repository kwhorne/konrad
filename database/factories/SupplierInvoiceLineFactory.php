<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\SupplierInvoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SupplierInvoiceLine>
 */
class SupplierInvoiceLineFactory extends Factory
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
            'account_id' => Account::factory()->expense(),
            'description' => fake()->sentence(),
            'quantity' => fake()->numberBetween(1, 10),
            'unit_price' => fake()->randomFloat(2, 100, 5000),
            'vat_percent' => 25,
            'sort_order' => 0,
        ];
    }
}
