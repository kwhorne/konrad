<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'organization_number' => fake()->unique()->numerify('#########'),
            'vat_number' => 'NO'.fake()->numerify('#########').'MVA',
            'address' => fake()->streetAddress(),
            'postal_code' => fake()->numerify('####'),
            'city' => fake()->city(),
            'country' => 'Norge',
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'website' => fake()->url(),
            'bank_name' => fake()->randomElement(['DNB', 'Nordea', 'SpareBank 1', 'Danske Bank']),
            'bank_account' => fake()->numerify('####.##.#####'),
            'default_payment_days' => 14,
            'default_quote_validity_days' => 30,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the company is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a company with a user as owner.
     */
    public function withOwner(\App\Models\User $user): static
    {
        return $this->afterCreating(function (Company $company) use ($user) {
            $company->users()->attach($user->id, [
                'role' => 'owner',
                'is_default' => true,
                'joined_at' => now(),
            ]);
        });
    }
}
