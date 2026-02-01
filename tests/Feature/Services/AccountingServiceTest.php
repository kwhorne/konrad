<?php

use App\Models\Account;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\PaymentMethod;
use App\Models\SupplierInvoice;
use App\Models\SupplierInvoiceLine;
use App\Models\SupplierPayment;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherLine;
use App\Services\AccountingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function setupAccountingContext(): array
{
    $user = User::factory()->create(['onboarding_completed' => true]);
    $company = Company::factory()->withOwner($user)->create();
    $user->update(['current_company_id' => $company->id]);
    app()->instance('current.company', $company);

    // Create standard Norwegian accounts
    $accounts = [
        '1500' => Account::create(['company_id' => $company->id, 'account_number' => '1500', 'name' => 'Kundefordringer', 'account_class' => '1', 'account_type' => 'asset']),
        '1920' => Account::create(['company_id' => $company->id, 'account_number' => '1920', 'name' => 'Bankinnskudd', 'account_class' => '1', 'account_type' => 'asset']),
        '2400' => Account::create(['company_id' => $company->id, 'account_number' => '2400', 'name' => 'Leverandørgjeld', 'account_class' => '2', 'account_type' => 'liability']),
        '2700' => Account::create(['company_id' => $company->id, 'account_number' => '2700', 'name' => 'Utgående MVA', 'account_class' => '2', 'account_type' => 'liability']),
        '2710' => Account::create(['company_id' => $company->id, 'account_number' => '2710', 'name' => 'Inngående MVA', 'account_class' => '2', 'account_type' => 'asset']),
        '3000' => Account::create(['company_id' => $company->id, 'account_number' => '3000', 'name' => 'Salgsinntekt', 'account_class' => '3', 'account_type' => 'revenue']),
        '4000' => Account::create(['company_id' => $company->id, 'account_number' => '4000', 'name' => 'Varekostnad', 'account_class' => '4', 'account_type' => 'expense']),
    ];

    return ['user' => $user->fresh(), 'company' => $company, 'accounts' => $accounts];
}

beforeEach(function () {
    ['user' => $this->user, 'company' => $this->company, 'accounts' => $this->accounts] = setupAccountingContext();
    $this->actingAs($this->user);
    $this->service = app(AccountingService::class);
});

describe('AccountingService Invoice Voucher', function () {
    test('creates voucher from invoice with correct entries', function () {
        $contact = Contact::factory()->customer()->create(['company_id' => $this->company->id]);

        $invoice = Invoice::factory()->create([
            'company_id' => $this->company->id,
            'contact_id' => $contact->id,
            'subtotal' => 10000,
            'discount_total' => 0,
            'vat_total' => 2500,
            'total' => 12500,
            'customer_name' => $contact->company_name,
        ]);

        $voucher = $this->service->createInvoiceVoucher($invoice);

        expect($voucher)->toBeInstanceOf(Voucher::class)
            ->and($voucher->voucher_type)->toBe('invoice')
            ->and($voucher->reference_id)->toBe($invoice->id)
            ->and($voucher->is_posted)->toBeTrue();

        $lines = $voucher->lines;

        // Should have 3 lines: debit receivables, credit revenue, credit VAT
        expect($lines)->toHaveCount(3);

        // Check debit (receivables 1500)
        $debitLine = $lines->where('debit', '>', 0)->first();
        expect((float) $debitLine->debit)->toBe(12500.00)
            ->and($debitLine->account->account_number)->toBe('1500')
            ->and($debitLine->contact_id)->toBe($contact->id);

        // Check credit revenue (3000)
        $revenueLine = $lines->where('credit', 10000)->first();
        expect($revenueLine)->not->toBeNull()
            ->and($revenueLine->account->account_number)->toBe('3000');

        // Check credit VAT (2700)
        $vatLine = $lines->where('credit', 2500)->first();
        expect($vatLine)->not->toBeNull()
            ->and($vatLine->account->account_number)->toBe('2700')
            ->and((float) $vatLine->vat_amount)->toBe(2500.00);
    });

    test('creates voucher without VAT line when vat_total is zero', function () {
        $contact = Contact::factory()->customer()->create(['company_id' => $this->company->id]);

        $invoice = Invoice::factory()->create([
            'company_id' => $this->company->id,
            'contact_id' => $contact->id,
            'subtotal' => 10000,
            'discount_total' => 0,
            'vat_total' => 0,
            'total' => 10000,
        ]);

        $voucher = $this->service->createInvoiceVoucher($invoice);
        $lines = $voucher->lines;

        // Should have 2 lines: debit receivables, credit revenue (no VAT)
        expect($lines)->toHaveCount(2);
    });

    test('handles discounts correctly in revenue line', function () {
        $contact = Contact::factory()->customer()->create(['company_id' => $this->company->id]);

        $invoice = Invoice::factory()->create([
            'company_id' => $this->company->id,
            'contact_id' => $contact->id,
            'subtotal' => 10000,
            'discount_total' => 1000,
            'vat_total' => 2250, // 25% of 9000
            'total' => 11250,
        ]);

        $voucher = $this->service->createInvoiceVoucher($invoice);

        $revenueLine = $voucher->lines->where('account_id', $this->accounts['3000']->id)->first();

        // Revenue should be subtotal minus discount
        expect((float) $revenueLine->credit)->toBe(9000.00);
    });
});

