<?php

use App\Models\Company;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\SupplierInvoice;
use App\Models\User;
use App\Services\LedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function setupLedgerContext(): array
{
    $user = User::factory()->create(['onboarding_completed' => true]);
    $company = Company::factory()->withOwner($user)->create();
    $user->update(['current_company_id' => $company->id]);
    app()->instance('current.company', $company);

    return ['user' => $user->fresh(), 'company' => $company];
}

beforeEach(function () {
    ['user' => $this->user, 'company' => $this->company] = setupLedgerContext();
    $this->actingAs($this->user);
    $this->service = app(LedgerService::class);
});

describe('LedgerService Customer Ledger', function () {
    test('returns unpaid invoices', function () {
        $customer = Contact::factory()->customer()->create(['company_id' => $this->company->id]);

        Invoice::factory()->create([
            'company_id' => $this->company->id,
            'contact_id' => $customer->id,
            'invoice_type' => 'invoice',
            'total' => 10000,
            'balance' => 10000, // Unpaid
            'due_date' => now()->addDays(30),
        ]);

        Invoice::factory()->create([
            'company_id' => $this->company->id,
            'contact_id' => $customer->id,
            'invoice_type' => 'invoice',
            'total' => 5000,
            'balance' => 0, // Paid
            'due_date' => now()->addDays(30),
        ]);

        $ledger = $this->service->getCustomerLedger();

        expect($ledger)->toHaveCount(1)
            ->and((float) $ledger->first()->balance)->toBe(10000.00);
    });

    test('excludes credit notes', function () {
        $customer = Contact::factory()->customer()->create(['company_id' => $this->company->id]);

        Invoice::factory()->create([
            'company_id' => $this->company->id,
            'contact_id' => $customer->id,
            'invoice_type' => 'invoice',
            'total' => 10000,
            'balance' => 10000,
        ]);

        Invoice::factory()->creditNote()->create([
            'company_id' => $this->company->id,
            'contact_id' => $customer->id,
            'total' => 2000,
            'balance' => 2000,
        ]);

        $ledger = $this->service->getCustomerLedger();

        expect($ledger)->toHaveCount(1)
            ->and($ledger->first()->invoice_type)->toBe('invoice');
    });

    test('filters by contact id', function () {
        $customer1 = Contact::factory()->customer()->create(['company_id' => $this->company->id]);
        $customer2 = Contact::factory()->customer()->create(['company_id' => $this->company->id]);

        Invoice::factory()->create([
            'company_id' => $this->company->id,
            'contact_id' => $customer1->id,
            'invoice_type' => 'invoice',
            'balance' => 5000,
        ]);

        Invoice::factory()->create([
            'company_id' => $this->company->id,
            'contact_id' => $customer2->id,
            'invoice_type' => 'invoice',
            'balance' => 8000,
        ]);

        $ledger = $this->service->getCustomerLedger($customer1->id);

        expect($ledger)->toHaveCount(1)
            ->and($ledger->first()->contact_id)->toBe($customer1->id);
    });

    test('orders by due date', function () {
        $customer = Contact::factory()->customer()->create(['company_id' => $this->company->id]);

        Invoice::factory()->create([
            'company_id' => $this->company->id,
            'contact_id' => $customer->id,
            'invoice_type' => 'invoice',
            'balance' => 5000,
            'due_date' => now()->addDays(30),
        ]);

        Invoice::factory()->create([
            'company_id' => $this->company->id,
            'contact_id' => $customer->id,
            'invoice_type' => 'invoice',
            'balance' => 3000,
            'due_date' => now()->addDays(10),
        ]);

        $ledger = $this->service->getCustomerLedger();

        expect($ledger->first()->due_date->lt($ledger->last()->due_date))->toBeTrue();
    });
});

