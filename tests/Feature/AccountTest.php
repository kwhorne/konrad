<?php

use App\Models\Account;
use App\Models\Company;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherLine;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['onboarding_completed' => true]);
    $this->company = Company::factory()->withOwner($this->user)->create();
    $this->user->update(['current_company_id' => $this->company->id]);
    app()->instance('current.company', $this->company);
    $this->actingAs($this->user);
});

// Basic Creation
test('account can be created', function () {
    $account = Account::factory()->asset()->create([
        'account_number' => '1920',
        'name' => 'Bank',
    ]);

    expect($account)->toBeInstanceOf(Account::class);
    expect($account->account_number)->toBe('1920');
    expect($account->name)->toBe('Bank');
});

// Class Name Attribute
test('account class name returns correct norwegian translation', function (string $class, string $type, string $expectedName) {
    $account = Account::factory()->create([
        'account_class' => $class,
        'account_type' => $type,
        'account_number' => $class.'000',
    ]);

    expect($account->class_name)->toBe($expectedName);
})->with([
    ['1', 'asset', 'Eiendeler'],
    ['2', 'liability', 'Egenkapital og gjeld'],
    ['3', 'revenue', 'Salgsinntekter'],
    ['4', 'expense', 'Varekostnad'],
    ['5', 'expense', 'Lønn og personal'],
    ['6', 'expense', 'Avskrivninger'],
    ['7', 'expense', 'Andre driftskostnader'],
    ['8', 'expense', 'Finansposter'],
]);

// Type Name Attribute
test('account type name returns correct norwegian translation', function (string $type, string $expectedName) {
    $state = match ($type) {
        'asset' => 'asset',
        'liability' => 'liability',
        'equity' => 'equity',
        'revenue' => 'revenue',
        'expense' => 'expense',
    };

    $account = Account::factory()->$state()->create();

    expect($account->type_name)->toBe($expectedName);
})->with([
    ['asset', 'Eiendel'],
    ['liability', 'Gjeld'],
    ['equity', 'Egenkapital'],
    ['revenue', 'Inntekt'],
    ['expense', 'Kostnad'],
]);

// Balance Calculation
test('asset account balance is debit minus credit', function () {
    $account = Account::factory()->asset()->create();
    $voucher = Voucher::factory()->posted()->create(['is_balanced' => true]);

    VoucherLine::factory()->create([
        'voucher_id' => $voucher->id,
        'account_id' => $account->id,
        'debit' => 1000,
        'credit' => 0,
    ]);

    VoucherLine::factory()->create([
        'voucher_id' => $voucher->id,
        'account_id' => $account->id,
        'debit' => 0,
        'credit' => 300,
    ]);

    expect($account->balance)->toBe(700.0);
});

test('expense account balance is debit minus credit', function () {
    $account = Account::factory()->expense()->create();
    $voucher = Voucher::factory()->posted()->create(['is_balanced' => true]);

    VoucherLine::factory()->create([
        'voucher_id' => $voucher->id,
        'account_id' => $account->id,
        'debit' => 500,
        'credit' => 0,
    ]);

    expect($account->balance)->toBe(500.0);
});

test('revenue account balance is credit minus debit', function () {
    $account = Account::factory()->revenue()->create();
    $voucher = Voucher::factory()->posted()->create(['is_balanced' => true]);

    VoucherLine::factory()->create([
        'voucher_id' => $voucher->id,
        'account_id' => $account->id,
        'debit' => 0,
        'credit' => 1000,
    ]);

    expect($account->balance)->toBe(1000.0);
});

test('liability account balance is credit minus debit', function () {
    $account = Account::factory()->liability()->create();
    $voucher = Voucher::factory()->posted()->create(['is_balanced' => true]);

    VoucherLine::factory()->create([
        'voucher_id' => $voucher->id,
        'account_id' => $account->id,
        'debit' => 0,
        'credit' => 2000,
    ]);

    expect($account->balance)->toBe(2000.0);
});

test('account balance only includes posted vouchers', function () {
    $account = Account::factory()->asset()->create();
    $postedVoucher = Voucher::factory()->posted()->create(['is_balanced' => true]);
    $unpostedVoucher = Voucher::factory()->create();

    VoucherLine::factory()->create([
        'voucher_id' => $postedVoucher->id,
        'account_id' => $account->id,
        'debit' => 1000,
        'credit' => 0,
    ]);

    VoucherLine::factory()->create([
        'voucher_id' => $unpostedVoucher->id,
        'account_id' => $account->id,
        'debit' => 500,
        'credit' => 0,
    ]);

    expect($account->balance)->toBe(1000.0);
});

