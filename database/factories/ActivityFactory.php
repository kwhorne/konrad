<?php

namespace Database\Factories;

use App\Models\ActivityType;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isCompleted = fake()->boolean(30);

        return [
            'contact_id' => Contact::factory(),
            'activity_type_id' => ActivityType::factory(),
            'subject' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'is_completed' => $isCompleted,
            'due_date' => fake()->optional()->dateTimeBetween('-1 week', '+2 weeks'),
            'completed_at' => $isCompleted ? fake()->dateTimeBetween('-1 week', 'now') : null,
            'created_by' => User::factory(),
            'assigned_to' => fake()->optional()->randomElement([User::factory()]),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_completed' => true,
            'completed_at' => now(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_completed' => false,
            'completed_at' => null,
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_completed' => false,
            'completed_at' => null,
            'due_date' => fake()->dateTimeBetween('-2 weeks', '-1 day'),
        ]);
    }
}