describe('LedgerService Customer Aging', function () {
    test('places not yet due invoices in current bucket', function () {
        $customer = Contact::factory()->customer()->create(['company_id' => $this->company->id]);

        Invoice::factory()->create([
            'company_id' => $this->company->id,
            'contact_id' => $customer->id,
            'invoice_type' => 'invoice',
            'balance' => 10000,
            'due_date' => now()->addDays(15), // Not yet due
        ]);

        $aging = $this->service->getCustomerAging();

        expect($aging['current']['invoices'])->toHaveCount(1)
            ->and($aging['current']['total'])->toBe(10000.0);
    });

    test('places 1-30 days overdue invoices correctly', function () {
        $customer = Contact::factory()->customer()->create(['company_id' => $this->company->id]);

        Invoice::factory()->create([
            'company_id' => $this->company->id,
            'contact_id' => $customer->id,
            'invoice_type' => 'invoice',
            'balance' => 5000,
            'due_date' => now()->subDays(15), // 15 days overdue
        ]);

        $aging = $this->service->getCustomerAging();

        expect($aging['1-30']['invoices'])->toHaveCount(1)
            ->and($aging['1-30']['total'])->toBe(5000.0);
    });

    test('places 31-60 days overdue invoices correctly', function () {
        $customer = Contact::factory()->customer()->create(['company_id' => $this->company->id]);

        Invoice::factory()->create([
            'company_id' => $this->company->id,
            'contact_id' => $customer->id,
            'invoice_type' => 'invoice',
            'balance' => 8000,
            'due_date' => now()->subDays(45), // 45 days overdue
        ]);

        $aging = $this->service->getCustomerAging();

        expect($aging['31-60']['invoices'])->toHaveCount(1)
            ->and($aging['31-60']['total'])->toBe(8000.0);
    });

    test('places 61-90 days overdue invoices correctly', function () {
        $customer = Contact::factory()->customer()->create(['company_id' => $this->company->id]);

        Invoice::factory()->create([
            'company_id' => $this->company->id,
            'contact_id' => $customer->id,
            'invoice_type' => 'invoice',
            'balance' => 12000,
            'due_date' => now()->subDays(75), // 75 days overdue
        ]);

        $aging = $this->service->getCustomerAging();

        expect($aging['61-90']['invoices'])->toHaveCount(1)
            ->and($aging['61-90']['total'])->toBe(12000.0);
    });

    test('places 90+ days overdue invoices correctly', function () {
        $customer = Contact::factory()->customer()->create(['company_id' => $this->company->id]);

        Invoice::factory()->create([
            'company_id' => $this->company->id,
            'contact_id' => $customer->id,
            'invoice_type' => 'invoice',
            'balance' => 20000,
            'due_date' => now()->subDays(120), // 120 days overdue
        ]);

        $aging = $this->service->getCustomerAging();

        expect($aging['90+']['invoices'])->toHaveCount(1)
            ->and($aging['90+']['total'])->toBe(20000.0);
    });

    test('aggregates totals across multiple invoices', function () {
        $customer = Contact::factory()->customer()->create(['company_id' => $this->company->id]);

        Invoice::factory()->create([
            'company_id' => $this->company->id,
            'contact_id' => $customer->id,
            'invoice_type' => 'invoice',
            'balance' => 5000,
            'due_date' => now()->subDays(10),
        ]);

        Invoice::factory()->create([
            'company_id' => $this->company->id,
            'contact_id' => $customer->id,
            'invoice_type' => 'invoice',
            'balance' => 3000,
            'due_date' => now()->subDays(25),
        ]);

        $aging = $this->service->getCustomerAging();

        expect($aging['1-30']['invoices'])->toHaveCount(2)
            ->and($aging['1-30']['total'])->toBe(8000.0);
    });
});

