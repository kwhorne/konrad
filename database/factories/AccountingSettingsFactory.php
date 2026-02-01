<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AccountingSettings>
 */
class AccountingSettingsFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'departments_enabled' => false,
            'require_department_on_vouchers' => false,
            'default_department_id' => null,
        ];
    }

    public function withDepartmentsEnabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'departments_enabled' => true,
        ]);
    }

    public function withRequiredDepartment(): static
    {
        return $this->state(fn (array $attributes) => [
            'departments_enabled' => true,
            'require_department_on_vouchers' => true,
        ]);
    }
}
