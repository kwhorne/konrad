<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shareholder>
 */
class ShareholderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shareholder_type' => 'person',
            'name' => fake()->name(),
            'national_id' => fake()->numerify('##########'),
            'country_code' => 'NO',
            'address' => fake()->streetAddress(),
            'postal_code' => fake()->postcode(),
            'city' => fake()->city(),
            'email' => fake()->optional()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'is_active' => true,
        ];
    }

    public function person(): static
    {
        return $this->state(fn (array $attributes) => [
            'shareholder_type' => 'person',
            'name' => fake()->name(),
            'national_id' => fake()->numerify('###########'),
            'organization_number' => null,
        ]);
    }

    public function company(): static
    {
        return $this->state(fn (array $attributes) => [
            'shareholder_type' => 'company',
            'name' => fake()->company(),
            'national_id' => null,
            'organization_number' => fake()->numerify('#########'),
        ]);
    }

    public function foreign(): static
    {
        return $this->state(fn (array $attributes) => [
            'country_code' => fake()->randomElement(['SE', 'DK', 'US', 'GB', 'DE']),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
