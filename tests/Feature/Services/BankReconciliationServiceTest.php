<?php

use App\Models\Account;
use App\Models\BankReconciliationDraft;
use App\Models\BankStatement;
use App\Models\BankTransaction;
use App\Models\BankTransactionMatch;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\User;
use App\Services\BankReconciliationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->company = Company::factory()->create();
    $this->user = User::factory()->create();
    $this->user->companies()->attach($this->company->id, ['role' => 'owner']);

    $this->actingAs($this->user);
    session(['current_company_id' => $this->company->id]);
    app()->instance('current.company', $this->company);

    $this->service = app(BankReconciliationService::class);
});

it('imports a csv file and creates bank statement with transactions', function () {
    Storage::fake('local');

    $csvContent = "Dato;Tekst;Ut;Inn;Referanse\n";
    $csvContent .= "01.01.2025;Innbetaling kunde;;5000,00;1234567890\n";
    $csvContent .= "02.01.2025;Betaling leverandor;2500,00;;\n";
    $csvContent .= "03.01.2025;Annen inntekt;;1000,50;\n";

    $file = UploadedFile::fake()->createWithContent('test.csv', $csvContent);

    $statement = $this->service->importCsvFile($file);

    expect($statement)->toBeInstanceOf(BankStatement::class);
    expect($statement->transaction_count)->toBe(3);
    expect($statement->company_id)->toBe($this->company->id);
    expect($statement->status)->toBe(BankStatement::STATUS_PENDING);

    $transactions = $statement->transactions;
    expect($transactions)->toHaveCount(3);

    $credit = $transactions->firstWhere('transaction_type', 'credit');
    expect((float) $credit->amount)->toBe(5000.00);

    $debit = $transactions->firstWhere('transaction_type', 'debit');
    expect(abs((float) $debit->amount))->toBe(2500.00);
});

it('runs auto-matching on a statement', function () {
    $statement = BankStatement::factory()->create([
        'company_id' => $this->company->id,
        'created_by' => $this->user->id,
    ]);

    BankTransaction::factory()->count(3)->create([
        'company_id' => $this->company->id,
        'bank_statement_id' => $statement->id,
        'match_status' => BankTransaction::MATCH_STATUS_UNMATCHED,
    ]);

    $result = $this->service->runAutoMatching($statement);

    expect($result)->toHaveKey('matched');
    expect($result)->toHaveKey('unmatched');

    $statement->refresh();
    expect($statement->status)->not->toBe(BankStatement::STATUS_PENDING);
});

it('can manually match a transaction to an invoice', function () {
    $statement = BankStatement::factory()->create([
        'company_id' => $this->company->id,
        'created_by' => $this->user->id,
    ]);

    $transaction = BankTransaction::factory()->credit(1000)->create([
        'company_id' => $this->company->id,
        'bank_statement_id' => $statement->id,
        'match_status' => BankTransaction::MATCH_STATUS_UNMATCHED,
    ]);

    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'total' => 1000,
        'balance' => 1000,
    ]);

    $match = $this->service->matchTransaction($transaction, $invoice, $this->user);

    expect($match)->toBeInstanceOf(BankTransactionMatch::class);
    expect($match->matchable_type)->toBe(Invoice::class);
    expect($match->matchable_id)->toBe($invoice->id);
    expect($match->match_type)->toBe(BankTransactionMatch::MATCH_TYPE_MANUAL);

    $transaction->refresh();
    expect($transaction->match_status)->toBe(BankTransaction::MATCH_STATUS_MANUAL_MATCHED);
});

it('can unmatch a transaction', function () {
    $statement = BankStatement::factory()->create([
        'company_id' => $this->company->id,
        'created_by' => $this->user->id,
    ]);

    $transaction = BankTransaction::factory()->manualMatched()->create([
        'company_id' => $this->company->id,
        'bank_statement_id' => $statement->id,
    ]);

    $invoice = Invoice::factory()->create(['company_id' => $this->company->id]);

    BankTransactionMatch::create([
        'company_id' => $this->company->id,
        'bank_transaction_id' => $transaction->id,
        'matchable_type' => Invoice::class,
        'matchable_id' => $invoice->id,
        'match_type' => BankTransactionMatch::MATCH_TYPE_MANUAL,
        'match_confidence' => 1.0,
        'matched_at' => now(),
        'is_confirmed' => true,
    ]);

    $this->service->unmatchTransaction($transaction);

    $transaction->refresh();
    expect($transaction->match_status)->toBe(BankTransaction::MATCH_STATUS_UNMATCHED);
    expect($transaction->matches)->toHaveCount(0);
});