// Relationships
test('account can have parent', function () {
    $parent = Account::factory()->create(['account_number' => '1900', 'name' => 'Bankinnskudd']);
    $child = Account::factory()->create([
        'account_number' => '1920',
        'name' => 'Driftskonto',
        'parent_id' => $parent->id,
    ]);

    expect($child->parent->id)->toBe($parent->id);
});

test('account can have children', function () {
    $parent = Account::factory()->create(['account_number' => '1900']);
    Account::factory()->count(3)->create(['parent_id' => $parent->id]);

    expect($parent->children)->toHaveCount(3);
});

test('account children are ordered by account number', function () {
    $parent = Account::factory()->create(['account_number' => '1900']);
    Account::factory()->create(['account_number' => '1930', 'parent_id' => $parent->id]);
    Account::factory()->create(['account_number' => '1910', 'parent_id' => $parent->id]);
    Account::factory()->create(['account_number' => '1920', 'parent_id' => $parent->id]);

    $numbers = $parent->children->pluck('account_number')->toArray();

    expect($numbers)->toBe(['1910', '1920', '1930']);
});

test('account can have voucher lines', function () {
    $account = Account::factory()->create();
    $voucher = Voucher::factory()->create();

    VoucherLine::factory()->count(3)->create([
        'voucher_id' => $voucher->id,
        'account_id' => $account->id,
    ]);

    expect($account->voucherLines)->toHaveCount(3);
});

// Scopes
test('active scope filters correctly', function () {
    Account::factory()->count(3)->create(['is_active' => true]);
    Account::factory()->count(2)->inactive()->create();

    expect(Account::active()->count())->toBe(3);
});

test('byClass scope filters correctly', function () {
    Account::factory()->create(['account_class' => '1']);
    Account::factory()->create(['account_class' => '1']);
    Account::factory()->create(['account_class' => '3']);

    expect(Account::byClass('1')->count())->toBe(2);
});

test('byType scope filters correctly', function () {
    Account::factory()->count(2)->asset()->create();
    Account::factory()->count(3)->revenue()->create();

    expect(Account::byType('asset')->count())->toBe(2);
    expect(Account::byType('revenue')->count())->toBe(3);
});

test('ordered scope sorts by account number', function () {
    Account::factory()->create(['account_number' => '3000']);
    Account::factory()->create(['account_number' => '1000']);
    Account::factory()->create(['account_number' => '2000']);

    $numbers = Account::ordered()->pluck('account_number')->toArray();

    expect($numbers)->toBe(['1000', '2000', '3000']);
});

test('rootLevel scope filters accounts without parent', function () {
    $root1 = Account::factory()->create(['parent_id' => null]);
    $root2 = Account::factory()->create(['parent_id' => null]);
    Account::factory()->create(['parent_id' => $root1->id]);

    expect(Account::rootLevel()->count())->toBe(2);
});

// Boolean Casts
test('account is_system is boolean', function () {
    $account = Account::factory()->system()->create();

    expect($account->is_system)->toBeTrue();
    expect($account->is_system)->toBeBool();
});

test('account is_active is boolean', function () {
    $account = Account::factory()->create(['is_active' => true]);

    expect($account->is_active)->toBeTrue();
    expect($account->is_active)->toBeBool();
});

// Factory States
test('account factory asset state works', function () {
    $account = Account::factory()->asset()->create();

    expect($account->account_type)->toBe('asset');
    expect($account->account_class)->toBe('1');
});

test('account factory liability state works', function () {
    $account = Account::factory()->liability()->create();

    expect($account->account_type)->toBe('liability');
    expect($account->account_class)->toBe('2');
});

test('account factory equity state works', function () {
    $account = Account::factory()->equity()->create();

    expect($account->account_type)->toBe('equity');
    expect($account->account_class)->toBe('2');
});

test('account factory revenue state works', function () {
    $account = Account::factory()->revenue()->create();

    expect($account->account_type)->toBe('revenue');
    expect($account->account_class)->toBe('3');
});

test('account factory expense state works', function () {
    $account = Account::factory()->expense()->create();

    expect($account->account_type)->toBe('expense');
});

test('account factory inactive state works', function () {
    $account = Account::factory()->inactive()->create();

    expect($account->is_active)->toBeFalse();
});

test('account factory system state works', function () {
    $account = Account::factory()->system()->create();

    expect($account->is_system)->toBeTrue();
});
