<?php

namespace App\Services;

use App\Models\Account;
use App\Models\BankReconciliationDraft;
use App\Models\BankStatement;
use App\Models\BankTransaction;
use App\Models\BankTransactionMatch;
use App\Models\CsvFormatMapping;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\SupplierInvoice;
use App\Models\SupplierPayment;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BankReconciliationService
{
    public function __construct(
        protected CsvParserService $csvParser,
        protected BankTransactionMatcherService $matcher
    ) {}

    /**
     * Import a CSV file and create a bank statement with transactions.
     */
    public function importCsvFile(
        UploadedFile $file,
        ?int $formatId = null,
        ?int $bankAccountId = null
    ): BankStatement {
        $disk = config('bank-reconciliation.storage.disk', 'local');
        $path = config('bank-reconciliation.storage.path', 'bank-statements');
        $storedPath = $file->store($path, $disk);
        $content = Storage::disk($disk)->get($storedPath);

        if ($formatId) {
            $format = CsvFormatMapping::find($formatId);
            if (! $format) {
                throw new \InvalidArgumentException('Invalid CSV format mapping.');
            }
            $transactions = $this->csvParser->parse($content, $format);
            $bankName = $format->bank_name;
        } else {
            $result = $this->csvParser->parseWithAutoDetect($content);
            $transactions = $result['data'];
            $formats = CsvFormatMapping::getSystemFormats();
            $bankName = isset($formats[$result['format']]) ? $formats[$result['format']]['bank_name'] : null;
        }

        if (empty($transactions)) {
            Storage::disk($disk)->delete($storedPath);
            throw new \RuntimeException('Kunne ikke tolke CSV-filen. Sjekk at formatet er riktig.');
        }

        $accountNumber = $this->csvParser->extractAccountNumber(
            $file->getClientOriginalName(),
            $content
        ) ?? '';

        $dateRange = $this->csvParser->extractDateRange($transactions);

        return DB::transaction(function () use (
            $storedPath,
            $file,
            $bankName,
            $accountNumber,
            $bankAccountId,
            $dateRange,
            $transactions
        ) {
            $statement = BankStatement::create([
                'file_path' => $storedPath,
                'original_filename' => $file->getClientOriginalName(),
                'bank_name' => $bankName,
                'account_number' => $accountNumber,
                'bank_account_id' => $bankAccountId,
                'from_date' => $dateRange['from'],
                'to_date' => $dateRange['to'],
                'status' => BankStatement::STATUS_PENDING,
                'transaction_count' => count($transactions),
                'matched_count' => 0,
                'unmatched_count' => count($transactions),
                'created_by' => auth()->id(),
            ]);

            foreach ($transactions as $txData) {
                BankTransaction::create([
                    'company_id' => $statement->company_id,
                    'bank_statement_id' => $statement->id,
                    'transaction_date' => $txData['transaction_date'],
                    'description' => $txData['description'],
                    'reference' => $txData['reference'],
                    'amount' => $txData['amount'],
                    'running_balance' => $txData['running_balance'],
                    'transaction_type' => $txData['transaction_type'],
                    'raw_data' => $txData['raw_data'] ?? null,
                    'match_status' => BankTransaction::MATCH_STATUS_UNMATCHED,
                    'sort_order' => $txData['sort_order'],
                ]);
            }

            return $statement;
        });
    }

    /**
     * Run auto-matching on a bank statement.
     *
     * @return array{matched: int, unmatched: int}
     */
    public function runAutoMatching(BankStatement $statement): array
    {
        $statement->update(['status' => BankStatement::STATUS_MATCHING]);

        $result = $this->matcher->matchStatement($statement);

        // If all matched, set to MATCHED; otherwise keep as MATCHING to indicate work is needed
        $newStatus = $result['unmatched'] === 0
            ? BankStatement::STATUS_MATCHED
            : BankStatement::STATUS_MATCHING;

        $statement->update(['status' => $newStatus]);

        return $result;
    }

    /**
     * Manually match a transaction to a matchable entity.
     */
    public function matchTransaction(
        BankTransaction $transaction,
        mixed $matchable,
        ?User $user = null
    ): BankTransactionMatch {
        $match = $this->matcher->createMatch(
            $transaction,
            $matchable,
            BankTransactionMatch::MATCH_TYPE_MANUAL,
            1.0,
            $user?->id,
            true
        );

        $transaction->bankStatement->recalculateCounts();

        return $match;
    }

    /**
     * Unmatch a transaction.
     */
    public function unmatchTransaction(BankTransaction $transaction): void
    {
        $transaction->matches()->delete();

        $transaction->update([
            'match_status' => BankTransaction::MATCH_STATUS_UNMATCHED,
            'match_confidence' => null,
        ]);

        $transaction->bankStatement->recalculateCounts();
    }

    /**
     * Ignore a transaction (mark as not needing reconciliation).
     */
    public function ignoreTransaction(BankTransaction $transaction): void
    {
        $transaction->update([
            'match_status' => BankTransaction::MATCH_STATUS_IGNORED,
        ]);

        $transaction->bankStatement->recalculateCounts();
    }

    /**
     * Un-ignore a transaction.
     */
    public function unignoreTransaction(BankTransaction $transaction): void
    {
        $transaction->update([
            'match_status' => BankTransaction::MATCH_STATUS_UNMATCHED,
        ]);

        $transaction->bankStatement->recalculateCounts();
    }

    /**
     * Confirm an auto-matched transaction.
     */
    public function confirmMatch(BankTransaction $transaction, ?User $user = null): bool
    {
        $match = $transaction->matches()->unconfirmed()->first();

        if (! $match) {
            return false;
        }

        return $match->confirm($user);
    }

    /**
     * Unconfirm a match.
     */
    public function unconfirmMatch(BankTransaction $transaction): bool
    {
        $match = $transaction->confirmedMatch;

        if (! $match) {
            return false;
        }

        return $match->unconfirm();
    }

    /**
     * Create a draft voucher for a transaction.
     *
     * @param  array<string, mixed>  $data
     */
    public function createDraftVoucher(BankTransaction $transaction, array $data): BankReconciliationDraft
    {
        $voucherType = $transaction->isCredit
            ? BankReconciliationDraft::VOUCHER_TYPE_PAYMENT
            : BankReconciliationDraft::VOUCHER_TYPE_SUPPLIER_PAYMENT;

        if (isset($data['voucher_type'])) {
            $voucherType = $data['voucher_type'];
        }

        return BankReconciliationDraft::create([
            'company_id' => $transaction->company_id,
            'bank_transaction_id' => $transaction->id,
            'voucher_type' => $voucherType,
            'voucher_data' => $data['voucher_data'] ?? ['lines' => []],
            'contact_id' => $data['contact_id'] ?? null,
            'account_id' => $data['account_id'] ?? null,
            'description' => $data['description'] ?? $transaction->description,
            'amount' => $data['amount'] ?? abs($transaction->amount),
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Update a draft voucher.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateDraftVoucher(BankReconciliationDraft $draft, array $data): BankReconciliationDraft
    {
        $draft->update([
            'voucher_type' => $data['voucher_type'] ?? $draft->voucher_type,
            'voucher_data' => $data['voucher_data'] ?? $draft->voucher_data,
            'contact_id' => $data['contact_id'] ?? $draft->contact_id,
            'account_id' => $data['account_id'] ?? $draft->account_id,
            'description' => $data['description'] ?? $draft->description,
            'amount' => $data['amount'] ?? $draft->amount,
        ]);

        return $draft->fresh();
    }

    /**
     * Delete a draft voucher.
     */
    public function deleteDraftVoucher(BankReconciliationDraft $draft): bool
    {
        if ($draft->is_processed) {
            return false;
        }

        return $draft->delete();
    }

    /**
     * Process all unprocessed draft vouchers for a statement.
     *
     * @return array{processed: int, errors: array<string>}
     */
    public function processAllDrafts(BankStatement $statement): array
    {
        $drafts = BankReconciliationDraft::whereHas('bankTransaction', function ($q) use ($statement) {
            $q->where('bank_statement_id', $statement->id);
        })->unprocessed()->get();

        $processed = 0;
        $errors = [];

        foreach ($drafts as $draft) {
            try {
                $voucher = $draft->processToVoucher();
                if ($voucher) {
                    $processed++;
                }
            } catch (\Exception $e) {
                $errors[] = "Transaksjon #{$draft->bank_transaction_id}: ".$e->getMessage();
            }
        }

        $statement->recalculateCounts();

        return [
            'processed' => $processed,
            'errors' => $errors,
        ];
    }

    /**
     * Process matched invoices (create payments).
     *
     * @return array{processed: int, errors: array<string>}
     */
    public function processMatchedInvoices(BankStatement $statement): array
    {
        $transactions = $statement->transactions()
            ->matched()
            ->with(['confirmedMatch.matchable'])
            ->get();

        $processed = 0;
        $errors = [];

        foreach ($transactions as $transaction) {
            $match = $transaction->confirmedMatch;
            if (! $match) {
                continue;
            }

            $matchable = $match->matchable;

            try {
                if ($matchable instanceof Invoice) {
                    $this->createInvoicePayment($transaction, $matchable);
                    $processed++;
                } elseif ($matchable instanceof SupplierInvoice) {
                    $this->createSupplierPayment($transaction, $matchable);
                    $processed++;
                }
            } catch (\Exception $e) {
                $errors[] = "Transaksjon #{$transaction->id}: ".$e->getMessage();
            }
        }

        return [
            'processed' => $processed,
            'errors' => $errors,
        ];
    }

    /**
     * Create an invoice payment from a matched transaction.
     */
    protected function createInvoicePayment(BankTransaction $transaction, Invoice $invoice): InvoicePayment
    {
        return InvoicePayment::create([
            'company_id' => $transaction->company_id,
            'invoice_id' => $invoice->id,
            'payment_date' => $transaction->transaction_date,
            'amount' => abs($transaction->amount),
            'reference' => $transaction->reference,
            'notes' => "Fra bankavstemming: {$transaction->description}",
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Create a supplier payment from a matched transaction.
     */
    protected function createSupplierPayment(BankTransaction $transaction, SupplierInvoice $invoice): SupplierPayment
    {
        return SupplierPayment::create([
            'company_id' => $transaction->company_id,
            'supplier_invoice_id' => $invoice->id,
            'payment_date' => $transaction->transaction_date,
            'amount' => abs($transaction->amount),
            'reference' => $transaction->reference,
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Finalize the reconciliation.
     */
    public function finalizeReconciliation(BankStatement $statement, User $user): bool
    {
        if (! $statement->canFinalize()) {
            return false;
        }

        $this->processMatchedInvoices($statement);

        return $statement->finalize($user);
    }

    /**
     * Get unmatched transactions for a statement.
     */
    public function getUnmatchedTransactions(BankStatement $statement): Collection
    {
        return $statement->transactions()
            ->unmatched()
            ->with('draftVoucher')
            ->ordered()
            ->get();
    }

    /**
     * Get matched transactions for a statement.
     */
    public function getMatchedTransactions(BankStatement $statement): Collection
    {
        return $statement->transactions()
            ->matched()
            ->with(['confirmedMatch.matchable', 'draftVoucher'])
            ->ordered()
            ->get();
    }

    /**
     * Get all bank accounts (1920 series).
     */
    public function getBankAccounts(): Collection
    {
        return Account::where('account_number', 'like', '19%')
            ->where('is_active', true)
            ->orderBy('account_number')
            ->get();
    }

    /**
     * Get available CSV format mappings.
     */
    public function getAvailableFormats(): Collection
    {
        $systemFormats = collect(CsvFormatMapping::getSystemFormats())
            ->map(function ($format, $key) {
                return new CsvFormatMapping(array_merge($format, [
                    'id' => $key,
                    'is_system' => true,
                ]));
            });

        $customFormats = CsvFormatMapping::custom()
            ->active()
            ->get();

        return $systemFormats->merge($customFormats);
    }

    /**
     * Get statistics for a bank statement.
     *
     * @return array<string, mixed>
     */
    public function getStatistics(BankStatement $statement): array
    {
        $transactions = $statement->transactions;

        $totalIn = $transactions->where('transaction_type', 'credit')->sum('amount');
        $totalOut = abs($transactions->where('transaction_type', 'debit')->sum('amount'));

        return [
            'total_transactions' => $statement->transaction_count,
            'matched_count' => $statement->matched_count,
            'unmatched_count' => $statement->unmatched_count,
            'ignored_count' => $transactions->where('match_status', 'ignored')->count(),
            'matched_percent' => $statement->matchedPercent,
            'total_in' => $totalIn,
            'total_out' => $totalOut,
            'net_change' => $totalIn - $totalOut,
            'opening_balance' => $statement->opening_balance,
            'closing_balance' => $statement->closing_balance,
        ];
    }
}
