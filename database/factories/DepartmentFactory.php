<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
class DepartmentFactory extends Factory
{
    public function definition(): array
    {
        $departments = [
            ['code' => 'ADM', 'name' => 'Administrasjon'],
            ['code' => 'SAL', 'name' => 'Salg'],
            ['code' => 'PRD', 'name' => 'Produksjon'],
            ['code' => 'LOG', 'name' => 'Logistikk'],
            ['code' => 'IT', 'name' => 'IT'],
            ['code' => 'HR', 'name' => 'Personal'],
            ['code' => 'FIN', 'name' => 'Finans'],
            ['code' => 'MKT', 'name' => 'Markedsføring'],
        ];
        $dept = fake()->randomElement($departments);

        return [
            'code' => fake()->unique()->bothify('DEP-###'),
            'name' => $dept['name'],
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function sales(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'SAL',
            'name' => 'Salg',
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'ADM',
            'name' => 'Administrasjon',
        ]);
    }

    public function production(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'PRD',
            'name' => 'Produksjon',
        ]);
    }
}
