<?php

namespace Database\Factories;

use App\Models\PayrollRun;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PayrollRun>
 */
class PayrollRunFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = now()->year;
        $month = now()->month;
        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        return [
            'year' => $year,
            'month' => $month,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'utbetalingsdato' => $periodEnd->copy()->addDays(5),
            'status' => PayrollRun::STATUS_DRAFT,
            'total_bruttolonn' => 0,
            'total_forskuddstrekk' => 0,
            'total_nettolonn' => 0,
            'total_feriepenger_grunnlag' => 0,
            'total_arbeidsgiveravgift' => 0,
            'total_otp' => 0,
            'aga_sone' => '1',
            'aga_sats' => 14.1,
            'created_by' => User::factory(),
        ];
    }

    public function forMonth(int $year, int $month): static
    {
        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        return $this->state(fn (array $attributes) => [
            'year' => $year,
            'month' => $month,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'utbetalingsdato' => $periodEnd->copy()->addDays(5),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PayrollRun::STATUS_DRAFT,
        ]);
    }

    public function calculated(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PayrollRun::STATUS_CALCULATED,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PayrollRun::STATUS_APPROVED,
            'approved_by' => User::factory(),
            'approved_at' => now(),
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PayrollRun::STATUS_PAID,
            'approved_by' => User::factory(),
            'approved_at' => now()->subHour(),
            'paid_at' => now(),
        ]);
    }

    public function reported(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PayrollRun::STATUS_REPORTED,
            'approved_by' => User::factory(),
            'approved_at' => now()->subHours(2),
            'paid_at' => now()->subHour(),
        ]);
    }

    public function withTotals(
        float $bruttolonn = 50000,
        float $forskuddstrekk = 15000,
        float $aga = 7050,
        float $otp = 1000
    ): static {
        return $this->state(fn (array $attributes) => [
            'total_bruttolonn' => $bruttolonn,
            'total_forskuddstrekk' => $forskuddstrekk,
            'total_nettolonn' => $bruttolonn - $forskuddstrekk,
            'total_feriepenger_grunnlag' => $bruttolonn,
            'total_arbeidsgiveravgift' => $aga,
            'total_otp' => $otp,
        ]);
    }

    public function withAgaZone(string $zone, float $rate): static
    {
        return $this->state(fn (array $attributes) => [
            'aga_sone' => $zone,
            'aga_sats' => $rate,
        ]);
    }
}
