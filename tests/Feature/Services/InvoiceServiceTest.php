<?php

use App\Models\Contact;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\InvoicePayment;
use App\Models\InvoiceStatus;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\InvoiceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(InvoiceService::class);
});

it('calculates due date correctly', function () {
    $invoiceDate = Carbon::parse('2024-01-15');
    $paymentTermsDays = 30;

    $dueDate = $this->service->calculateDueDate($invoiceDate, $paymentTermsDays);

    expect($dueDate->format('Y-m-d'))->toBe('2024-02-14');
});

it('calculates reminder date correctly', function () {
    $dueDate = Carbon::parse('2024-02-14');
    $reminderDays = 14;

    $reminderDate = $this->service->calculateReminderDate($dueDate, $reminderDays);

    expect($reminderDate->format('Y-m-d'))->toBe('2024-02-28');
});

it('prepares reminder date string', function () {
    $result = $this->service->prepareReminderDate('2024-02-14', 14);

    expect($result)->toBe('2024-02-28');
});

it('returns null when preparing reminder date without due date', function () {
    $result = $this->service->prepareReminderDate(null, 14);

    expect($result)->toBeNull();
});

it('returns null when preparing reminder date without reminder days', function () {
    $result = $this->service->prepareReminderDate('2024-02-14', null);

    expect($result)->toBeNull();
});

it('gets default status', function () {
    InvoiceStatus::factory()->create(['code' => 'draft', 'name' => 'Utkast']);

    $status = $this->service->getDefaultStatus();

    expect($status)->not->toBeNull();
    expect($status->code)->toBe('draft');
});

it('creates credit note from invoice', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $contact = Contact::factory()->create();
    InvoiceStatus::factory()->create(['code' => 'credited', 'name' => 'Kreditert']);

    $invoice = Invoice::factory()->create([
        'invoice_type' => 'invoice',
        'contact_id' => $contact->id,
        'subtotal' => 1000,
        'discount_total' => 100,
        'vat_total' => 225,
        'total' => 1125,
    ]);

    InvoiceLine::factory()->create([
        'invoice_id' => $invoice->id,
        'quantity' => 10,
        'unit_price' => 100,
        'discount_percent' => 10,
        'vat_percent' => 25,
    ]);

    $creditNote = $this->service->createCreditNote($invoice);

    expect($creditNote->invoice_type)->toBe('credit_note');
    expect($creditNote->original_invoice_id)->toBe($invoice->id);
    expect($creditNote->subtotal)->toBe('-1000.00');
    expect($creditNote->total)->toBe('-1125.00');
    expect($creditNote->lines)->toHaveCount(1);
    expect($creditNote->lines->first()->quantity)->toBeLessThan(0);
});

it('throws exception when creating credit note from credit note', function () {
    $creditNote = Invoice::factory()->create([
        'invoice_type' => 'credit_note',
    ]);

    $this->service->createCreditNote($creditNote);
})->throws(\InvalidArgumentException::class, 'Kan ikke lage kreditnota av en kreditnota.');

it('marks invoice as sent', function () {
    $sentStatus = InvoiceStatus::factory()->create(['code' => 'sent', 'name' => 'Sendt']);

    $invoice = Invoice::factory()->create([
        'sent_at' => null,
    ]);

    $result = $this->service->markAsSent($invoice);

    expect($result->invoice_status_id)->toBe($sentStatus->id);
    expect($result->sent_at)->not->toBeNull();
});

it('records payment and updates totals', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $paidStatus = InvoiceStatus::factory()->create(['code' => 'paid', 'name' => 'Betalt']);
    $paymentMethod = PaymentMethod::factory()->create();

    $invoice = Invoice::factory()->create([
        'total' => 1000,
        'paid_amount' => 0,
        'balance' => 1000,
    ]);

    $payment = $this->service->recordPayment($invoice, [
        'payment_method_id' => $paymentMethod->id,
        'payment_date' => '2024-01-15',
        'amount' => 1000,
        'reference' => 'REF123',
        'notes' => 'Full payment',
    ]);

    $invoice->refresh();

    expect($payment)->toBeInstanceOf(InvoicePayment::class);
    expect($payment->amount)->toBe('1000.00');
    expect($invoice->paid_amount)->toBe('1000.00');
    expect($invoice->balance)->toBe('0.00');
    expect($invoice->invoice_status_id)->toBe($paidStatus->id);
});

it('updates payment status to partially_paid', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $partialStatus = InvoiceStatus::factory()->create(['code' => 'partially_paid', 'name' => 'Delvis betalt']);
    $paymentMethod = PaymentMethod::factory()->create();

    $invoice = Invoice::factory()->create([
        'total' => 1000,
        'paid_amount' => 0,
        'balance' => 1000,
    ]);

    $this->service->recordPayment($invoice, [
        'payment_method_id' => $paymentMethod->id,
        'payment_date' => '2024-01-15',
        'amount' => 500,
    ]);

    $invoice->refresh();

    expect($invoice->paid_amount)->toBe('500.00');
    expect($invoice->balance)->toBe('500.00');
    expect($invoice->invoice_status_id)->toBe($partialStatus->id);
});

it('deletes payment and updates totals', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $paymentMethod = PaymentMethod::factory()->create();

    $invoice = Invoice::factory()->create([
        'total' => 1000,
        'paid_amount' => 500,
        'balance' => 500,
    ]);

    $payment = InvoicePayment::factory()->create([
        'invoice_id' => $invoice->id,
        'payment_method_id' => $paymentMethod->id,
        'amount' => 500,
    ]);

    $this->service->deletePayment($payment);

    $invoice->refresh();

    expect($invoice->paid_amount)->toBe('0.00');
    expect($invoice->balance)->toBe('1000.00');
});
