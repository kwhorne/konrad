<?php

use App\Models\Account;
use App\Models\Contact;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherLine;
use App\Services\VoucherService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(VoucherService::class);
});

it('calculates total debit from working lines', function () {
    $lines = [
        ['debit' => 1000, 'credit' => 0],
        ['debit' => 500, 'credit' => 0],
        ['debit' => 0, 'credit' => 1500],
    ];

    expect($this->service->calculateTotalDebit($lines))->toBe(1500.0);
});

it('calculates total credit from working lines', function () {
    $lines = [
        ['debit' => 1000, 'credit' => 0],
        ['debit' => 0, 'credit' => 500],
        ['debit' => 0, 'credit' => 500],
    ];

    expect($this->service->calculateTotalCredit($lines))->toBe(1000.0);
});

it('calculates difference between debit and credit', function () {
    $balancedLines = [
        ['debit' => 1000, 'credit' => 0],
        ['debit' => 0, 'credit' => 1000],
    ];

    $unbalancedLines = [
        ['debit' => 1000, 'credit' => 0],
        ['debit' => 0, 'credit' => 800],
    ];

    expect($this->service->calculateDifference($balancedLines))->toBe(0.0);
    expect($this->service->calculateDifference($unbalancedLines))->toBe(200.0);
});

it('checks if lines are balanced', function () {
    $balancedLines = [
        ['debit' => 1000, 'credit' => 0],
        ['debit' => 0, 'credit' => 1000],
    ];

    $unbalancedLines = [
        ['debit' => 1000, 'credit' => 0],
        ['debit' => 0, 'credit' => 800],
    ];

    $emptyLines = [];

    expect($this->service->isBalanced($balancedLines))->toBeTrue();
    expect($this->service->isBalanced($unbalancedLines))->toBeFalse();
    expect($this->service->isBalanced($emptyLines))->toBeFalse();
});

it('validates line amounts - both zero', function () {
    $errors = $this->service->validateLineAmounts(0, 0);

    expect($errors)->not->toBeNull();
    expect($errors)->toHaveKey('debit');
});

it('validates line amounts - both filled', function () {
    $errors = $this->service->validateLineAmounts(100, 100);

    expect($errors)->not->toBeNull();
    expect($errors)->toHaveKey('debit');
});

it('validates line amounts - valid debit', function () {
    $errors = $this->service->validateLineAmounts(100, 0);

    expect($errors)->toBeNull();
});

it('validates line amounts - valid credit', function () {
    $errors = $this->service->validateLineAmounts(0, 100);

    expect($errors)->toBeNull();
});

it('builds line data from inputs', function () {
    $account = Account::factory()->create([
        'account_number' => '1920',
        'name' => 'Bankkonto',
    ]);
    $contact = Contact::factory()->create([
        'company_name' => 'Test Company',
    ]);

    $lineData = $this->service->buildLineData(
        $account->id,
        'Test description',
        1000,
        0,
        $contact->id,
        null
    );

    expect($lineData['account_id'])->toBe($account->id);
    expect($lineData['account_number'])->toBe('1920');
    expect($lineData['account_name'])->toBe('Bankkonto');
    expect($lineData['description'])->toBe('Test description');
    expect($lineData['debit'])->toBe(1000.0);
    expect($lineData['credit'])->toBe(0.0);
    expect($lineData['contact_id'])->toBe($contact->id);
    expect($lineData['contact_name'])->toBe('Test Company');
    expect($lineData['id'])->toBeNull();
});

it('converts voucher lines to working lines format', function () {
    $account = Account::factory()->create();
    $contact = Contact::factory()->create();
    $voucher = Voucher::factory()->create();

    VoucherLine::factory()->debit(1000)->create([
        'voucher_id' => $voucher->id,
        'account_id' => $account->id,
        'contact_id' => $contact->id,
        'description' => 'Debit line',
    ]);

    VoucherLine::factory()->credit(1000)->create([
        'voucher_id' => $voucher->id,
        'account_id' => $account->id,
        'description' => 'Credit line',
    ]);

    $voucher->load('lines.account', 'lines.contact');
    $workingLines = $this->service->voucherLinesToWorkingLines($voucher);

    expect($workingLines)->toHaveCount(2);
    expect($workingLines[0]['account_id'])->toBe($account->id);
    expect($workingLines[0]['description'])->toBe('Debit line');
});

it('creates a voucher with lines', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $account1 = Account::factory()->asset()->create();
    $account2 = Account::factory()->expense()->create();

    $voucherData = [
        'voucher_date' => '2026-01-15',
        'description' => 'Test voucher',
    ];

    $lines = [
        [
            'id' => null,
            'account_id' => $account1->id,
            'description' => 'Debit',
            'debit' => 1000,
            'credit' => 0,
            'contact_id' => null,
        ],
        [
            'id' => null,
            'account_id' => $account2->id,
            'description' => 'Credit',
            'debit' => 0,
            'credit' => 1000,
            'contact_id' => null,
        ],
    ];

    $voucher = $this->service->createVoucher($voucherData, $lines);

    expect($voucher)->toBeInstanceOf(Voucher::class);
    expect($voucher->description)->toBe('Test voucher');
    expect($voucher->voucher_type)->toBe('manual');
    expect($voucher->lines)->toHaveCount(2);
    expect($voucher->is_balanced)->toBeTrue();
    expect((float) $voucher->total_debit)->toBe(1000.0);
    expect((float) $voucher->total_credit)->toBe(1000.0);
});

