<?php

use App\Models\Contact;
use App\Models\PaymentMethod;
use App\Models\SupplierInvoice;
use App\Models\SupplierInvoiceLine;
use App\Models\SupplierPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

// Internal Number Generation
test('supplier invoice has auto-generated internal number', function () {
    $invoice = SupplierInvoice::factory()->create();

    $year = date('Y');
    expect($invoice->internal_number)->toMatch("/^LF-{$year}-\d{4}$/");
});

test('supplier invoice internal numbers increment correctly', function () {
    $invoice1 = SupplierInvoice::factory()->create();
    $invoice2 = SupplierInvoice::factory()->create();

    preg_match('/LF-\d{4}-(\d+)/', $invoice1->internal_number, $matches1);
    preg_match('/LF-\d{4}-(\d+)/', $invoice2->internal_number, $matches2);

    expect((int) $matches2[1])->toBe((int) $matches1[1] + 1);
});

// Status Labels
test('supplier invoice status label returns correct norwegian translation', function (string $status, string $expectedLabel) {
    $invoice = SupplierInvoice::factory()->create(['status' => $status]);

    expect($invoice->status_label)->toBe($expectedLabel);
})->with([
    ['draft', 'Utkast'],
    ['approved', 'Godkjent'],
    ['paid', 'Betalt'],
    ['partially_paid', 'Delvis betalt'],
]);

// Status Colors
test('supplier invoice status color returns correct color', function (string $status, string $expectedColor) {
    $invoice = SupplierInvoice::factory()->create(['status' => $status]);

    expect($invoice->status_color)->toBe($expectedColor);
})->with([
    ['draft', 'zinc'],
    ['approved', 'blue'],
    ['paid', 'green'],
    ['partially_paid', 'yellow'],
]);

// Relationships
test('supplier invoice belongs to contact', function () {
    $contact = Contact::factory()->supplier()->create();
    $invoice = SupplierInvoice::factory()->create(['contact_id' => $contact->id]);

    expect($invoice->contact->id)->toBe($contact->id);
});

test('supplier invoice belongs to creator', function () {
    $creator = User::factory()->create();
    $invoice = SupplierInvoice::factory()->create(['created_by' => $creator->id]);

    expect($invoice->creator->id)->toBe($creator->id);
});

test('supplier invoice belongs to approver when approved', function () {
    $approver = User::factory()->create();
    $invoice = SupplierInvoice::factory()->approved()->create(['approved_by' => $approver->id]);

    expect($invoice->approver->id)->toBe($approver->id);
});

test('supplier invoice can have lines', function () {
    $invoice = SupplierInvoice::factory()->create();

    SupplierInvoiceLine::factory()->count(3)->create([
        'supplier_invoice_id' => $invoice->id,
    ]);

    expect($invoice->lines)->toHaveCount(3);
});

test('supplier invoice can have payments', function () {
    $invoice = SupplierInvoice::factory()->approved()->create();
    $paymentMethod = PaymentMethod::factory()->create();

    SupplierPayment::factory()->count(2)->create([
        'supplier_invoice_id' => $invoice->id,
        'payment_method_id' => $paymentMethod->id,
    ]);

    expect($invoice->payments)->toHaveCount(2);
});

// Totals Recalculation
test('supplier invoice recalculates totals from lines', function () {
    $invoice = SupplierInvoice::factory()->create([
        'subtotal' => 0,
        'vat_total' => 0,
        'total' => 0,
        'balance' => 0,
        'paid_amount' => 0,
    ]);

    SupplierInvoiceLine::factory()->create([
        'supplier_invoice_id' => $invoice->id,
        'quantity' => 2,
        'unit_price' => 1000,
        'vat_percent' => 25,
    ]);

    $invoice->refresh();

    expect((float) $invoice->subtotal)->toBe(2000.0);
    expect((float) $invoice->vat_total)->toBe(500.0);
    expect((float) $invoice->total)->toBe(2500.0);
    expect((float) $invoice->balance)->toBe(2500.0);
});

// Payment Updates
test('supplier invoice updates paid amount when payment is added', function () {
    $invoice = SupplierInvoice::factory()->approved()->create([
        'total' => 1000,
        'balance' => 1000,
        'paid_amount' => 0,
    ]);
    $paymentMethod = PaymentMethod::factory()->create();

    SupplierPayment::factory()->create([
        'supplier_invoice_id' => $invoice->id,
        'payment_method_id' => $paymentMethod->id,
        'amount' => 400,
    ]);

    $invoice->refresh();

    expect((float) $invoice->paid_amount)->toBe(400.0);
    expect((float) $invoice->balance)->toBe(600.0);
    expect($invoice->status)->toBe('partially_paid');
});

