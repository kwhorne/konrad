<?php

use App\Models\Company;
use App\Models\IncomingVoucher;
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

// Admin can do everything
it('allows admin to viewAny incoming vouchers', function () {
    expect($this->admin->can('viewAny', IncomingVoucher::class))->toBeTrue();
});

it('allows admin to view an incoming voucher', function () {
    $voucher = IncomingVoucher::factory()->create(['company_id' => $this->company->id]);
    expect($this->admin->can('view', $voucher))->toBeTrue();
});

it('allows admin to create incoming vouchers', function () {
    expect($this->admin->can('create', IncomingVoucher::class))->toBeTrue();
});

it('allows admin to update an incoming voucher', function () {
    $voucher = IncomingVoucher::factory()->create(['company_id' => $this->company->id]);
    expect($this->admin->can('update', $voucher))->toBeTrue();
});

it('allows admin to delete an incoming voucher', function () {
    $voucher = IncomingVoucher::factory()->create(['company_id' => $this->company->id]);
    expect($this->admin->can('delete', $voucher))->toBeTrue();
});

it('allows admin to attest an incoming voucher', function () {
    $voucher = IncomingVoucher::factory()->create(['company_id' => $this->company->id]);
    expect($this->admin->can('attest', $voucher))->toBeTrue();
});

it('allows admin to approve an incoming voucher', function () {
    $voucher = IncomingVoucher::factory()->create(['company_id' => $this->company->id]);
    expect($this->admin->can('approve', $voucher))->toBeTrue();
});

it('allows admin to reject an incoming voucher', function () {
    $voucher = IncomingVoucher::factory()->create(['company_id' => $this->company->id]);
    expect($this->admin->can('reject', $voucher))->toBeTrue();
});

// Economy user - basic permissions
it('allows economy user to viewAny incoming vouchers', function () {
    expect($this->economyUser->can('viewAny', IncomingVoucher::class))->toBeTrue();
});

it('allows economy user to view an incoming voucher', function () {
    $voucher = IncomingVoucher::factory()->create(['company_id' => $this->company->id]);
    expect($this->economyUser->can('view', $voucher))->toBeTrue();
});

it('allows economy user to create incoming vouchers', function () {
    expect($this->economyUser->can('create', IncomingVoucher::class))->toBeTrue();
});

it('allows economy user to attest an incoming voucher', function () {
    $voucher = IncomingVoucher::factory()->create(['company_id' => $this->company->id]);
    expect($this->economyUser->can('attest', $voucher))->toBeTrue();
});

it('allows economy user to approve an incoming voucher', function () {
    $voucher = IncomingVoucher::factory()->create(['company_id' => $this->company->id]);
    expect($this->economyUser->can('approve', $voucher))->toBeTrue();
});

it('allows economy user to reject an incoming voucher', function () {
    $voucher = IncomingVoucher::factory()->create(['company_id' => $this->company->id]);
    expect($this->economyUser->can('reject', $voucher))->toBeTrue();
});

// Economy user - update/delete allowed when status is not approved/posted
it('allows economy user to update incoming voucher when status is not approved or posted', function (string $status) {
    $voucher = IncomingVoucher::factory()->create([
        'company_id' => $this->company->id,
        'status' => $status,
    ]);
    expect($this->economyUser->can('update', $voucher))->toBeTrue();
})->with([
    'pending' => IncomingVoucher::STATUS_PENDING,
    'parsing' => IncomingVoucher::STATUS_PARSING,
    'parsed' => IncomingVoucher::STATUS_PARSED,
    'attested' => IncomingVoucher::STATUS_ATTESTED,
    'rejected' => IncomingVoucher::STATUS_REJECTED,
]);

it('allows economy user to delete incoming voucher when status is not approved or posted', function (string $status) {
    $voucher = IncomingVoucher::factory()->create([
        'company_id' => $this->company->id,
        'status' => $status,
    ]);
    expect($this->economyUser->can('delete', $voucher))->toBeTrue();
})->with([
    'pending' => IncomingVoucher::STATUS_PENDING,
    'parsing' => IncomingVoucher::STATUS_PARSING,
    'parsed' => IncomingVoucher::STATUS_PARSED,
    'attested' => IncomingVoucher::STATUS_ATTESTED,
    'rejected' => IncomingVoucher::STATUS_REJECTED,
]);

// Economy user - update/delete denied when status is approved or posted
it('denies economy user to update incoming voucher when status is approved or posted', function (string $status) {
    $voucher = IncomingVoucher::factory()->create([
        'company_id' => $this->company->id,
        'status' => $status,
    ]);
    expect($this->economyUser->can('update', $voucher))->toBeFalse();
})->with([
    'approved' => IncomingVoucher::STATUS_APPROVED,
    'posted' => IncomingVoucher::STATUS_POSTED,
]);

it('denies economy user to delete incoming voucher when status is approved or posted', function (string $status) {
    $voucher = IncomingVoucher::factory()->create([
        'company_id' => $this->company->id,
        'status' => $status,
    ]);
    expect($this->economyUser->can('delete', $voucher))->toBeFalse();
})->with([
    'approved' => IncomingVoucher::STATUS_APPROVED,
    'posted' => IncomingVoucher::STATUS_POSTED,
]);

// Regular user denied for all
it('denies regular user to viewAny incoming vouchers', function () {
    expect($this->regularUser->can('viewAny', IncomingVoucher::class))->toBeFalse();
});

it('denies regular user to view an incoming voucher', function () {
    $voucher = IncomingVoucher::factory()->create(['company_id' => $this->company->id]);
    expect($this->regularUser->can('view', $voucher))->toBeFalse();
});

it('denies regular user to create incoming vouchers', function () {
    expect($this->regularUser->can('create', IncomingVoucher::class))->toBeFalse();
});

it('denies regular user to update an incoming voucher', function () {
    $voucher = IncomingVoucher::factory()->pending()->create(['company_id' => $this->company->id]);
    expect($this->regularUser->can('update', $voucher))->toBeFalse();
});

it('denies regular user to delete an incoming voucher', function () {
    $voucher = IncomingVoucher::factory()->pending()->create(['company_id' => $this->company->id]);
    expect($this->regularUser->can('delete', $voucher))->toBeFalse();
});

it('denies regular user to attest an incoming voucher', function () {
    $voucher = IncomingVoucher::factory()->create(['company_id' => $this->company->id]);
    expect($this->regularUser->can('attest', $voucher))->toBeFalse();
});

it('denies regular user to approve an incoming voucher', function () {
    $voucher = IncomingVoucher::factory()->create(['company_id' => $this->company->id]);
    expect($this->regularUser->can('approve', $voucher))->toBeFalse();
});

it('denies regular user to reject an incoming voucher', function () {
    $voucher = IncomingVoucher::factory()->create(['company_id' => $this->company->id]);
    expect($this->regularUser->can('reject', $voucher))->toBeFalse();
});
