<?php

use App\Models\Contact;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\InvoicePayment;
use App\Models\InvoiceStatus;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Models\VatRate;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    // Create default statuses if they don't exist
    InvoiceStatus::firstOrCreate(
        ['code' => 'draft'],
        ['name' => 'Utkast', 'color' => 'blue', 'is_active' => true, 'sort_order' => 1]
    );
    InvoiceStatus::firstOrCreate(
        ['code' => 'sent'],
        ['name' => 'Sendt', 'color' => 'yellow', 'is_active' => true, 'sort_order' => 2]
    );
    InvoiceStatus::firstOrCreate(
        ['code' => 'paid'],
        ['name' => 'Betalt', 'color' => 'green', 'is_active' => true, 'sort_order' => 4]
    );
    InvoiceStatus::firstOrCreate(
        ['code' => 'credited'],
        ['name' => 'Kreditert', 'color' => 'purple', 'is_active' => true, 'sort_order' => 6]
    );

    // Create default VAT rate if it doesn't exist
    VatRate::firstOrCreate(
        ['code' => 'standard'],
        ['rate' => 25, 'name' => 'Standard MVA', 'is_default' => true, 'is_active' => true]
    );

    // Create default payment method if it doesn't exist
    PaymentMethod::firstOrCreate(
        ['code' => 'bank'],
        ['name' => 'Bankoverføring', 'is_active' => true, 'sort_order' => 1]
    );
});

test('invoice has auto-generated number', function () {
    $contact = Contact::factory()->create();
    $invoice = Invoice::create([
        'invoice_type' => 'invoice',
        'title' => 'Test Invoice',
        'contact_id' => $contact->id,
        'created_by' => $this->user->id,
    ]);

    expect($invoice->invoice_number)->toMatch('/^F-\d{4}-\d{4}$/');
});

test('credit note has different number prefix', function () {
    $contact = Contact::factory()->create();
    $creditNote = Invoice::create([
        'invoice_type' => 'credit_note',
        'title' => 'Test Credit Note',
        'contact_id' => $contact->id,
        'created_by' => $this->user->id,
    ]);

    expect($creditNote->invoice_number)->toMatch('/^K-\d{4}-\d{4}$/');
});

test('invoice can have lines', function () {
    $contact = Contact::factory()->create();
    $vatRate = VatRate::where('code', 'standard')->first();

    $invoice = Invoice::create([
        'invoice_type' => 'invoice',
        'title' => 'Test Invoice',
        'contact_id' => $contact->id,
        'created_by' => $this->user->id,
    ]);

    InvoiceLine::create([
        'invoice_id' => $invoice->id,
        'description' => 'Test Product',
        'quantity' => 4,
        'unit' => 'stk',
        'unit_price' => 250,
        'vat_rate_id' => $vatRate->id,
        'vat_percent' => 25,
        'sort_order' => 0,
    ]);

    $invoice->refresh();

    expect($invoice->lines)->toHaveCount(1);
    expect((float) $invoice->subtotal)->toBe(1000.0);
    expect((float) $invoice->vat_total)->toBe(250.0);
    expect((float) $invoice->total)->toBe(1250.0);
    expect((float) $invoice->balance)->toBe(1250.0);
});

test('invoice can have payments', function () {
    $contact = Contact::factory()->create();
    $vatRate = VatRate::where('code', 'standard')->first();
    $paymentMethod = PaymentMethod::where('code', 'bank')->first();

    $invoice = Invoice::create([
        'invoice_type' => 'invoice',
        'title' => 'Test Invoice',
        'contact_id' => $contact->id,
        'created_by' => $this->user->id,
    ]);

    InvoiceLine::create([
        'invoice_id' => $invoice->id,
        'description' => 'Test Product',
        'quantity' => 1,
        'unit' => 'stk',
        'unit_price' => 800,
        'vat_rate_id' => $vatRate->id,
        'vat_percent' => 25,
        'sort_order' => 0,
    ]);

    $invoice->refresh();
    expect((float) $invoice->total)->toBe(1000.0);

    InvoicePayment::create([
        'invoice_id' => $invoice->id,
        'payment_method_id' => $paymentMethod->id,
        'payment_date' => now(),
        'amount' => 500,
        'created_by' => $this->user->id,
    ]);

    $invoice->refresh();
    expect((float) $invoice->paid_amount)->toBe(500.0);
    expect((float) $invoice->balance)->toBe(500.0);

    InvoicePayment::create([
        'invoice_id' => $invoice->id,
        'payment_method_id' => $paymentMethod->id,
        'payment_date' => now(),
        'amount' => 500,
        'created_by' => $this->user->id,
    ]);

    $invoice->refresh();
    expect((float) $invoice->paid_amount)->toBe(1000.0);
    expect((float) $invoice->balance)->toBe(0.0);
});

test('invoice can be credited', function () {
    $contact = Contact::factory()->create();
    $vatRate = VatRate::where('code', 'standard')->first();

    $invoice = Invoice::create([
        'invoice_type' => 'invoice',
        'title' => 'Test Invoice',
        'contact_id' => $contact->id,
        'created_by' => $this->user->id,
        'customer_name' => 'Test Company',
        'customer_address' => 'Test Address',
    ]);

    InvoiceLine::create([
        'invoice_id' => $invoice->id,
        'description' => 'Test Product',
        'quantity' => 2,
        'unit' => 'stk',
        'unit_price' => 400,
        'vat_rate_id' => $vatRate->id,
        'vat_percent' => 25,
        'sort_order' => 0,
    ]);

    $invoice->refresh();

    $creditNote = $invoice->createCreditNote();

    expect($creditNote)->not->toBeNull();
    expect($creditNote->is_credit_note)->toBeTrue();
    expect($creditNote->invoice_number)->toMatch('/^K-\d{4}-\d{4}$/');
    expect($creditNote->original_invoice_id)->toBe($invoice->id);
    expect($creditNote->lines)->toHaveCount(1);
    // Credit note has negative quantity
    expect((float) $creditNote->lines->first()->quantity)->toBe(-2.0);
    expect((float) $creditNote->total)->toBe(-1000.0);
});

test('invoice is_overdue works correctly', function () {
    $contact = Contact::factory()->create();
    $vatRate = VatRate::where('code', 'standard')->first();

    $notOverdue = Invoice::create([
        'invoice_type' => 'invoice',
        'title' => 'Not Overdue',
        'contact_id' => $contact->id,
        'created_by' => $this->user->id,
        'due_date' => now()->addDays(5),
        'total' => 100,
        'balance' => 100,
    ]);

    $overdue = Invoice::create([
        'invoice_type' => 'invoice',
        'title' => 'Overdue',
        'contact_id' => $contact->id,
        'created_by' => $this->user->id,
        'due_date' => now()->subDays(5),
        'total' => 100,
        'balance' => 100,
    ]);

    expect($notOverdue->is_overdue)->toBeFalse();
    expect($overdue->is_overdue)->toBeTrue();
});