describe('AccountingService Payment Voucher', function () {
    test('creates voucher from customer payment', function () {
        $contact = Contact::factory()->customer()->create(['company_id' => $this->company->id]);

        $invoice = Invoice::factory()->create([
            'company_id' => $this->company->id,
            'contact_id' => $contact->id,
            'total' => 12500,
            'balance' => 12500,
            'customer_name' => $contact->company_name,
        ]);

        $paymentMethod = PaymentMethod::factory()->create(['company_id' => $this->company->id]);

        $payment = InvoicePayment::create([
            'company_id' => $this->company->id,
            'invoice_id' => $invoice->id,
            'payment_method_id' => $paymentMethod->id,
            'payment_date' => now(),
            'amount' => 12500,
            'reference' => 'PAY-001',
            'created_by' => $this->user->id,
        ]);

        $voucher = $this->service->createPaymentVoucher($payment);

        expect($voucher)->toBeInstanceOf(Voucher::class)
            ->and($voucher->voucher_type)->toBe('payment')
            ->and($voucher->is_posted)->toBeTrue();

        $lines = $voucher->lines;
        expect($lines)->toHaveCount(2);

        // Debit bank (1920)
        $bankLine = $lines->where('account_id', $this->accounts['1920']->id)->first();
        expect((float) $bankLine->debit)->toBe(12500.00)
            ->and((float) $bankLine->credit)->toBe(0.00);

        // Credit receivables (1500)
        $receivablesLine = $lines->where('account_id', $this->accounts['1500']->id)->first();
        expect((float) $receivablesLine->credit)->toBe(12500.00)
            ->and($receivablesLine->contact_id)->toBe($contact->id);
    });

    test('creates voucher for partial payment', function () {
        $contact = Contact::factory()->customer()->create(['company_id' => $this->company->id]);

        $invoice = Invoice::factory()->create([
            'company_id' => $this->company->id,
            'contact_id' => $contact->id,
            'total' => 10000,
            'balance' => 10000,
        ]);

        $paymentMethod = PaymentMethod::factory()->create(['company_id' => $this->company->id]);

        $payment = InvoicePayment::create([
            'company_id' => $this->company->id,
            'invoice_id' => $invoice->id,
            'payment_method_id' => $paymentMethod->id,
            'payment_date' => now(),
            'amount' => 5000, // Partial payment
            'reference' => 'PAY-002',
            'created_by' => $this->user->id,
        ]);

        $voucher = $this->service->createPaymentVoucher($payment);

        $bankLine = $voucher->lines->where('account_id', $this->accounts['1920']->id)->first();
        expect((float) $bankLine->debit)->toBe(5000.00);
    });
});

