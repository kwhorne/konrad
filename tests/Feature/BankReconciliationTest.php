<?php

use App\Livewire\BankReconciliationManager;
use App\Models\Account;
use App\Models\BankStatement;
use App\Models\BankTransaction;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['onboarding_completed' => true, 'is_economy' => true]);
    $this->company = Company::factory()->withOwner($this->user)->create();
    $this->user->update(['current_company_id' => $this->company->id]);
    app()->instance('current.company', $this->company);
    $this->actingAs($this->user);
});

it('renders the bank reconciliation page', function () {
    $response = $this->get(route('economy.bank-reconciliation'));

    $response->assertStatus(200);
    $response->assertSeeLivewire(BankReconciliationManager::class);
});

it('shows step 1 upload form initially', function () {
    Livewire::test(BankReconciliationManager::class)
        ->assertSet('currentStep', 1)
        ->assertSee('Last opp kontoutskrift')
        ->assertSee('CSV-format');
});

it('can upload a csv file', function () {
    Storage::fake('local');

    $csvContent = "Dato;Tekst;Ut;Inn;Referanse\n";
    $csvContent .= "01.01.2025;Test transaksjon;;1000,00;123456\n";
    $csvContent .= "02.01.2025;Utbetaling;500,00;;\n";

    $file = UploadedFile::fake()->createWithContent('kontoutskrift.csv', $csvContent);

    $bankAccount = Account::factory()->create([
        'company_id' => $this->company->id,
        'account_number' => '1920',
        'name' => 'Bank',
    ]);

    Livewire::test(BankReconciliationManager::class)
        ->set('uploadFile', $file)
        ->set('selectedBankAccountId', $bankAccount->id)
        ->call('uploadAndParse')
        ->assertSet('currentStep', 2);

    $this->assertDatabaseHas('bank_statements', [
        'company_id' => $this->company->id,
        'original_filename' => 'kontoutskrift.csv',
    ]);
});

it('validates required file on upload', function () {
    Livewire::test(BankReconciliationManager::class)
        ->call('uploadAndParse')
        ->assertHasErrors(['uploadFile' => 'required']);
});

it('shows imported transactions in step 2', function () {
    $statement = BankStatement::factory()->create([
        'company_id' => $this->company->id,
        'created_by' => $this->user->id,
        'transaction_count' => 2,
        'matched_count' => 0,
        'unmatched_count' => 2,
        'status' => BankStatement::STATUS_PENDING,
    ]);

    BankTransaction::factory()->credit(1000)->create([
        'company_id' => $this->company->id,
        'bank_statement_id' => $statement->id,
        'description' => 'Innbetaling fra kunde',
    ]);

    BankTransaction::factory()->debit(500)->create([
        'company_id' => $this->company->id,
        'bank_statement_id' => $statement->id,
        'description' => 'Betaling til leverandor',
    ]);

    Livewire::test(BankReconciliationManager::class, ['statementId' => $statement->id])
        ->assertSet('currentStep', 2)
        ->assertSee('Innbetaling fra kunde')
        ->assertSee('Betaling til leverandor');
});

it('can run auto-matching', function () {
    $statement = BankStatement::factory()->create([
        'company_id' => $this->company->id,
        'created_by' => $this->user->id,
        'status' => BankStatement::STATUS_PENDING,
    ]);

    BankTransaction::factory()->credit(1000)->create([
        'company_id' => $this->company->id,
        'bank_statement_id' => $statement->id,
    ]);

    Livewire::test(BankReconciliationManager::class, ['statementId' => $statement->id])
        ->set('currentStep', 2)
        ->call('runMatching')
        ->assertSet('currentStep', 3);

    $statement->refresh();
    $this->assertNotEquals(BankStatement::STATUS_PENDING, $statement->status);
});

it('can ignore a transaction', function () {
    $statement = BankStatement::factory()->create([
        'company_id' => $this->company->id,
        'created_by' => $this->user->id,
    ]);

    $transaction = BankTransaction::factory()->create([
        'company_id' => $this->company->id,
        'bank_statement_id' => $statement->id,
        'match_status' => BankTransaction::MATCH_STATUS_UNMATCHED,
    ]);

    Livewire::test(BankReconciliationManager::class, ['statementId' => $statement->id])
        ->call('ignoreTransaction', $transaction->id);

    $transaction->refresh();
    $this->assertEquals(BankTransaction::MATCH_STATUS_IGNORED, $transaction->match_status);
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

    Livewire::test(BankReconciliationManager::class, ['statementId' => $statement->id])
        ->call('unignoreTransaction', $transaction->id);

    $transaction->refresh();
    $this->assertEquals(BankTransaction::MATCH_STATUS_UNMATCHED, $transaction->match_status);
});

