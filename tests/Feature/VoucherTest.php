<?php

use App\Models\Account;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherLine;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

// Voucher Number Generation
test('voucher has auto-generated number', function () {
    $voucher = Voucher::factory()->create();

    $year = date('Y');
    expect($voucher->voucher_number)->toMatch("/^BIL-{$year}-\d{4}$/");
});

test('voucher numbers increment correctly', function () {
    $voucher1 = Voucher::factory()->create();
    $voucher2 = Voucher::factory()->create();

    preg_match('/BIL-\d{4}-(\d+)/', $voucher1->voucher_number, $matches1);
    preg_match('/BIL-\d{4}-(\d+)/', $voucher2->voucher_number, $matches2);

    expect((int) $matches2[1])->toBe((int) $matches1[1] + 1);
});

test('voucher number is not overwritten if provided', function () {
    $voucher = Voucher::factory()->create(['voucher_number' => 'CUSTOM-001']);

    expect($voucher->voucher_number)->toBe('CUSTOM-001');
});

test('voucher date defaults to now if not provided', function () {
    $voucher = Voucher::factory()->create(['voucher_date' => null]);

    expect($voucher->voucher_date->toDateString())->toBe(now()->toDateString());
});

// Voucher Type Labels
test('voucher type label returns correct norwegian translation', function (string $type, string $expectedLabel) {
    $voucher = Voucher::factory()->create(['voucher_type' => $type]);

    expect($voucher->voucher_type_label)->toBe($expectedLabel);
})->with([
    ['manual', 'Manuell'],
    ['invoice', 'Utgående faktura'],
    ['payment', 'Innbetaling'],
    ['supplier_invoice', 'Leverandørfaktura'],
    ['supplier_payment', 'Utbetaling'],
]);

// Relationships
test('voucher belongs to creator', function () {
    $creator = User::factory()->create();
    $voucher = Voucher::factory()->create(['created_by' => $creator->id]);

    expect($voucher->creator->id)->toBe($creator->id);
});

test('voucher can have lines', function () {
    $voucher = Voucher::factory()->create();
    $account = Account::factory()->create();

    VoucherLine::factory()->create([
        'voucher_id' => $voucher->id,
        'account_id' => $account->id,
        'debit' => 1000,
        'credit' => 0,
    ]);

    expect($voucher->lines)->toHaveCount(1);
});

// Balance Calculation
test('voucher recalculates totals when lines are added', function () {
    $voucher = Voucher::factory()->create();
    $debitAccount = Account::factory()->asset()->create();
    $creditAccount = Account::factory()->revenue()->create();

    VoucherLine::factory()->create([
        'voucher_id' => $voucher->id,
        'account_id' => $debitAccount->id,
        'debit' => 1000,
        'credit' => 0,
    ]);

    $voucher->refresh();
    expect((float) $voucher->total_debit)->toBe(1000.0);
    expect((float) $voucher->total_credit)->toBe(0.0);
    expect($voucher->is_balanced)->toBeFalse();

    VoucherLine::factory()->create([
        'voucher_id' => $voucher->id,
        'account_id' => $creditAccount->id,
        'debit' => 0,
        'credit' => 1000,
    ]);

    $voucher->refresh();
    expect((float) $voucher->total_debit)->toBe(1000.0);
    expect((float) $voucher->total_credit)->toBe(1000.0);
    expect($voucher->is_balanced)->toBeTrue();
});

test('voucher recalculates totals when lines are deleted', function () {
    $voucher = Voucher::factory()->create();
    $debitAccount = Account::factory()->asset()->create();
    $creditAccount = Account::factory()->revenue()->create();

    $debitLine = VoucherLine::factory()->create([
        'voucher_id' => $voucher->id,
        'account_id' => $debitAccount->id,
        'debit' => 1000,
        'credit' => 0,
    ]);

    VoucherLine::factory()->create([
        'voucher_id' => $voucher->id,
        'account_id' => $creditAccount->id,
        'debit' => 0,
        'credit' => 1000,
    ]);

    $voucher->refresh();
    expect($voucher->is_balanced)->toBeTrue();

    $debitLine->delete();
    $voucher->recalculateTotals();

    expect((float) $voucher->total_debit)->toBe(0.0);
    expect((float) $voucher->total_credit)->toBe(1000.0);
    expect($voucher->is_balanced)->toBeFalse();
});

