<?php

namespace Database\Factories;

use App\Models\AnnualAccount;
use App\Models\AnnualAccountNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AnnualAccountNote>
 */
class AnnualAccountNoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $noteType = fake()->randomElement(array_keys(AnnualAccountNote::NOTE_TYPES));
        $noteInfo = AnnualAccountNote::NOTE_TYPES[$noteType];

        return [
            'annual_account_id' => AnnualAccount::factory(),
            'note_number' => fake()->numberBetween(1, 10),
            'note_type' => $noteType,
            'title' => $noteInfo['title'],
            'content' => fake()->paragraphs(2, true),
            'sort_order' => $noteInfo['order'],
            'is_required' => $noteInfo['required'],
            'is_visible' => true,
            'created_by' => User::factory(),
        ];
    }

    public function accountingPrinciples(): static
    {
        return $this->state(fn (array $attributes) => [
            'note_type' => 'accounting_principles',
            'note_number' => 1,
            'title' => AnnualAccountNote::NOTE_TYPES['accounting_principles']['title'],
            'sort_order' => AnnualAccountNote::NOTE_TYPES['accounting_principles']['order'],
            'is_required' => true,
            'content' => AnnualAccountNote::getAccountingPrinciplesTemplate(),
        ]);
    }

    public function employees(): static
    {
        return $this->state(fn (array $attributes) => [
            'note_type' => 'employees',
            'note_number' => 3,
            'title' => AnnualAccountNote::NOTE_TYPES['employees']['title'],
            'sort_order' => AnnualAccountNote::NOTE_TYPES['employees']['order'],
            'is_required' => false,
            'content' => AnnualAccountNote::getEmployeesTemplate(),
        ]);
    }

    public function equity(): static
    {
        return $this->state(fn (array $attributes) => [
            'note_type' => 'equity',
            'note_number' => 2,
            'title' => AnnualAccountNote::NOTE_TYPES['equity']['title'],
            'sort_order' => AnnualAccountNote::NOTE_TYPES['equity']['order'],
            'is_required' => true,
            'content' => AnnualAccountNote::getEquityTemplate(),
        ]);
    }

    public function required(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_required' => true,
        ]);
    }

    public function visible(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_visible' => true,
        ]);
    }

    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_visible' => false,
        ]);
    }

    public function empty(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => '',
        ]);
    }

    public function forType(string $type): static
    {
        $noteInfo = AnnualAccountNote::NOTE_TYPES[$type] ?? null;

        return $this->state(fn (array $attributes) => [
            'note_type' => $type,
            'title' => $noteInfo['title'] ?? $type,
            'sort_order' => $noteInfo['order'] ?? 99,
            'is_required' => $noteInfo['required'] ?? false,
        ]);
    }
}
