<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\PlatformInvoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlatformInvoice>
 */
class PlatformInvoiceFactory extends Factory
{
    protected $model = PlatformInvoice::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'invoice_number' => 'KON-'.fake()->year().'-'.str_pad(fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'description' => 'Månedlig lisens — Konrad Office, '.fake()->monthName().' '.fake()->year(),
            'amount' => fake()->randomElement([14900, 29900, 34900, 49900, 99900]),
            'due_date' => fake()->dateTimeBetween('-1 month', '+2 months'),
            'sent_at' => now(),
            'paid_at' => null,
            'notes' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'paid_at' => now(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => now()->subDays(10),
            'paid_at' => null,
        ]);
    }
}
