<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\ProjectStatus;
use App\Models\ProjectType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-6 months', '+1 month');
        $endDate = fake()->dateTimeBetween($startDate, '+12 months');

        return [
            'name' => fake()->randomElement([
                'Nybygg',
                'Renovering',
                'Vedlikehold',
                'Oppussing',
                'Utbygging',
                'Rehabilitering',
            ]).' '.fake()->streetName(),
            'description' => fake()->optional()->paragraph(),
            'contact_id' => Contact::factory(),
            'project_type_id' => ProjectType::factory(),
            'project_status_id' => ProjectStatus::factory(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'budget' => fake()->randomFloat(2, 50000, 5000000),
            'estimated_hours' => fake()->randomFloat(2, 10, 500),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function withManager(?User $user = null): static
    {
        return $this->state(fn (array $attributes) => [
            'manager_id' => $user?->id ?? User::factory(),
        ]);
    }
}
