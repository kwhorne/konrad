<?php

namespace Database\Factories;

use App\Models\EmployeePayrollSettings;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmployeePayrollSettings>
 */
class EmployeePayrollSettingsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'ansattnummer' => fake()->unique()->numerify('###'),
            'ansatt_fra' => now()->subYears(2),
            'stillingsprosent' => 100.00,
            'stilling' => fake()->jobTitle(),
            'lonn_type' => EmployeePayrollSettings::LONN_TYPE_FAST,
            'maanedslonn' => fake()->numberBetween(30000, 80000),
            'skatt_type' => EmployeePayrollSettings::SKATT_TYPE_TABELLTREKK,
            'skattetabell' => '7100',
            'feriepenger_prosent' => 10.2,
            'ferie_5_uker' => false,
            'over_60' => false,
            'otp_enabled' => true,
            'otp_prosent' => 2.0,
            'kontonummer' => fake()->numerify('###########'),
            'is_active' => true,
        ];
    }

    public function hourly(float $rate = 250): static
    {
        return $this->state(fn (array $attributes) => [
            'lonn_type' => EmployeePayrollSettings::LONN_TYPE_TIME,
            'maanedslonn' => null,
            'timelonn' => $rate,
        ]);
    }

    public function withProsenttrekk(float $prosent = 30): static
    {
        return $this->state(fn (array $attributes) => [
            'skatt_type' => EmployeePayrollSettings::SKATT_TYPE_PROSENTTREKK,
            'skatteprosent' => $prosent,
            'skattetabell' => null,
        ]);
    }

    public function withFrikort(float $belop = 65000): static
    {
        return $this->state(fn (array $attributes) => [
            'skatt_type' => EmployeePayrollSettings::SKATT_TYPE_FRIKORT,
            'frikort_belop' => $belop,
            'frikort_brukt' => 0,
            'skattetabell' => null,
        ]);
    }

    public function withKildeskatt(): static
    {
        return $this->state(fn (array $attributes) => [
            'skatt_type' => EmployeePayrollSettings::SKATT_TYPE_KILDESKATT,
            'skattetabell' => null,
        ]);
    }

    public function withFiveWeeksHoliday(): static
    {
        return $this->state(fn (array $attributes) => [
            'ferie_5_uker' => true,
            'feriepenger_prosent' => 12.0,
        ]);
    }

    public function over60(): static
    {
        return $this->state(fn (array $attributes) => [
            'over_60' => true,
            'feriepenger_prosent' => 12.5,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function terminated(): static
    {
        return $this->state(fn (array $attributes) => [
            'ansatt_til' => now()->subDays(30),
        ]);
    }

    public function withOtp(float $prosent = 5.0): static
    {
        return $this->state(fn (array $attributes) => [
            'otp_enabled' => true,
            'otp_prosent' => $prosent,
        ]);
    }

    public function withoutOtp(): static
    {
        return $this->state(fn (array $attributes) => [
            'otp_enabled' => false,
            'otp_prosent' => 0,
        ]);
    }
}
