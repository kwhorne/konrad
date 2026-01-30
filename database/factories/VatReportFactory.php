<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\VatReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VatReport>
 */
class VatReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = fake()->numberBetween(2020, 2026);
        $period = fake()->numberBetween(1, 6);
        $dates = VatReport::getBimonthlyPeriodDates($year, $period);

        return [
            'report_type' => 'alminnelig',
            'period_type' => 'bimonthly',
            'year' => $year,
            'period' => $period,
            'period_from' => $dates['from'],
            'period_to' => $dates['to'],
            'total_base' => 0,
            'total_output_vat' => 0,
            'total_input_vat' => 0,
            'vat_payable' => 0,
            'status' => 'draft',
            'created_by' => User::factory(),
        ];
    }

    public function calculated(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'calculated',
            'calculated_at' => now(),
        ]);
    }

    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'submitted',
            'calculated_at' => now(),
            'submitted_at' => now(),
            'submitted_by' => User::factory(),
        ]);
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'accepted',
            'calculated_at' => now(),
            'submitted_at' => now(),
            'submitted_by' => User::factory(),
            'altinn_reference' => 'AR'.fake()->numerify('########'),
        ]);
    }

    public function monthly(): static
    {
        return $this->state(function (array $attributes) {
            $period = fake()->numberBetween(1, 12);

            return [
                'period_type' => 'monthly',
                'period' => $period,
                'period_from' => \Carbon\Carbon::create($attributes['year'], $period, 1)->startOfMonth(),
                'period_to' => \Carbon\Carbon::create($attributes['year'], $period, 1)->endOfMonth(),
            ];
        });
    }

    public function primaer(): static
    {
        return $this->state(fn (array $attributes) => [
            'report_type' => 'primaer',
        ]);
    }
}