// Posting
test('balanced voucher can be posted', function () {
    $voucher = Voucher::factory()->create(['is_balanced' => true, 'is_posted' => false]);

    $result = $voucher->post();

    expect($result)->toBeTrue();
    expect($voucher->is_posted)->toBeTrue();
    expect($voucher->posted_at)->not->toBeNull();
});

test('unbalanced voucher cannot be posted', function () {
    $voucher = Voucher::factory()->unbalanced()->create();

    $result = $voucher->post();

    expect($result)->toBeFalse();
    expect($voucher->is_posted)->toBeFalse();
});

test('already posted voucher cannot be posted again', function () {
    $voucher = Voucher::factory()->posted()->create(['is_balanced' => true]);

    $result = $voucher->post();

    expect($result)->toBeFalse();
});

// Scopes
test('posted scope filters correctly', function () {
    Voucher::factory()->count(2)->posted()->create(['is_balanced' => true]);
    Voucher::factory()->count(3)->create();

    expect(Voucher::posted()->count())->toBe(2);
});

test('unposted scope filters correctly', function () {
    Voucher::factory()->count(2)->posted()->create(['is_balanced' => true]);
    Voucher::factory()->count(3)->create();

    expect(Voucher::unposted()->count())->toBe(3);
});

test('balanced scope filters correctly', function () {
    Voucher::factory()->count(2)->create(['is_balanced' => true]);
    Voucher::factory()->count(3)->unbalanced()->create();

    expect(Voucher::balanced()->count())->toBe(2);
});

test('byType scope filters correctly', function () {
    Voucher::factory()->count(2)->invoice()->create();
    Voucher::factory()->count(3)->payment()->create();

    expect(Voucher::byType('invoice')->count())->toBe(2);
    expect(Voucher::byType('payment')->count())->toBe(3);
});

test('ordered scope sorts by date descending', function () {
    $old = Voucher::factory()->create(['voucher_date' => now()->subDays(10)]);
    $new = Voucher::factory()->create(['voucher_date' => now()]);
    $middle = Voucher::factory()->create(['voucher_date' => now()->subDays(5)]);

    $vouchers = Voucher::ordered()->pluck('id')->toArray();

    expect($vouchers[0])->toBe($new->id);
    expect($vouchers[1])->toBe($middle->id);
    expect($vouchers[2])->toBe($old->id);
});

// Soft Deletes
test('voucher can be soft deleted', function () {
    $voucher = Voucher::factory()->create();

    $voucher->delete();

    expect($voucher->trashed())->toBeTrue();
    expect(Voucher::count())->toBe(0);
    expect(Voucher::withTrashed()->count())->toBe(1);
});

// Factory States
test('voucher factory posted state works', function () {
    $voucher = Voucher::factory()->posted()->create(['is_balanced' => true]);

    expect($voucher->is_posted)->toBeTrue();
    expect($voucher->posted_at)->not->toBeNull();
});

test('voucher factory unbalanced state works', function () {
    $voucher = Voucher::factory()->unbalanced()->create();

    expect($voucher->is_balanced)->toBeFalse();
});

test('voucher factory type states work', function () {
    expect(Voucher::factory()->invoice()->create()->voucher_type)->toBe('invoice');
    expect(Voucher::factory()->payment()->create()->voucher_type)->toBe('payment');
    expect(Voucher::factory()->supplierInvoice()->create()->voucher_type)->toBe('supplier_invoice');
    expect(Voucher::factory()->supplierPayment()->create()->voucher_type)->toBe('supplier_payment');
});
