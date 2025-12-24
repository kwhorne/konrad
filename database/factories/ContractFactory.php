<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contract>
 */
class ContractFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-2 years', 'now');
        $durationMonths = fake()->randomElement([12, 24, 36, 48, 60]);
        $endDate = (clone $startDate)->modify("+{$durationMonths} months");
        $noticePeriodDays = fake()->randomElement([30, 60, 90]);

        return [
            'title' => fake()->randomElement([
                'Serviceavtale',
                'Vedlikeholdsavtale',
                'Leieavtale',
                'Programvareavtale',
                'Forsikringsavtale',
                'Leverandøravtale',
            ]).' - '.fake()->company(),
            'description' => fake()->optional()->paragraph(),
            'established_date' => $startDate,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'duration_months' => $durationMonths,
            'notice_period_days' => $noticePeriodDays,
            'notice_date' => (clone $endDate)->modify("-{$noticePeriodDays} days"),
            'company_name' => fake()->company(),
            'company_contact' => fake()->name(),
            'company_email' => fake()->companyEmail(),
            'company_phone' => fake()->phoneNumber(),
            'department' => fake()->optional()->randomElement(['IT', 'HR', 'Drift', 'Salg', 'Økonomi']),
            'type' => fake()->randomElement(['service', 'lease', 'maintenance', 'software', 'insurance', 'supplier']),
            'status' => fake()->randomElement(['draft', 'active', 'active', 'active', 'expiring_soon', 'expired']),
            'value' => fake()->randomFloat(2, 5000, 500000),
            'currency' => 'NOK',
            'payment_frequency' => fake()->randomElement(['monthly', 'quarterly', 'yearly']),
            'auto_renewal' => fake()->boolean(70),
            'renewal_period_months' => 12,
            'notes' => fake()->optional()->paragraph(),
            'created_by' => User::first()?->id ?? User::factory(),
            'responsible_user_id' => User::first()?->id ?? User::factory(),
        ];
    }
}