it('can ignore a transaction', function () {
    $statement = BankStatement::factory()->create([
        'company_id' => $this->company->id,
        'created_by' => $this->user->id,
    ]);

    $transaction = BankTransaction::factory()->unmatched()->create([
        'company_id' => $this->company->id,
        'bank_statement_id' => $statement->id,
    ]);

    $this->service->ignoreTransaction($transaction);

    $transaction->refresh();
    expect($transaction->match_status)->toBe(BankTransaction::MATCH_STATUS_IGNORED);
});

it('can unignore a transaction', function () {
    $statement = BankStatement::factory()->create([
        'company_id' => $this->company->id,
        'created_by' => $this->user->id,
    ]);

    $transaction = BankTransaction::factory()->ignored()->create([
        'company_id' => $this->company->id,
        'bank_statement_id' => $statement->id,
    ]);

    $this->service->unignoreTransaction($transaction);

    $transaction->refresh();
    expect($transaction->match_status)->toBe(BankTransaction::MATCH_STATUS_UNMATCHED);
});

it('can create a draft voucher', function () {
    $statement = BankStatement::factory()->create([
        'company_id' => $this->company->id,
        'created_by' => $this->user->id,
    ]);

    $transaction = BankTransaction::factory()->credit(1000)->create([
        'company_id' => $this->company->id,
        'bank_statement_id' => $statement->id,
    ]);

    $account = Account::factory()->create(['company_id' => $this->company->id]);

    $draft = $this->service->createDraftVoucher($transaction, [
        'description' => 'Test draft',
        'account_id' => $account->id,
        'amount' => 1000,
    ]);

    expect($draft)->toBeInstanceOf(BankReconciliationDraft::class);
    expect($draft->description)->toBe('Test draft');
    expect($draft->account_id)->toBe($account->id);
    expect($draft->voucher_type)->toBe(BankReconciliationDraft::VOUCHER_TYPE_PAYMENT);
});

it('can update a draft voucher', function () {
    $statement = BankStatement::factory()->create([
        'company_id' => $this->company->id,
        'created_by' => $this->user->id,
    ]);

    $transaction = BankTransaction::factory()->create([
        'company_id' => $this->company->id,
        'bank_statement_id' => $statement->id,
    ]);

    $account = Account::factory()->create(['company_id' => $this->company->id]);

    $draft = BankReconciliationDraft::create([
        'company_id' => $this->company->id,
        'bank_transaction_id' => $transaction->id,
        'voucher_type' => BankReconciliationDraft::VOUCHER_TYPE_PAYMENT,
        'voucher_data' => [],
        'description' => 'Original',
        'amount' => 500,
        'created_by' => $this->user->id,
    ]);

    $updated = $this->service->updateDraftVoucher($draft, [
        'description' => 'Updated',
        'account_id' => $account->id,
        'amount' => 1000,
    ]);

    expect($updated->description)->toBe('Updated');
    expect($updated->account_id)->toBe($account->id);
    expect((float) $updated->amount)->toBe(1000.0);
});

it('can delete a draft voucher', function () {
    $statement = BankStatement::factory()->create([
        'company_id' => $this->company->id,
        'created_by' => $this->user->id,
    ]);

    $transaction = BankTransaction::factory()->create([
        'company_id' => $this->company->id,
        'bank_statement_id' => $statement->id,
    ]);

    $draft = BankReconciliationDraft::create([
        'company_id' => $this->company->id,
        'bank_transaction_id' => $transaction->id,
        'voucher_type' => BankReconciliationDraft::VOUCHER_TYPE_PAYMENT,
        'voucher_data' => [],
        'description' => 'Test',
        'amount' => 500,
        'created_by' => $this->user->id,
    ]);

    $result = $this->service->deleteDraftVoucher($draft);

    expect($result)->toBeTrue();
    expect(BankReconciliationDraft::find($draft->id))->toBeNull();
});