describe('LedgerService Supplier Ledger', function () {
    test('returns unpaid supplier invoices', function () {
        $supplier = Contact::factory()->supplier()->create(['company_id' => $this->company->id]);

        SupplierInvoice::create([
            'company_id' => $this->company->id,
            'contact_id' => $supplier->id,
            'invoice_number' => 'SUP-001',
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => 8000,
            'vat_total' => 2000,
            'total' => 10000,
            'balance' => 10000,
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);

        SupplierInvoice::create([
            'company_id' => $this->company->id,
            'contact_id' => $supplier->id,
            'invoice_number' => 'SUP-002',
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => 4000,
            'vat_total' => 1000,
            'total' => 5000,
            'balance' => 0, // Paid
            'status' => 'paid',
            'created_by' => $this->user->id,
        ]);

        $ledger = $this->service->getSupplierLedger();

        expect($ledger)->toHaveCount(1)
            ->and((float) $ledger->first()->balance)->toBe(10000.00);
    });

    test('filters by contact id', function () {
        $supplier1 = Contact::factory()->supplier()->create(['company_id' => $this->company->id]);
        $supplier2 = Contact::factory()->supplier()->create(['company_id' => $this->company->id]);

        SupplierInvoice::create([
            'company_id' => $this->company->id,
            'contact_id' => $supplier1->id,
            'invoice_number' => 'S1',
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'total' => 5000,
            'balance' => 5000,
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);

        SupplierInvoice::create([
            'company_id' => $this->company->id,
            'contact_id' => $supplier2->id,
            'invoice_number' => 'S2',
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'total' => 8000,
            'balance' => 8000,
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);

        $ledger = $this->service->getSupplierLedger($supplier1->id);

        expect($ledger)->toHaveCount(1)
            ->and($ledger->first()->contact_id)->toBe($supplier1->id);
    });
});

describe('LedgerService Supplier Aging', function () {
    test('categorizes supplier invoices by age', function () {
        $supplier = Contact::factory()->supplier()->create(['company_id' => $this->company->id]);

        // Current (not yet due)
        SupplierInvoice::create([
            'company_id' => $this->company->id,
            'contact_id' => $supplier->id,
            'invoice_number' => 'CURRENT',
            'invoice_date' => now(),
            'due_date' => now()->addDays(15),
            'total' => 1000,
            'balance' => 1000,
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);

        // 1-30 days overdue
        SupplierInvoice::create([
            'company_id' => $this->company->id,
            'contact_id' => $supplier->id,
            'invoice_number' => 'OVERDUE-30',
            'invoice_date' => now()->subDays(45),
            'due_date' => now()->subDays(15),
            'total' => 2000,
            'balance' => 2000,
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);

        // 90+ days overdue
        SupplierInvoice::create([
            'company_id' => $this->company->id,
            'contact_id' => $supplier->id,
            'invoice_number' => 'OVERDUE-100',
            'invoice_date' => now()->subDays(130),
            'due_date' => now()->subDays(100),
            'total' => 5000,
            'balance' => 5000,
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);

        $aging = $this->service->getSupplierAging();

        expect($aging['current']['total'])->toBe(1000.0)
            ->and($aging['1-30']['total'])->toBe(2000.0)
            ->and($aging['90+']['total'])->toBe(5000.0);
    });
});

describe('LedgerService Total Balances', function () {
    test('calculates total customer balance', function () {
        $customer = Contact::factory()->customer()->create(['company_id' => $this->company->id]);

        Invoice::factory()->create([
            'company_id' => $this->company->id,
            'contact_id' => $customer->id,
            'invoice_type' => 'invoice',
            'balance' => 10000,
        ]);

        Invoice::factory()->create([
            'company_id' => $this->company->id,
            'contact_id' => $customer->id,
            'invoice_type' => 'invoice',
            'balance' => 5000,
        ]);

        $total = $this->service->getTotalCustomerBalance();

        expect($total)->toBe(15000.0);
    });

    test('excludes paid invoices from customer total', function () {
        $customer = Contact::factory()->customer()->create(['company_id' => $this->company->id]);

        Invoice::factory()->create([
            'company_id' => $this->company->id,
            'contact_id' => $customer->id,
            'invoice_type' => 'invoice',
            'balance' => 10000,
        ]);

        Invoice::factory()->create([
            'company_id' => $this->company->id,
            'contact_id' => $customer->id,
            'invoice_type' => 'invoice',
            'balance' => 0, // Paid
        ]);

        $total = $this->service->getTotalCustomerBalance();

        expect($total)->toBe(10000.0);
    });

    test('calculates total supplier balance', function () {
        $supplier = Contact::factory()->supplier()->create(['company_id' => $this->company->id]);

        SupplierInvoice::create([
            'company_id' => $this->company->id,
            'contact_id' => $supplier->id,
            'invoice_number' => 'S1',
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'total' => 8000,
            'balance' => 8000,
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);

        SupplierInvoice::create([
            'company_id' => $this->company->id,
            'contact_id' => $supplier->id,
            'invoice_number' => 'S2',
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'total' => 4000,
            'balance' => 4000,
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);

        $total = $this->service->getTotalSupplierBalance();

        expect($total)->toBe(12000.0);
    });

    test('returns zero when no unpaid invoices', function () {
        expect($this->service->getTotalCustomerBalance())->toBe(0.0)
            ->and($this->service->getTotalSupplierBalance())->toBe(0.0);
    });
});

describe('LedgerService Summary Reports', function () {
    test('groups customer ledger by contact', function () {
        $customer1 = Contact::factory()->customer()->create(['company_id' => $this->company->id]);
        $customer2 = Contact::factory()->customer()->create(['company_id' => $this->company->id]);

        Invoice::factory()->create([
            'company_id' => $this->company->id,
            'contact_id' => $customer1->id,
            'invoice_type' => 'invoice',
            'total' => 10000,
            'balance' => 8000,
        ]);

        Invoice::factory()->create([
            'company_id' => $this->company->id,
            'contact_id' => $customer1->id,
            'invoice_type' => 'invoice',
            'total' => 5000,
            'balance' => 5000,
        ]);

        Invoice::factory()->create([
            'company_id' => $this->company->id,
            'contact_id' => $customer2->id,
            'invoice_type' => 'invoice',
            'total' => 3000,
            'balance' => 3000,
        ]);

        $summary = $this->service->getCustomerLedgerSummary();

        expect($summary)->toHaveCount(2);

        $customer1Summary = $summary->first(fn ($item) => $item['contact']->id === $customer1->id);
        expect($customer1Summary['invoice_count'])->toBe(2)
            ->and((float) $customer1Summary['total_balance'])->toBe(13000.0);
    });

    test('groups supplier ledger by contact', function () {
        $supplier1 = Contact::factory()->supplier()->create(['company_id' => $this->company->id]);
        $supplier2 = Contact::factory()->supplier()->create(['company_id' => $this->company->id]);

        SupplierInvoice::create([
            'company_id' => $this->company->id,
            'contact_id' => $supplier1->id,
            'invoice_number' => 'S1A',
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'total' => 10000,
            'balance' => 10000,
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);

        SupplierInvoice::create([
            'company_id' => $this->company->id,
            'contact_id' => $supplier1->id,
            'invoice_number' => 'S1B',
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'total' => 5000,
            'balance' => 5000,
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);

        SupplierInvoice::create([
            'company_id' => $this->company->id,
            'contact_id' => $supplier2->id,
            'invoice_number' => 'S2',
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'total' => 2000,
            'balance' => 2000,
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);

        $summary = $this->service->getSupplierLedgerSummary();

        expect($summary)->toHaveCount(2);

        $supplier1Summary = $summary->first(fn ($item) => $item['contact']->id === $supplier1->id);
        expect($supplier1Summary['invoice_count'])->toBe(2)
            ->and((float) $supplier1Summary['total_balance'])->toBe(15000.0);
    });

    test('excludes paid invoices from summary', function () {
        $customer = Contact::factory()->customer()->create(['company_id' => $this->company->id]);

        Invoice::factory()->create([
            'company_id' => $this->company->id,
            'contact_id' => $customer->id,
            'invoice_type' => 'invoice',
            'total' => 10000,
            'balance' => 10000,
        ]);

        Invoice::factory()->create([
            'company_id' => $this->company->id,
            'contact_id' => $customer->id,
            'invoice_type' => 'invoice',
            'total' => 5000,
            'balance' => 0, // Paid
        ]);

        $summary = $this->service->getCustomerLedgerSummary();

        // Only 1 invoice has balance > 0
        expect($summary)->toHaveCount(1)
            ->and($summary->first()['invoice_count'])->toBe(1);
    });
});
