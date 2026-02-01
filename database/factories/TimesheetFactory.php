<?php

namespace Database\Factories;

use App\Models\Timesheet;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Timesheet>
 */
class TimesheetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $weekStart = Carbon::now()->startOfWeek();

        return [
            'user_id' => User::factory(),
            'week_start' => $weekStart,
            'week_end' => $weekStart->copy()->endOfWeek(),
            'status' => Timesheet::STATUS_DRAFT,
            'total_hours' => 0,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Timesheet::STATUS_DRAFT,
        ]);
    }

    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Timesheet::STATUS_SUBMITTED,
            'submitted_by' => $attributes['user_id'],
            'submitted_at' => now(),
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Timesheet::STATUS_APPROVED,
            'submitted_by' => $attributes['user_id'],
            'submitted_at' => now()->subHour(),
            'approved_by' => User::factory(),
            'approved_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Timesheet::STATUS_REJECTED,
            'submitted_by' => $attributes['user_id'],
            'submitted_at' => now()->subHour(),
            'rejected_by' => User::factory(),
            'rejected_at' => now(),
            'rejection_reason' => fake()->sentence(),
        ]);
    }

    public function forWeek(Carbon $date): static
    {
        $weekStart = $date->copy()->startOfWeek();

        return $this->state(fn (array $attributes) => [
            'week_start' => $weekStart,
            'week_end' => $weekStart->copy()->endOfWeek(),
        ]);
    }

    public function withHours(float $hours): static
    {
        return $this->state(fn (array $attributes) => [
            'total_hours' => $hours,
        ]);
    }

    public function lastWeek(): static
    {
        return $this->forWeek(Carbon::now()->subWeek());
    }

    public function thisWeek(): static
    {
        return $this->forWeek(Carbon::now());
    }
}