test('supplier invoice becomes paid when fully paid', function () {
    $invoice = SupplierInvoice::factory()->approved()->create([
        'total' => 1000,
        'balance' => 1000,
        'paid_amount' => 0,
    ]);
    $paymentMethod = PaymentMethod::factory()->create();

    SupplierPayment::factory()->create([
        'supplier_invoice_id' => $invoice->id,
        'payment_method_id' => $paymentMethod->id,
        'amount' => 1000,
    ]);

    $invoice->refresh();

    expect((float) $invoice->paid_amount)->toBe(1000.0);
    expect((float) $invoice->balance)->toBe(0.0);
    expect($invoice->status)->toBe('paid');
});

// Approval
test('draft invoice can be approved', function () {
    $invoice = SupplierInvoice::factory()->create(['status' => 'draft']);

    $result = $invoice->approve();

    expect($result)->toBeTrue();
    expect($invoice->status)->toBe('approved');
    expect($invoice->approved_by)->toBe($this->user->id);
    expect($invoice->approved_at)->not->toBeNull();
});

test('non-draft invoice cannot be approved', function () {
    $invoice = SupplierInvoice::factory()->approved()->create();

    $result = $invoice->approve();

    expect($result)->toBeFalse();
});

// Is Paid Attribute
test('supplier invoice is_paid is true when balance is zero', function () {
    $invoice = SupplierInvoice::factory()->paid()->create();

    expect($invoice->is_paid)->toBeTrue();
});

test('supplier invoice is_paid is false when balance is positive', function () {
    $invoice = SupplierInvoice::factory()->approved()->create(['balance' => 100]);

    expect($invoice->is_paid)->toBeFalse();
});

// Is Overdue Attribute
test('supplier invoice is_overdue when past due date and unpaid', function () {
    $invoice = SupplierInvoice::factory()->overdue()->create();

    expect($invoice->is_overdue)->toBeTrue();
});

test('supplier invoice is not overdue when paid', function () {
    $invoice = SupplierInvoice::factory()->paid()->create([
        'due_date' => now()->subDays(10),
    ]);

    expect($invoice->is_overdue)->toBeFalse();
});

test('supplier invoice is not overdue when due date is in future', function () {
    $invoice = SupplierInvoice::factory()->approved()->create([
        'due_date' => now()->addDays(10),
    ]);

    expect($invoice->is_overdue)->toBeFalse();
});

// Scopes
test('draft scope filters correctly', function () {
    SupplierInvoice::factory()->count(2)->create(['status' => 'draft']);
    SupplierInvoice::factory()->count(3)->approved()->create();

    expect(SupplierInvoice::draft()->count())->toBe(2);
});

test('approved scope filters correctly', function () {
    SupplierInvoice::factory()->count(2)->create(['status' => 'draft']);
    SupplierInvoice::factory()->count(3)->approved()->create();

    expect(SupplierInvoice::approved()->count())->toBe(3);
});

test('unpaid scope filters correctly', function () {
    SupplierInvoice::factory()->count(2)->approved()->create(['balance' => 100]);
    SupplierInvoice::factory()->count(3)->paid()->create();

    expect(SupplierInvoice::unpaid()->count())->toBe(2);
});

test('overdue scope filters correctly', function () {
    SupplierInvoice::factory()->count(2)->overdue()->create();
    SupplierInvoice::factory()->count(3)->approved()->create(['due_date' => now()->addDays(10)]);

    expect(SupplierInvoice::overdue()->count())->toBe(2);
});

// Soft Deletes
test('supplier invoice can be soft deleted', function () {
    $invoice = SupplierInvoice::factory()->create();

    $invoice->delete();

    expect($invoice->trashed())->toBeTrue();
    expect(SupplierInvoice::count())->toBe(0);
    expect(SupplierInvoice::withTrashed()->count())->toBe(1);
});

// Factory States
test('supplier invoice factory approved state works', function () {
    $invoice = SupplierInvoice::factory()->approved()->create();

    expect($invoice->status)->toBe('approved');
    expect($invoice->approved_at)->not->toBeNull();
});

test('supplier invoice factory paid state works', function () {
    $invoice = SupplierInvoice::factory()->paid()->create();

    expect($invoice->status)->toBe('paid');
    expect((float) $invoice->balance)->toBe(0.0);
});

test('supplier invoice factory overdue state works', function () {
    $invoice = SupplierInvoice::factory()->overdue()->create();

    expect($invoice->due_date->isPast())->toBeTrue();
    expect($invoice->is_overdue)->toBeTrue();
});
