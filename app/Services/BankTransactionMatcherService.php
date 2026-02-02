<?php

namespace App\Services;

use App\Models\BankStatement;
use App\Models\BankTransaction;
use App\Models\BankTransactionMatch;
use App\Models\Invoice;
use App\Models\SupplierInvoice;
use App\Models\Voucher;
use App\Models\VoucherLine;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BankTransactionMatcherService
{
    protected int $dateToleranceDays = 5;

    protected float $amountTolerancePercent = 0.01;

    /**
     * Run auto-matching on all unmatched transactions in a statement.
     *
     * @return array{matched: int, unmatched: int}
     */
    public function matchStatement(BankStatement $statement): array
    {
        $transactions = $statement->transactions()->unmatched()->get();
        $matched = 0;

        foreach ($transactions as $transaction) {
            $match = $this->findBestMatch($transaction);

            if ($match) {
                $this->createMatch($transaction, $match['matchable'], $match['type'], $match['confidence']);
                $matched++;
            }
        }

        $statement->recalculateCounts();

        return [
            'matched' => $matched,
            'unmatched' => $statement->unmatched_count,
        ];
    }

    /**
     * Find the best match for a transaction.
     *
     * @return array{matchable: mixed, type: string, confidence: float}|null
     */
    public function findBestMatch(BankTransaction $transaction): ?array
    {
        $candidates = [];

        if ($transaction->reference) {
            $kidMatch = $this->matchByKid($transaction);
            if ($kidMatch) {
                $candidates[] = $kidMatch;
            }
        }

        $voucherMatch = $this->matchByVoucher($transaction);
        if ($voucherMatch) {
            $candidates[] = $voucherMatch;
        }

        if ($transaction->isCredit) {
            $invoiceMatch = $this->matchByInvoice($transaction);
            if ($invoiceMatch) {
                $candidates[] = $invoiceMatch;
            }
        }

        if ($transaction->isDebit) {
            $supplierMatch = $this->matchBySupplierInvoice($transaction);
            if ($supplierMatch) {
                $candidates[] = $supplierMatch;
            }
        }

        if (empty($candidates)) {
            return null;
        }

        usort($candidates, fn ($a, $b) => $b['confidence'] <=> $a['confidence']);

        $best = $candidates[0];

        if ($best['confidence'] < 0.60) {
            return null;
        }

        return $best;
    }

    /**
     * Match by KID reference (Norwegian payment reference).
     *
     * @return array{matchable: Invoice, type: string, confidence: float}|null
     */
    protected function matchByKid(BankTransaction $transaction): ?array
    {
        $kid = $this->normalizeKid($transaction->reference);
        if (! $kid) {
            return null;
        }

        $invoice = Invoice::where('kid', $kid)
            ->where('balance', '>', 0)
            ->first();

        if (! $invoice) {
            return null;
        }

        $amountMatch = $this->calculateAmountConfidence(
            abs($transaction->amount),
            $invoice->balance
        );

        $confidence = 0.95 + ($amountMatch * 0.05);

        return [
            'matchable' => $invoice,
            'type' => BankTransactionMatch::MATCH_TYPE_EXACT,
            'confidence' => $confidence,
        ];
    }

    /**
     * Match by existing voucher on bank account.
     *
     * @return array{matchable: Voucher, type: string, confidence: float}|null
     */
    protected function matchByVoucher(BankTransaction $transaction): ?array
    {
        $bankAccountId = $transaction->bankStatement->bank_account_id;

        if (! $bankAccountId) {
            return null;
        }

        $query = VoucherLine::where('account_id', $bankAccountId)
            ->whereHas('voucher', function ($q) use ($transaction) {
                $q->where('is_posted', true)
                    ->whereBetween('voucher_date', [
                        $transaction->transaction_date->copy()->subDays($this->dateToleranceDays),
                        $transaction->transaction_date->copy()->addDays($this->dateToleranceDays),
                    ]);
            });

        if ($transaction->isCredit) {
            $query->where('debit', '>', 0);
        } else {
            $query->where('credit', '>', 0);
        }

        $lines = $query->with('voucher')->get();

        foreach ($lines as $line) {
            $voucherAmount = $transaction->isCredit ? $line->debit : $line->credit;
            $amountMatch = $this->calculateAmountConfidence(abs($transaction->amount), $voucherAmount);

            if ($amountMatch >= 0.99) {
                $dateMatch = $this->calculateDateConfidence(
                    $transaction->transaction_date,
                    $line->voucher->voucher_date
                );

                $confidence = 0.85 + ($amountMatch * 0.10) + ($dateMatch * 0.05);

                return [
                    'matchable' => $line->voucher,
                    'type' => $amountMatch >= 1.0 ? BankTransactionMatch::MATCH_TYPE_EXACT : BankTransactionMatch::MATCH_TYPE_FUZZY,
                    'confidence' => min(0.95, $confidence),
                ];
            }
        }

        return null;
    }

    /**
     * Match incoming payment to unpaid invoice.
     *
     * @return array{matchable: Invoice, type: string, confidence: float}|null
     */
    protected function matchByInvoice(BankTransaction $transaction): ?array
    {
        $invoices = Invoice::where('balance', '>', 0)
            ->whereBetween('due_date', [
                $transaction->transaction_date->copy()->subDays(30),
                $transaction->transaction_date->copy()->addDays(30),
            ])
            ->get();

        $bestMatch = null;
        $bestConfidence = 0;

        foreach ($invoices as $invoice) {
            $amountMatch = $this->calculateAmountConfidence(
                abs($transaction->amount),
                $invoice->balance
            );

            if ($amountMatch < 0.95) {
                continue;
            }

            $dateMatch = $this->calculateDateConfidence(
                $transaction->transaction_date,
                $invoice->due_date
            );

            $descriptionMatch = $this->calculateDescriptionConfidence(
                $transaction->description,
                $invoice->contact?->company_name ?? ''
            );

            $confidence = ($amountMatch * 0.60) + ($dateMatch * 0.20) + ($descriptionMatch * 0.20);

            if ($confidence > $bestConfidence && $confidence >= 0.80) {
                $bestConfidence = $confidence;
                $bestMatch = [
                    'matchable' => $invoice,
                    'type' => $amountMatch >= 1.0 ? BankTransactionMatch::MATCH_TYPE_EXACT : BankTransactionMatch::MATCH_TYPE_FUZZY,
                    'confidence' => $confidence,
                ];
            }
        }

        return $bestMatch;
    }

    /**
     * Match outgoing payment to unpaid supplier invoice.
     *
     * @return array{matchable: SupplierInvoice, type: string, confidence: float}|null
     */
    protected function matchBySupplierInvoice(BankTransaction $transaction): ?array
    {
        $invoices = SupplierInvoice::where('balance', '>', 0)
            ->whereBetween('due_date', [
                $transaction->transaction_date->copy()->subDays(30),
                $transaction->transaction_date->copy()->addDays(30),
            ])
            ->with('contact')
            ->get();

        $bestMatch = null;
        $bestConfidence = 0;

        foreach ($invoices as $invoice) {
            $amountMatch = $this->calculateAmountConfidence(
                abs($transaction->amount),
                $invoice->balance
            );

            if ($amountMatch < 0.95) {
                continue;
            }

            $dateMatch = $this->calculateDateConfidence(
                $transaction->transaction_date,
                $invoice->due_date
            );

            $descriptionMatch = $this->calculateDescriptionConfidence(
                $transaction->description,
                $invoice->contact?->company_name ?? ''
            );

            $confidence = ($amountMatch * 0.60) + ($dateMatch * 0.20) + ($descriptionMatch * 0.20);

            if ($confidence > $bestConfidence && $confidence >= 0.80) {
                $bestConfidence = $confidence;
                $bestMatch = [
                    'matchable' => $invoice,
                    'type' => $amountMatch >= 1.0 ? BankTransactionMatch::MATCH_TYPE_EXACT : BankTransactionMatch::MATCH_TYPE_FUZZY,
                    'confidence' => $confidence,
                ];
            }
        }

        return $bestMatch;
    }

    /**
     * Create a match record.
     */
    public function createMatch(
        BankTransaction $transaction,
        mixed $matchable,
        string $matchType,
        float $confidence,
        ?int $userId = null,
        bool $confirmed = false
    ): BankTransactionMatch {
        $match = BankTransactionMatch::create([
            'company_id' => $transaction->company_id,
            'bank_transaction_id' => $transaction->id,
            'matchable_type' => get_class($matchable),
            'matchable_id' => $matchable->id,
            'match_type' => $matchType,
            'match_confidence' => $confidence,
            'matched_by' => $userId,
            'matched_at' => now(),
            'is_confirmed' => $confirmed,
        ]);

        if ($confirmed) {
            $transaction->update([
                'match_status' => $userId
                    ? BankTransaction::MATCH_STATUS_MANUAL_MATCHED
                    : BankTransaction::MATCH_STATUS_AUTO_MATCHED,
                'match_confidence' => $confidence,
            ]);
        }

        return $match;
    }

    /**
     * Get suggested matches for a transaction (for manual review).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getSuggestedMatches(BankTransaction $transaction, int $limit = 10): Collection
    {
        $suggestions = collect();

        if ($transaction->isCredit) {
            $invoices = Invoice::where('balance', '>', 0)
                ->orderBy('due_date')
                ->limit($limit)
                ->get();

            foreach ($invoices as $invoice) {
                $confidence = $this->calculateAmountConfidence(
                    abs($transaction->amount),
                    $invoice->balance
                );

                $suggestions->push([
                    'matchable' => $invoice,
                    'type' => 'invoice',
                    'label' => "Faktura {$invoice->invoice_number}",
                    'description' => $invoice->contact?->company_name ?? '',
                    'amount' => $invoice->balance,
                    'date' => $invoice->due_date,
                    'confidence' => $confidence,
                ]);
            }
        } else {
            $supplierInvoices = SupplierInvoice::where('balance', '>', 0)
                ->with('contact')
                ->orderBy('due_date')
                ->limit($limit)
                ->get();

            foreach ($supplierInvoices as $invoice) {
                $confidence = $this->calculateAmountConfidence(
                    abs($transaction->amount),
                    $invoice->balance
                );

                $suggestions->push([
                    'matchable' => $invoice,
                    'type' => 'supplier_invoice',
                    'label' => "Leverandorfaktura {$invoice->invoice_number}",
                    'description' => $invoice->contact?->company_name ?? '',
                    'amount' => $invoice->balance,
                    'date' => $invoice->due_date,
                    'confidence' => $confidence,
                ]);
            }
        }

        return $suggestions->sortByDesc('confidence')->take($limit)->values();
    }

    /**
     * Normalize KID reference.
     */
    protected function normalizeKid(?string $kid): ?string
    {
        if (! $kid) {
            return null;
        }

        return preg_replace('/[^0-9]/', '', $kid);
    }

    /**
     * Calculate confidence based on amount match.
     */
    protected function calculateAmountConfidence(float $amount1, float $amount2): float
    {
        if ($amount2 == 0) {
            return 0;
        }

        $diff = abs($amount1 - $amount2);
        $tolerance = $amount2 * $this->amountTolerancePercent;

        if ($diff <= $tolerance) {
            return 1.0;
        }

        $percentDiff = $diff / $amount2;

        if ($percentDiff > 0.10) {
            return 0;
        }

        return max(0, 1 - ($percentDiff / 0.10));
    }

    /**
     * Calculate confidence based on date proximity.
     */
    protected function calculateDateConfidence(Carbon $date1, Carbon $date2): float
    {
        $daysDiff = abs($date1->diffInDays($date2));

        if ($daysDiff === 0) {
            return 1.0;
        }

        if ($daysDiff > $this->dateToleranceDays) {
            return 0;
        }

        return 1 - ($daysDiff / $this->dateToleranceDays);
    }

    /**
     * Calculate confidence based on description similarity.
     */
    protected function calculateDescriptionConfidence(string $desc1, string $desc2): float
    {
        if (empty($desc1) || empty($desc2)) {
            return 0;
        }

        $desc1 = strtolower($desc1);
        $desc2 = strtolower($desc2);

        if (str_contains($desc1, $desc2) || str_contains($desc2, $desc1)) {
            return 1.0;
        }

        similar_text($desc1, $desc2, $percent);

        return $percent / 100;
    }
}
