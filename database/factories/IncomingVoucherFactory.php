<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Contact;
use App\Models\IncomingVoucher;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IncomingVoucher>
 */
class IncomingVoucherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'original_filename' => fake()->word().'.pdf',
            'file_path' => 'incoming-vouchers/'.fake()->uuid().'.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => fake()->numberBetween(10000, 500000),
            'source' => IncomingVoucher::SOURCE_UPLOAD,
            'status' => IncomingVoucher::STATUS_PENDING,
            'created_by' => User::factory(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => IncomingVoucher::STATUS_PENDING,
        ]);
    }

    public function parsing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => IncomingVoucher::STATUS_PARSING,
        ]);
    }

    public function parsed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => IncomingVoucher::STATUS_PARSED,
            'parsed_at' => now(),
            'suggested_supplier_id' => Contact::factory()->supplier(),
            'suggested_invoice_number' => 'INV-'.fake()->numberBetween(1000, 9999),
            'suggested_invoice_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'suggested_due_date' => fake()->dateTimeBetween('now', '+30 days'),
            'suggested_total' => fake()->randomFloat(2, 1000, 50000),
            'suggested_vat_total' => fake()->randomFloat(2, 200, 10000),
            'suggested_account_id' => Account::factory()->expense(),
            'confidence_score' => fake()->randomFloat(2, 0.5, 1.0),
            'parsed_data' => [
                'description' => fake()->sentence(),
                'supplier_name' => fake()->company(),
            ],
        ]);
    }

    public function attested(): static
    {
        return $this->parsed()->state(fn (array $attributes) => [
            'status' => IncomingVoucher::STATUS_ATTESTED,
            'attested_by' => User::factory(),
            'attested_at' => now(),
        ]);
    }

    public function approved(): static
    {
        return $this->attested()->state(fn (array $attributes) => [
            'status' => IncomingVoucher::STATUS_APPROVED,
            'approved_by' => User::factory(),
            'approved_at' => now(),
        ]);
    }

    public function posted(): static
    {
        return $this->approved()->state(fn (array $attributes) => [
            'status' => IncomingVoucher::STATUS_POSTED,
        ]);
    }

    public function rejected(): static
    {
        return $this->parsed()->state(fn (array $attributes) => [
            'status' => IncomingVoucher::STATUS_REJECTED,
            'rejected_by' => User::factory(),
            'rejected_at' => now(),
            'rejection_reason' => fake()->sentence(),
        ]);
    }

    public function fromEmail(): static
    {
        return $this->state(fn (array $attributes) => [
            'source' => IncomingVoucher::SOURCE_EMAIL,
            'email_from' => fake()->email(),
            'email_subject' => fake()->sentence(),
            'email_received_at' => now(),
        ]);
    }
}
