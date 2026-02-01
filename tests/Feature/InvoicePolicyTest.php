<?php

use App\Models\Company;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $owner = User::factory()->create(['onboarding_completed' => true]);
    $this->company = Company::factory()->withOwner($owner)->create();
    app()->instance('current.company', $this->company);

    $this->admin = User::factory()->create([
        'is_admin' => true,
        'is_economy' => false,
        'current_company_id' => $this->company->id,
    ]);
    $this->economyUser = User::factory()->create([
        'is_admin' => false,
        'is_economy' => true,
        'current_company_id' => $this->company->id,
    ]);
    $this->regularUser = User::factory()->create([
        'is_admin' => false,
        'is_economy' => false,
        'current_company_id' => $this->company->id,
    ]);
});

// viewAny tests
it('allows admin to view any invoices', function () {
    expect($this->admin->can('viewAny', Invoice::class))->toBeTrue();
});

it('allows economy user to view any invoices', function () {
    expect($this->economyUser->can('viewAny', Invoice::class))->toBeTrue();
});

it('denies regular user to view any invoices', function () {
    expect($this->regularUser->can('viewAny', Invoice::class))->toBeFalse();
});

// view tests
it('allows admin to view invoice', function () {
    $invoice = Invoice::factory()->create();
    expect($this->admin->can('view', $invoice))->toBeTrue();
});

it('allows economy user to view invoice', function () {
    $invoice = Invoice::factory()->create();
    expect($this->economyUser->can('view', $invoice))->toBeTrue();
});

it('denies regular user to view invoice', function () {
    $invoice = Invoice::factory()->create();
    expect($this->regularUser->can('view', $invoice))->toBeFalse();
});

// create tests
it('allows admin to create invoices', function () {
    expect($this->admin->can('create', Invoice::class))->toBeTrue();
});

it('allows economy user to create invoices', function () {
    expect($this->economyUser->can('create', Invoice::class))->toBeTrue();
});

it('denies regular user to create invoices', function () {
    expect($this->regularUser->can('create', Invoice::class))->toBeFalse();
});

// update tests
it('allows admin to update any invoice', function () {
    $invoice = Invoice::factory()->create(['sent_at' => now()]);
    expect($this->admin->can('update', $invoice))->toBeTrue();
});

it('allows economy user to update unsent invoice', function () {
    $invoice = Invoice::factory()->create(['sent_at' => null]);
    expect($this->economyUser->can('update', $invoice))->toBeTrue();
});

it('denies economy user to update sent invoice', function () {
    $invoice = Invoice::factory()->create(['sent_at' => now()]);
    expect($this->economyUser->can('update', $invoice))->toBeFalse();
});

it('allows economy user to update sent credit note', function () {
    $invoice = Invoice::factory()->create([
        'invoice_type' => 'credit_note',
        'sent_at' => now(),
    ]);
    expect($this->economyUser->can('update', $invoice))->toBeTrue();
});

it('denies regular user to update invoice', function () {
    $invoice = Invoice::factory()->create(['sent_at' => null]);
    expect($this->regularUser->can('update', $invoice))->toBeFalse();
});

// delete tests
it('allows admin to delete any invoice', function () {
    $invoice = Invoice::factory()->create(['sent_at' => now()]);
    expect($this->admin->can('delete', $invoice))->toBeTrue();
});

it('allows economy user to delete unsent invoice', function () {
    $invoice = Invoice::factory()->create(['sent_at' => null, 'paid_amount' => 0]);
    expect($this->economyUser->can('delete', $invoice))->toBeTrue();
});

it('denies economy user to delete sent invoice', function () {
    $invoice = Invoice::factory()->create(['sent_at' => now()]);
    expect($this->economyUser->can('delete', $invoice))->toBeFalse();
});

it('denies economy user to delete invoice with payments', function () {
    $invoice = Invoice::factory()->create(['sent_at' => null, 'paid_amount' => 100]);
    expect($this->economyUser->can('delete', $invoice))->toBeFalse();
});

it('denies regular user to delete invoice', function () {
    $invoice = Invoice::factory()->create(['sent_at' => null]);
    expect($this->regularUser->can('delete', $invoice))->toBeFalse();
});

// createCreditNote tests
it('allows admin to create credit note', function () {
    $invoice = Invoice::factory()->create(['invoice_type' => 'invoice']);
    expect($this->admin->can('createCreditNote', $invoice))->toBeTrue();
});

it('allows economy user to create credit note for invoice', function () {
    $invoice = Invoice::factory()->create(['invoice_type' => 'invoice']);
    expect($this->economyUser->can('createCreditNote', $invoice))->toBeTrue();
});

it('denies economy user to create credit note from credit note', function () {
    $invoice = Invoice::factory()->create(['invoice_type' => 'credit_note']);
    expect($this->economyUser->can('createCreditNote', $invoice))->toBeFalse();
});

it('denies regular user to create credit note', function () {
    $invoice = Invoice::factory()->create(['invoice_type' => 'invoice']);
    expect($this->regularUser->can('createCreditNote', $invoice))->toBeFalse();
});

// markAsSent tests
it('allows admin to mark any invoice as sent', function () {
    $invoice = Invoice::factory()->create(['sent_at' => now()]);
    expect($this->admin->can('markAsSent', $invoice))->toBeTrue();
});

it('allows economy user to mark unsent invoice as sent', function () {
    $invoice = Invoice::factory()->create(['sent_at' => null]);
    expect($this->economyUser->can('markAsSent', $invoice))->toBeTrue();
});

it('denies economy user to mark already sent invoice as sent', function () {
    $invoice = Invoice::factory()->create(['sent_at' => now()]);
    expect($this->economyUser->can('markAsSent', $invoice))->toBeFalse();
});

it('denies regular user to mark invoice as sent', function () {
    $invoice = Invoice::factory()->create(['sent_at' => null]);
    expect($this->regularUser->can('markAsSent', $invoice))->toBeFalse();
});

// recordPayment tests
it('allows admin to record payment on any invoice', function () {
    $invoice = Invoice::factory()->create(['sent_at' => null]);
    expect($this->admin->can('recordPayment', $invoice))->toBeTrue();
});

it('allows economy user to record payment on sent invoice', function () {
    $invoice = Invoice::factory()->create(['sent_at' => now()]);
    expect($this->economyUser->can('recordPayment', $invoice))->toBeTrue();
});

it('denies economy user to record payment on unsent invoice', function () {
    $invoice = Invoice::factory()->create(['sent_at' => null]);
    expect($this->economyUser->can('recordPayment', $invoice))->toBeFalse();
});

it('denies regular user to record payment', function () {
    $invoice = Invoice::factory()->create(['sent_at' => now()]);
    expect($this->regularUser->can('recordPayment', $invoice))->toBeFalse();
});