it('can open and close match modal', function () {
    $statement = BankStatement::factory()->create([
        'company_id' => $this->company->id,
        'created_by' => $this->user->id,
    ]);

    $transaction = BankTransaction::factory()->create([
        'company_id' => $this->company->id,
        'bank_statement_id' => $statement->id,
    ]);

    Livewire::test(BankReconciliationManager::class, ['statementId' => $statement->id])
        ->call('openMatchModal', $transaction->id)
        ->assertSet('showMatchModal', true)
        ->assertSet('selectedTransactionId', $transaction->id)
        ->call('closeMatchModal')
        ->assertSet('showMatchModal', false)
        ->assertSet('selectedTransactionId', null);
});

it('can open and close draft modal', function () {
    $statement = BankStatement::factory()->create([
        'company_id' => $this->company->id,
        'created_by' => $this->user->id,
    ]);

    $transaction = BankTransaction::factory()->credit(1000)->create([
        'company_id' => $this->company->id,
        'bank_statement_id' => $statement->id,
        'description' => 'Test transaksjon',
    ]);

    Livewire::test(BankReconciliationManager::class, ['statementId' => $statement->id])
        ->call('openDraftModal', $transaction->id)
        ->assertSet('showDraftModal', true)
        ->assertSet('draftDescription', 'Test transaksjon')
        ->call('closeDraftModal')
        ->assertSet('showDraftModal', false);
});

it('can save a draft voucher', function () {
    $statement = BankStatement::factory()->create([
        'company_id' => $this->company->id,
        'created_by' => $this->user->id,
    ]);

    $transaction = BankTransaction::factory()->credit(1000)->create([
        'company_id' => $this->company->id,
        'bank_statement_id' => $statement->id,
    ]);

    $account = Account::factory()->create([
        'company_id' => $this->company->id,
        'account_number' => '3000',
        'name' => 'Salgsinntekt',
    ]);

    Livewire::test(BankReconciliationManager::class, ['statementId' => $statement->id])
        ->call('openDraftModal', $transaction->id)
        ->set('draftDescription', 'Test kladd')
        ->set('draftAccountId', $account->id)
        ->set('draftAmount', '1000')
        ->call('saveDraft')
        ->assertSet('showDraftModal', false);

    $this->assertDatabaseHas('bank_reconciliation_drafts', [
        'bank_transaction_id' => $transaction->id,
        'description' => 'Test kladd',
        'account_id' => $account->id,
    ]);
});

it('validates draft voucher fields', function () {
    $statement = BankStatement::factory()->create([
        'company_id' => $this->company->id,
        'created_by' => $this->user->id,
    ]);

    $transaction = BankTransaction::factory()->create([
        'company_id' => $this->company->id,
        'bank_statement_id' => $statement->id,
    ]);

    Livewire::test(BankReconciliationManager::class, ['statementId' => $statement->id])
        ->call('openDraftModal', $transaction->id)
        ->set('draftDescription', '')
        ->set('draftAccountId', null)
        ->call('saveDraft')
        ->assertHasErrors(['draftDescription', 'draftAccountId']);
});

it('can finalize reconciliation when all transactions are matched', function () {
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

    Livewire::test(BankReconciliationManager::class, ['statementId' => $statement->id])
        ->set('currentStep', 4)
        ->call('finalizeReconciliation');

    $statement->refresh();
    $this->assertEquals(BankStatement::STATUS_FINALIZED, $statement->status);
    $this->assertNotNull($statement->finalized_at);
    $this->assertEquals($this->user->id, $statement->finalized_by);
});

it('cannot finalize reconciliation with unmatched transactions', function () {
    $statement = BankStatement::factory()->create([
        'company_id' => $this->company->id,
        'created_by' => $this->user->id,
        'status' => BankStatement::STATUS_PENDING,
        'transaction_count' => 1,
        'matched_count' => 0,
        'unmatched_count' => 1,
    ]);

    BankTransaction::factory()->unmatched()->create([
        'company_id' => $this->company->id,
        'bank_statement_id' => $statement->id,
    ]);

    Livewire::test(BankReconciliationManager::class, ['statementId' => $statement->id])
        ->set('currentStep', 4)
        ->call('finalizeReconciliation');

    $statement->refresh();
    $this->assertNotEquals(BankStatement::STATUS_FINALIZED, $statement->status);
});

it('shows statistics in step 3', function () {
    $statement = BankStatement::factory()->create([
        'company_id' => $this->company->id,
        'created_by' => $this->user->id,
        'transaction_count' => 3,
        'matched_count' => 2,
        'unmatched_count' => 1,
    ]);

    Livewire::test(BankReconciliationManager::class, ['statementId' => $statement->id])
        ->set('currentStep', 3)
        ->assertSee('3')
        ->assertSee('2')
        ->assertSee('1');
});
