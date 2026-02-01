<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Timesheet;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TimesheetEntry>
 */
class TimesheetEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'timesheet_id' => Timesheet::factory(),
            'entry_date' => Carbon::now()->startOfWeek(),
            'hours' => fake()->randomFloat(2, 0.5, 8),
            'project_id' => null,
            'work_order_id' => null,
            'description' => fake()->sentence(),
            'is_billable' => true,
            'sort_order' => 0,
        ];
    }

    public function forDate(Carbon $date): static
    {
        return $this->state(fn (array $attributes) => [
            'entry_date' => $date,
        ]);
    }

    public function withHours(float $hours): static
    {
        return $this->state(fn (array $attributes) => [
            'hours' => $hours,
        ]);
    }

    public function forProject(Project $project): static
    {
        return $this->state(fn (array $attributes) => [
            'project_id' => $project->id,
        ]);
    }

    public function forWorkOrder(WorkOrder $workOrder): static
    {
        return $this->state(fn (array $attributes) => [
            'work_order_id' => $workOrder->id,
        ]);
    }

    public function billable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_billable' => true,
        ]);
    }

    public function nonBillable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_billable' => false,
        ]);
    }

    public function withDescription(string $description): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => $description,
        ]);
    }
}
