<?php

namespace Database\Factories;

use App\Models\VatCode;
use App\Models\VatReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VatReportLine>
 */
class VatReportLineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $baseAmount = fake()->randomFloat(2, 1000, 100000);
        $vatRate = 25;
        $vatAmount = $baseAmount * ($vatRate / 100);

        return [
            'vat_report_id' => VatReport::factory(),
            'vat_code_id' => VatCode::factory(),
            'base_amount' => $baseAmount,
            'vat_rate' => $vatRate,
            'vat_amount' => $vatAmount,
            'is_manual_override' => false,
            'sort_order' => 0,
        ];
    }

    public function manualOverride(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_manual_override' => true,
        ]);
    }

    public function withAmounts(float $base, float $rate): static
    {
        return $this->state(fn (array $attributes) => [
            'base_amount' => $base,
            'vat_rate' => $rate,
            'vat_amount' => $base * ($rate / 100),
        ]);
    }
}
