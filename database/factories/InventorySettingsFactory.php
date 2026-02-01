<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InventorySettings>
 */
class InventorySettingsFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'inventory_account_id' => null,
            'cogs_account_id' => null,
            'grni_account_id' => null,
            'inventory_adjustment_account_id' => null,
            'default_stock_location_id' => null,
            'auto_reserve_on_order' => true,
            'allow_negative_stock' => false,
        ];
    }

    public function allowNegativeStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'allow_negative_stock' => true,
        ]);
    }

    public function noAutoReservation(): static
    {
        return $this->state(fn (array $attributes) => [
            'auto_reserve_on_order' => false,
        ]);
    }
}
