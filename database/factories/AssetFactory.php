<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Asset>
 */
class AssetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $purchaseDate = fake()->dateTimeBetween('-5 years', '-1 month');
        $warrantyYears = fake()->randomElement([1, 2, 3, 5]);

        return [
            'title' => fake()->randomElement([
                'MacBook Pro 14"',
                'Dell XPS 15',
                'HP EliteBook',
                'iPhone 15 Pro',
                'Samsung Galaxy S24',
                'iPad Pro 12.9"',
                'Dell Monitor 27"',
                'Logitech MX Master',
                'Herman Miller Aeron',
                'Standing Desk',
                'Projektor Epson',
                'Printer HP LaserJet',
                'Server Dell PowerEdge',
                'NAS Synology',
                'Router Cisco',
            ]),
            'description' => fake()->optional()->sentence(),
            'serial_number' => strtoupper(fake()->bothify('???-####-????')),
            'asset_model' => fake()->optional()->word(),
            'purchase_price' => fake()->randomFloat(2, 500, 50000),
            'currency' => 'NOK',
            'purchase_date' => $purchaseDate,
            'supplier' => fake()->company(),
            'manufacturer' => fake()->randomElement(['Apple', 'Dell', 'HP', 'Lenovo', 'Samsung', 'Microsoft', 'Logitech']),
            'location' => fake()->randomElement(['Kontor Oslo', 'Kontor Bergen', 'Lager', 'Hjemmekontor', 'Møterom A', 'Møterom B']),
            'department' => fake()->randomElement(['IT', 'HR', 'Salg', 'Økonomi', 'Drift', 'Ledelse']),
            'invoice_number' => fake()->optional()->numerify('F-####'),
            'invoice_date' => $purchaseDate,
            'warranty_from' => $purchaseDate,
            'warranty_until' => (clone $purchaseDate)->modify("+{$warrantyYears} years"),
            'status' => fake()->randomElement(['in_use', 'in_use', 'in_use', 'available', 'maintenance', 'retired']),
            'condition' => fake()->randomElement(['excellent', 'good', 'good', 'fair', 'poor']),
            'is_active' => true,
            'notes' => fake()->optional()->sentence(),
            'created_by' => User::first()?->id ?? User::factory(),
            'responsible_user_id' => User::first()?->id ?? User::factory(),
        ];
    }
}
