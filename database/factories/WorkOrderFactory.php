<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\User;
use App\Models\WorkOrderPriority;
use App\Models\WorkOrderStatus;
use App\Models\WorkOrderType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkOrder>
 */
class WorkOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $scheduledDate = fake()->dateTimeBetween('-1 month', '+2 months');
        $dueDate = (clone $scheduledDate)->modify('+'.fake()->numberBetween(1, 14).' days');

        return [
            'title' => fake()->randomElement([
                'Reparasjon',
                'Vedlikehold',
                'Installasjon',
                'Inspeksjon',
                'Service',
                'Oppgradering',
                'Feilsøking',
                'Montering',
            ]).' - '.fake()->word(),
            'description' => fake()->optional()->paragraph(),
            'contact_id' => Contact::factory(),
            'work_order_type_id' => WorkOrderType::factory(),
            'work_order_status_id' => WorkOrderStatus::factory(),
            'work_order_priority_id' => WorkOrderPriority::factory(),
            'assigned_to' => User::factory(),
            'created_by' => User::factory(),
            'scheduled_date' => $scheduledDate,
            'due_date' => $dueDate,
            'estimated_hours' => fake()->randomFloat(2, 1, 40),
            'budget' => fake()->optional()->randomFloat(2, 1000, 50000),
            'internal_notes' => fake()->optional()->sentence(),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
