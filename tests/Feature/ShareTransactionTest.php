<?php

use App\Models\ShareClass;
use App\Models\Shareholder;
use App\Models\ShareTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

// Transaction Number Generation
test('share transaction has auto-generated number', function () {
    $transaction = ShareTransaction::factory()->create();

    $year = date('Y');
    expect($transaction->transaction_number)->toMatch("/^AKS-{$year}-\d{4}$/");
});

test('share transaction numbers increment correctly', function () {
    $transaction1 = ShareTransaction::factory()->create();
    $transaction2 = ShareTransaction::factory()->create();

    preg_match('/AKS-\d{4}-(\d+)/', $transaction1->transaction_number, $matches1);
    preg_match('/AKS-\d{4}-(\d+)/', $transaction2->transaction_number, $matches2);

    expect((int) $matches2[1])->toBe((int) $matches1[1] + 1);
});

// Transaction Type Labels
test('share transaction type label returns correct norwegian translation', function (string $type, string $expectedLabel) {
    $transaction = ShareTransaction::factory()->create(['transaction_type' => $type]);

    expect($transaction->getTransactionTypeLabel())->toBe($expectedLabel);
})->with([
    ['issue', 'Emisjon'],
    ['transfer', 'Overdragelse'],
    ['redemption', 'Innløsning'],
    ['split', 'Aksjesplitt'],
    ['merger', 'Fusjon'],
    ['bonus', 'Fondsemisjon'],
]);

// Transaction Type Badge Colors
test('share transaction type badge color returns correct color', function (string $type, string $expectedColor) {
    $transaction = ShareTransaction::factory()->create(['transaction_type' => $type]);

    expect($transaction->getTransactionTypeBadgeColor())->toBe($expectedColor);
})->with([
    ['issue', 'success'],
    ['transfer', 'info'],
    ['redemption', 'warning'],
    ['split', 'primary'],
    ['merger', 'danger'],
    ['bonus', 'success'],
]);

// Formatted Values
test('share transaction formatted price per share includes currency', function () {
    $transaction = ShareTransaction::factory()->create([
        'price_per_share' => 125.5000,
        'currency' => 'NOK',
    ]);

    expect($transaction->getFormattedPricePerShare())->toBe('125,5000 NOK');
});

test('share transaction formatted price per share returns dash when null', function () {
    $transaction = ShareTransaction::factory()->create(['price_per_share' => null]);

    expect($transaction->getFormattedPricePerShare())->toBe('-');
});

test('share transaction formatted total amount includes currency', function () {
    $transaction = ShareTransaction::factory()->create([
        'total_amount' => 125000.00,
        'currency' => 'NOK',
    ]);

    expect($transaction->getFormattedTotalAmount())->toBe('125 000,00 NOK');
});

// Relationships
test('share transaction belongs to share class', function () {
    $shareClass = ShareClass::factory()->create();
    $transaction = ShareTransaction::factory()->create(['share_class_id' => $shareClass->id]);

    expect($transaction->shareClass->id)->toBe($shareClass->id);
});

test('share transaction belongs to from shareholder', function () {
    $shareholder = Shareholder::factory()->create();
    $transaction = ShareTransaction::factory()->transfer()->create(['from_shareholder_id' => $shareholder->id]);

    expect($transaction->fromShareholder->id)->toBe($shareholder->id);
});

test('share transaction belongs to to shareholder', function () {
    $shareholder = Shareholder::factory()->create();
    $transaction = ShareTransaction::factory()->create(['to_shareholder_id' => $shareholder->id]);

    expect($transaction->toShareholder->id)->toBe($shareholder->id);
});

test('share transaction belongs to creator', function () {
    $creator = User::factory()->create();
    $transaction = ShareTransaction::factory()->create(['created_by' => $creator->id]);

    expect($transaction->creator->id)->toBe($creator->id);
});

// Business Methods
test('share transaction isIncrease returns true for increase types', function (string $type) {
    $transaction = ShareTransaction::factory()->create(['transaction_type' => $type]);

    expect($transaction->isIncrease())->toBeTrue();
})->with(['issue', 'bonus', 'split']);

test('share transaction isDecrease returns true for decrease types', function (string $type) {
    $transaction = ShareTransaction::factory()->create(['transaction_type' => $type]);

    expect($transaction->isDecrease())->toBeTrue();
})->with(['redemption', 'merger']);

test('share transaction isTransfer returns true for transfer type', function () {
    $transaction = ShareTransaction::factory()->transfer()->create();

    expect($transaction->isTransfer())->toBeTrue();
});

test('share transaction affectsShareholder returns true when shareholder is involved', function () {
    $shareholder = Shareholder::factory()->create();
    $transaction = ShareTransaction::factory()->create(['to_shareholder_id' => $shareholder->id]);

    expect($transaction->affectsShareholder($shareholder->id))->toBeTrue();
});

test('share transaction getShareChangeForShareholder returns positive for receiver', function () {
    $shareholder = Shareholder::factory()->create();
    $transaction = ShareTransaction::factory()->create([
        'to_shareholder_id' => $shareholder->id,
        'number_of_shares' => 100,
    ]);

    expect($transaction->getShareChangeForShareholder($shareholder->id))->toBe(100);
});

test('share transaction getShareChangeForShareholder returns negative for sender', function () {
    $shareholder = Shareholder::factory()->create();
    $transaction = ShareTransaction::factory()->transfer()->create([
        'from_shareholder_id' => $shareholder->id,
        'number_of_shares' => 100,
    ]);

    expect($transaction->getShareChangeForShareholder($shareholder->id))->toBe(-100);
});

// Scopes
test('ofType scope filters correctly', function () {
    ShareTransaction::factory()->count(2)->issue()->create();
    ShareTransaction::factory()->count(3)->transfer()->create();

    expect(ShareTransaction::ofType('issue')->count())->toBe(2);
    expect(ShareTransaction::ofType('transfer')->count())->toBe(3);
});

test('inYear scope filters correctly', function () {
    $currentYear = (int) date('Y');

    ShareTransaction::factory()->create(['transaction_date' => "{$currentYear}-01-15"]);
    ShareTransaction::factory()->create(['transaction_date' => "{$currentYear}-06-15"]);

    expect(ShareTransaction::inYear($currentYear)->count())->toBe(2);
    expect(ShareTransaction::inYear($currentYear - 1)->count())->toBe(0);
});

test('forShareholder scope filters correctly', function () {
    $shareholder = Shareholder::factory()->create();
    ShareTransaction::factory()->create(['from_shareholder_id' => $shareholder->id]);
    ShareTransaction::factory()->create(['to_shareholder_id' => $shareholder->id]);
    ShareTransaction::factory()->count(3)->create();

    expect(ShareTransaction::forShareholder($shareholder->id)->count())->toBe(2);
});

// Factory States
test('share transaction factory issue state works', function () {
    $transaction = ShareTransaction::factory()->issue()->create();

    expect($transaction->transaction_type)->toBe('issue');
    expect($transaction->from_shareholder_id)->toBeNull();
});

test('share transaction factory transfer state works', function () {
    $transaction = ShareTransaction::factory()->transfer()->create();

    expect($transaction->transaction_type)->toBe('transfer');
});

test('share transaction factory redemption state works', function () {
    $transaction = ShareTransaction::factory()->redemption()->create();

    expect($transaction->transaction_type)->toBe('redemption');
    expect($transaction->to_shareholder_id)->toBeNull();
});