it('updates an existing voucher with lines', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $account = Account::factory()->create();
    $voucher = Voucher::factory()->create();

    $existingLine = VoucherLine::factory()->debit(500)->create([
        'voucher_id' => $voucher->id,
        'account_id' => $account->id,
    ]);

    $voucherData = [
        'voucher_date' => '2026-01-20',
        'description' => 'Updated description',
    ];

    $lines = [
        [
            'id' => $existingLine->id,
            'account_id' => $account->id,
            'description' => 'Updated line',
            'debit' => 1500,
            'credit' => 0,
            'contact_id' => null,
        ],
        [
            'id' => null,
            'account_id' => $account->id,
            'description' => 'New credit line',
            'debit' => 0,
            'credit' => 1500,
            'contact_id' => null,
        ],
    ];

    $updated = $this->service->updateVoucher($voucher, $voucherData, $lines);

    expect($updated->description)->toBe('Updated description');
    expect($updated->lines)->toHaveCount(2);
    expect((float) $updated->total_debit)->toBe(1500.0);
});

it('throws exception when updating posted voucher', function () {
    $voucher = Voucher::factory()->posted()->create();

    $this->service->updateVoucher($voucher, ['voucher_date' => '2026-01-20', 'description' => 'Test'], []);
})->throws(\InvalidArgumentException::class, 'Kan ikke redigere et bokført bilag');

it('posts a voucher', function () {
    $account = Account::factory()->create();
    $voucher = Voucher::factory()->create(['is_balanced' => true]);

    VoucherLine::factory()->debit(1000)->create([
        'voucher_id' => $voucher->id,
        'account_id' => $account->id,
    ]);
    VoucherLine::factory()->credit(1000)->create([
        'voucher_id' => $voucher->id,
        'account_id' => $account->id,
    ]);

    $voucher->recalculateTotals();

    $result = $this->service->postVoucher($voucher);

    expect($result)->toBeTrue();
    expect($voucher->fresh()->is_posted)->toBeTrue();
});

it('cannot post unbalanced voucher', function () {
    $voucher = Voucher::factory()->unbalanced()->create();

    $result = $this->service->postVoucher($voucher);

    expect($result)->toBeFalse();
});

it('deletes a voucher', function () {
    $voucher = Voucher::factory()->create();

    $result = $this->service->deleteVoucher($voucher);

    expect($result)->toBeTrue();
    expect(Voucher::find($voucher->id))->toBeNull();
});

it('cannot delete posted voucher', function () {
    $voucher = Voucher::factory()->posted()->create();

    $result = $this->service->deleteVoucher($voucher);

    expect($result)->toBeFalse();
    expect(Voucher::find($voucher->id))->not->toBeNull();
});

it('validates voucher with too few lines', function () {
    $lines = [
        ['debit' => 1000, 'credit' => 0],
    ];

    $errors = $this->service->validateVoucher($lines);

    expect($errors)->not->toBeNull();
    expect($errors)->toHaveKey('lines');
});

it('validates unbalanced voucher', function () {
    $lines = [
        ['debit' => 1000, 'credit' => 0],
        ['debit' => 0, 'credit' => 800],
    ];

    $errors = $this->service->validateVoucher($lines);

    expect($errors)->not->toBeNull();
    expect($errors)->toHaveKey('lines');
});

it('validates balanced voucher with enough lines', function () {
    $lines = [
        ['debit' => 1000, 'credit' => 0],
        ['debit' => 0, 'credit' => 1000],
    ];

    $errors = $this->service->validateVoucher($lines);

    expect($errors)->toBeNull();
});

it('gets voucher summary', function () {
    $account = Account::factory()->create();
    $voucher = Voucher::factory()->create();

    VoucherLine::factory()->debit(2000)->create([
        'voucher_id' => $voucher->id,
        'account_id' => $account->id,
    ]);
    VoucherLine::factory()->credit(2000)->create([
        'voucher_id' => $voucher->id,
        'account_id' => $account->id,
    ]);

    $voucher->recalculateTotals();

    $summary = $this->service->getVoucherSummary($voucher);

    expect($summary['total_debit'])->toBe(2000.0);
    expect($summary['total_credit'])->toBe(2000.0);
    expect($summary['difference'])->toBe(0.0);
    expect($summary['is_balanced'])->toBeTrue();
    expect($summary['line_count'])->toBe(2);
});