describe('AccountingService Supplier Invoice Voucher', function () {
    test('creates voucher from supplier invoice', function () {
        $supplier = Contact::factory()->supplier()->create(['company_id' => $this->company->id]);

        $invoice = SupplierInvoice::create([
            'company_id' => $this->company->id,
            'contact_id' => $supplier->id,
            'invoice_number' => 'SUP-001',
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => 8000,
            'vat_total' => 2000,
            'total' => 10000,
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);

        // Create invoice line with account
        SupplierInvoiceLine::create([
            'company_id' => $this->company->id,
            'supplier_invoice_id' => $invoice->id,
            'account_id' => $this->accounts['4000']->id,
            'description' => 'Varer',
            'quantity' => 1,
            'unit_price' => 8000,
            'vat_rate' => 25,
            'vat_amount' => 2000,
            'total' => 10000,
            'sort_order' => 0,
        ]);

        $voucher = $this->service->createSupplierInvoiceVoucher($invoice);

        expect($voucher)->toBeInstanceOf(Voucher::class)
            ->and($voucher->voucher_type)->toBe('supplier_invoice')
            ->and($voucher->is_posted)->toBeTrue();

        $lines = $voucher->lines;
        expect($lines)->toHaveCount(3);

        // Debit expense account (4000)
        $expenseLine = $lines->where('account_id', $this->accounts['4000']->id)->first();
        expect((float) $expenseLine->debit)->toBe(8000.00);

        // Debit input VAT (2710)
        $vatLine = $lines->where('account_id', $this->accounts['2710']->id)->first();
        expect((float) $vatLine->debit)->toBe(2000.00);

        // Credit supplier liability (2400)
        $liabilityLine = $lines->where('account_id', $this->accounts['2400']->id)->first();
        expect((float) $liabilityLine->credit)->toBe(10000.00)
            ->and($liabilityLine->contact_id)->toBe($supplier->id);
    });
});

describe('AccountingService Supplier Payment Voucher', function () {
    test('creates voucher from supplier payment', function () {
        $supplier = Contact::factory()->supplier()->create(['company_id' => $this->company->id]);

        $invoice = SupplierInvoice::create([
            'company_id' => $this->company->id,
            'contact_id' => $supplier->id,
            'invoice_number' => 'SUP-002',
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => 8000,
            'vat_total' => 2000,
            'total' => 10000,
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);

        $paymentMethod = PaymentMethod::factory()->create(['company_id' => $this->company->id]);

        $payment = SupplierPayment::create([
            'company_id' => $this->company->id,
            'supplier_invoice_id' => $invoice->id,
            'payment_method_id' => $paymentMethod->id,
            'payment_date' => now(),
            'amount' => 10000,
            'reference' => 'OUT-001',
            'created_by' => $this->user->id,
        ]);

        $voucher = $this->service->createSupplierPaymentVoucher($payment);

        expect($voucher)->toBeInstanceOf(Voucher::class)
            ->and($voucher->voucher_type)->toBe('supplier_payment')
            ->and($voucher->is_posted)->toBeTrue();

        $lines = $voucher->lines;
        expect($lines)->toHaveCount(2);

        // Debit supplier liability (2400)
        $liabilityLine = $lines->where('account_id', $this->accounts['2400']->id)->first();
        expect((float) $liabilityLine->debit)->toBe(10000.00)
            ->and($liabilityLine->contact_id)->toBe($supplier->id);

        // Credit bank (1920)
        $bankLine = $lines->where('account_id', $this->accounts['1920']->id)->first();
        expect((float) $bankLine->credit)->toBe(10000.00);
    });
});

describe('AccountingService Account Balance', function () {
    test('calculates asset account balance correctly', function () {
        $bankAccount = $this->accounts['1920'];

        // Create a posted voucher with bank transactions
        $voucher = Voucher::create([
            'company_id' => $this->company->id,
            'voucher_date' => now(),
            'description' => 'Test voucher',
            'voucher_type' => 'manual',
            'is_posted' => true,
            'created_by' => $this->user->id,
        ]);

        // Debit 10000 to bank
        VoucherLine::create([
            'company_id' => $this->company->id,
            'voucher_id' => $voucher->id,
            'account_id' => $bankAccount->id,
            'description' => 'Deposit',
            'debit' => 10000,
            'credit' => 0,
            'sort_order' => 0,
        ]);

        // Credit 3000 from bank
        VoucherLine::create([
            'company_id' => $this->company->id,
            'voucher_id' => $voucher->id,
            'account_id' => $bankAccount->id,
            'description' => 'Withdrawal',
            'debit' => 0,
            'credit' => 3000,
            'sort_order' => 1,
        ]);

        $balance = $this->service->getAccountBalance($bankAccount);

        // Asset account: debit - credit = 10000 - 3000 = 7000
        expect($balance)->toBe(7000.00);
    });

    test('calculates liability account balance correctly', function () {
        $liabilityAccount = $this->accounts['2400'];

        $voucher = Voucher::create([
            'company_id' => $this->company->id,
            'voucher_date' => now(),
            'description' => 'Test voucher',
            'voucher_type' => 'manual',
            'is_posted' => true,
            'created_by' => $this->user->id,
        ]);

        // Credit 15000 to liability
        VoucherLine::create([
            'company_id' => $this->company->id,
            'voucher_id' => $voucher->id,
            'account_id' => $liabilityAccount->id,
            'description' => 'Increase liability',
            'debit' => 0,
            'credit' => 15000,
            'sort_order' => 0,
        ]);

        // Debit 5000 from liability (payment)
        VoucherLine::create([
            'company_id' => $this->company->id,
            'voucher_id' => $voucher->id,
            'account_id' => $liabilityAccount->id,
            'description' => 'Pay down liability',
            'debit' => 5000,
            'credit' => 0,
            'sort_order' => 1,
        ]);

        $balance = $this->service->getAccountBalance($liabilityAccount);

        // Liability account: credit - debit = 15000 - 5000 = 10000
        expect($balance)->toBe(10000.00);
    });

    test('calculates balance at specific date', function () {
        $bankAccount = $this->accounts['1920'];

        // Create voucher from yesterday
        $yesterdayVoucher = Voucher::create([
            'company_id' => $this->company->id,
            'voucher_date' => now()->subDay(),
            'description' => 'Yesterday voucher',
            'voucher_type' => 'manual',
            'is_posted' => true,
            'created_by' => $this->user->id,
        ]);

        VoucherLine::create([
            'company_id' => $this->company->id,
            'voucher_id' => $yesterdayVoucher->id,
            'account_id' => $bankAccount->id,
            'debit' => 5000,
            'credit' => 0,
            'sort_order' => 0,
        ]);

        // Create voucher from today
        $todayVoucher = Voucher::create([
            'company_id' => $this->company->id,
            'voucher_date' => now(),
            'description' => 'Today voucher',
            'voucher_type' => 'manual',
            'is_posted' => true,
            'created_by' => $this->user->id,
        ]);

        VoucherLine::create([
            'company_id' => $this->company->id,
            'voucher_id' => $todayVoucher->id,
            'account_id' => $bankAccount->id,
            'debit' => 3000,
            'credit' => 0,
            'sort_order' => 0,
        ]);

        // Balance as of yesterday
        $yesterdayBalance = $this->service->getAccountBalance($bankAccount, now()->subDay());
        expect($yesterdayBalance)->toBe(5000.00);

        // Balance as of today (includes both)
        $todayBalance = $this->service->getAccountBalance($bankAccount, now());
        expect($todayBalance)->toBe(8000.00);
    });

    test('ignores unposted vouchers', function () {
        $bankAccount = $this->accounts['1920'];

        // Create unposted voucher
        $voucher = Voucher::create([
            'company_id' => $this->company->id,
            'voucher_date' => now(),
            'description' => 'Unposted voucher',
            'voucher_type' => 'manual',
            'is_posted' => false, // Not posted
            'created_by' => $this->user->id,
        ]);

        VoucherLine::create([
            'company_id' => $this->company->id,
            'voucher_id' => $voucher->id,
            'account_id' => $bankAccount->id,
            'debit' => 10000,
            'credit' => 0,
            'sort_order' => 0,
        ]);

        $balance = $this->service->getAccountBalance($bankAccount);

        expect($balance)->toBe(0.00);
    });
});

describe('AccountingService Account Statement', function () {
    test('returns transactions for period', function () {
        $bankAccount = $this->accounts['1920'];

        // Create vouchers with transactions
        $voucher1 = Voucher::create([
            'company_id' => $this->company->id,
            'voucher_date' => now()->subDays(5),
            'description' => 'Voucher 1',
            'voucher_type' => 'manual',
            'is_posted' => true,
            'created_by' => $this->user->id,
        ]);

        VoucherLine::create([
            'company_id' => $this->company->id,
            'voucher_id' => $voucher1->id,
            'account_id' => $bankAccount->id,
            'description' => 'Transaction 1',
            'debit' => 5000,
            'credit' => 0,
            'sort_order' => 0,
        ]);

        $voucher2 = Voucher::create([
            'company_id' => $this->company->id,
            'voucher_date' => now()->subDays(2),
            'description' => 'Voucher 2',
            'voucher_type' => 'manual',
            'is_posted' => true,
            'created_by' => $this->user->id,
        ]);

        VoucherLine::create([
            'company_id' => $this->company->id,
            'voucher_id' => $voucher2->id,
            'account_id' => $bankAccount->id,
            'description' => 'Transaction 2',
            'debit' => 3000,
            'credit' => 0,
            'sort_order' => 0,
        ]);

        $statement = $this->service->getAccountStatement(
            $bankAccount,
            now()->subDays(10),
            now()
        );

        expect($statement)->toHaveCount(2)
            ->and($statement->pluck('description')->toArray())
            ->toContain('Transaction 1', 'Transaction 2');
    });

    test('excludes transactions outside period', function () {
        $bankAccount = $this->accounts['1920'];

        // Create old voucher (outside period)
        $oldVoucher = Voucher::create([
            'company_id' => $this->company->id,
            'voucher_date' => now()->subDays(30),
            'description' => 'Old voucher',
            'voucher_type' => 'manual',
            'is_posted' => true,
            'created_by' => $this->user->id,
        ]);

        VoucherLine::create([
            'company_id' => $this->company->id,
            'voucher_id' => $oldVoucher->id,
            'account_id' => $bankAccount->id,
            'description' => 'Old transaction',
            'debit' => 1000,
            'credit' => 0,
            'sort_order' => 0,
        ]);

        // Create recent voucher (inside period)
        $recentVoucher = Voucher::create([
            'company_id' => $this->company->id,
            'voucher_date' => now()->subDays(5),
            'description' => 'Recent voucher',
            'voucher_type' => 'manual',
            'is_posted' => true,
            'created_by' => $this->user->id,
        ]);

        VoucherLine::create([
            'company_id' => $this->company->id,
            'voucher_id' => $recentVoucher->id,
            'account_id' => $bankAccount->id,
            'description' => 'Recent transaction',
            'debit' => 2000,
            'credit' => 0,
            'sort_order' => 0,
        ]);

        $statement = $this->service->getAccountStatement(
            $bankAccount,
            now()->subDays(10),
            now()
        );

        expect($statement)->toHaveCount(1)
            ->and($statement->first()->description)->toBe('Recent transaction');
    });

    test('eager loads relationships', function () {
        $bankAccount = $this->accounts['1920'];
        $contact = Contact::factory()->customer()->create(['company_id' => $this->company->id]);

        $voucher = Voucher::create([
            'company_id' => $this->company->id,
            'voucher_date' => now(),
            'description' => 'Test voucher',
            'voucher_type' => 'manual',
            'is_posted' => true,
            'created_by' => $this->user->id,
        ]);

        VoucherLine::create([
            'company_id' => $this->company->id,
            'voucher_id' => $voucher->id,
            'account_id' => $bankAccount->id,
            'description' => 'Test',
            'debit' => 1000,
            'credit' => 0,
            'contact_id' => $contact->id,
            'sort_order' => 0,
        ]);

        $statement = $this->service->getAccountStatement(
            $bankAccount,
            now()->subDay(),
            now()->addDay()
        );

        $line = $statement->first();

        expect($line->relationLoaded('voucher'))->toBeTrue()
            ->and($line->relationLoaded('contact'))->toBeTrue();
    });
});
