<?php

namespace Database\Factories;

use App\Models\InvoiceStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InvoiceStatus>
 */
class InvoiceStatusFactory extends Factory
{
    protected $model = InvoiceStatus::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'code' => fake()->unique()->slug(1),
            'color' => fake()->randomElement(['blue', 'green', 'yellow', 'red', 'purple', 'zinc']),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }
}