it('cannot delete a processed draft voucher', function () {
    $statement = BankStatement::factory()->create([
        'company_id' => $this->company->id,
        'created_by' => $this->user->id,
    ]);

    $transaction = BankTransaction::factory()->create([
        'company_id' => $this->company->id,
        'bank_statement_id' => $statement->id,
    ]);

    $draft = BankReconciliationDraft::create([
        'company_id' => $this->company->id,
        'bank_transaction_id' => $transaction->id,
        'voucher_type' => BankReconciliationDraft::VOUCHER_TYPE_PAYMENT,
        'voucher_data' => [],
        'description' => 'Test',
        'amount' => 500,
        'is_processed' => true,
        'created_by' => $this->user->id,
    ]);

    $result = $this->service->deleteDraftVoucher($draft);

    expect($result)->toBeFalse();
    expect(BankReconciliationDraft::find($draft->id))->not->toBeNull();
});

it('can finalize reconciliation', function () {
    $statement = BankStatement::factory()->create([
        'company_id' => $this->company->id,
        'created_by' => $this->user->id,
        'status' => BankStatement::STATUS_MATCHED,
        'transaction_count' => 1,
        'matched_count' => 1,
        'unmatched_count' => 0,
    ]);

    BankTransaction::factory()->manualMatched()->create([
        'company_id' => $this->company->id,
        'bank_statement_id' => $statement->id,
    ]);

    $result = $this->service->finalizeReconciliation($statement, $this->user);

    expect($result)->toBeTrue();

    $statement->refresh();
    expect($statement->status)->toBe(BankStatement::STATUS_FINALIZED);
    expect($statement->finalized_by)->toBe($this->user->id);
    expect($statement->finalized_at)->not->toBeNull();
});

it('cannot finalize with unmatched transactions', function () {
    $statement = BankStatement::factory()->create([
        'company_id' => $this->company->id,
        'created_by' => $this->user->id,
        'status' => BankStatement::STATUS_PENDING,
        'transaction_count' => 2,
        'matched_count' => 1,
        'unmatched_count' => 1,
    ]);

    $result = $this->service->finalizeReconciliation($statement, $this->user);

    expect($result)->toBeFalse();

    $statement->refresh();
    expect($statement->status)->not->toBe(BankStatement::STATUS_FINALIZED);
});

it('returns statistics for a statement', function () {
    $statement = BankStatement::factory()->create([
        'company_id' => $this->company->id,
        'created_by' => $this->user->id,
        'transaction_count' => 5,
        'matched_count' => 3,
        'unmatched_count' => 2,
    ]);

    BankTransaction::factory()->credit(1000)->create([
        'company_id' => $this->company->id,
        'bank_statement_id' => $statement->id,
    ]);

    BankTransaction::factory()->debit(500)->create([
        'company_id' => $this->company->id,
        'bank_statement_id' => $statement->id,
    ]);

    $stats = $this->service->getStatistics($statement);

    expect($stats)->toHaveKey('total_transactions');
    expect($stats)->toHaveKey('matched_count');
    expect($stats)->toHaveKey('unmatched_count');
    expect($stats)->toHaveKey('matched_percent');
    expect($stats)->toHaveKey('total_in');
    expect($stats)->toHaveKey('total_out');
    expect($stats)->toHaveKey('net_change');
});

it('returns available bank accounts', function () {
    Account::factory()->create([
        'company_id' => $this->company->id,
        'account_number' => '1920',
        'name' => 'Driftskonto',
        'is_active' => true,
    ]);

    Account::factory()->create([
        'company_id' => $this->company->id,
        'account_number' => '1921',
        'name' => 'Sparekonto',
        'is_active' => true,
    ]);

    Account::factory()->create([
        'company_id' => $this->company->id,
        'account_number' => '4000',
        'name' => 'Varekost',
        'is_active' => true,
    ]);

    $accounts = $this->service->getBankAccounts();

    expect($accounts)->toHaveCount(2);
    expect($accounts->pluck('account_number')->toArray())->toBe(['1920', '1921']);
});
