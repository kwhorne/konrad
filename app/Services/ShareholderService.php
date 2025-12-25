<?php

namespace App\Services;

use App\Models\Dividend;
use App\Models\ShareClass;
use App\Models\Shareholder;
use App\Models\ShareholderReport;
use App\Models\Shareholding;
use App\Models\ShareTransaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ShareholderService
{
    /**
     * Create a new share class.
     */
    public function createShareClass(array $data): ShareClass
    {
        return ShareClass::create($data);
    }

    /**
     * Create a new shareholder.
     */
    public function createShareholder(array $data): Shareholder
    {
        return Shareholder::create($data);
    }

    /**
     * Issue new shares to a shareholder (emission).
     */
    public function issueShares(array $data): ShareTransaction
    {
        return DB::transaction(function () use ($data) {
            // Create the transaction
            $transaction = ShareTransaction::create([
                'transaction_date' => $data['transaction_date'],
                'transaction_type' => 'issue',
                'share_class_id' => $data['share_class_id'],
                'to_shareholder_id' => $data['shareholder_id'],
                'number_of_shares' => $data['number_of_shares'],
                'price_per_share' => $data['price_per_share'] ?? null,
                'total_amount' => $data['total_amount'] ?? null,
                'currency' => $data['currency'] ?? 'NOK',
                'description' => $data['description'] ?? null,
                'document_reference' => $data['document_reference'] ?? null,
                'metadata' => $data['metadata'] ?? null,
                'created_by' => $data['created_by'],
            ]);

            // Create shareholding
            $acquisitionCost = $data['total_amount']
                ?? ($data['number_of_shares'] * ($data['price_per_share'] ?? 0));

            Shareholding::create([
                'shareholder_id' => $data['shareholder_id'],
                'share_class_id' => $data['share_class_id'],
                'number_of_shares' => $data['number_of_shares'],
                'acquisition_cost' => $acquisitionCost,
                'cost_per_share' => $acquisitionCost > 0 ? $acquisitionCost / $data['number_of_shares'] : 0,
                'acquired_date' => $data['transaction_date'],
                'acquisition_type' => $data['acquisition_type'] ?? 'purchase',
                'acquisition_reference' => $transaction->transaction_number,
                'is_active' => true,
            ]);

            // Update share class total
            $shareClass = ShareClass::find($data['share_class_id']);
            $shareClass->increment('total_shares', $data['number_of_shares']);

            return $transaction;
        });
    }

    /**
     * Transfer shares between shareholders.
     */
    public function transferShares(array $data): ShareTransaction
    {
        return DB::transaction(function () use ($data) {
            // Create the transaction
            $transaction = ShareTransaction::create([
                'transaction_date' => $data['transaction_date'],
                'transaction_type' => 'transfer',
                'share_class_id' => $data['share_class_id'],
                'from_shareholder_id' => $data['from_shareholder_id'],
                'to_shareholder_id' => $data['to_shareholder_id'],
                'number_of_shares' => $data['number_of_shares'],
                'price_per_share' => $data['price_per_share'] ?? null,
                'total_amount' => $data['total_amount'] ?? null,
                'currency' => $data['currency'] ?? 'NOK',
                'description' => $data['description'] ?? null,
                'document_reference' => $data['document_reference'] ?? null,
                'metadata' => $data['metadata'] ?? null,
                'created_by' => $data['created_by'],
            ]);

            // Reduce from seller
            $this->reduceShareholding(
                $data['from_shareholder_id'],
                $data['share_class_id'],
                $data['number_of_shares'],
                $data['transaction_date'],
                'sale'
            );

            // Add to buyer
            $acquisitionCost = $data['total_amount']
                ?? ($data['number_of_shares'] * ($data['price_per_share'] ?? 0));

            Shareholding::create([
                'shareholder_id' => $data['to_shareholder_id'],
                'share_class_id' => $data['share_class_id'],
                'number_of_shares' => $data['number_of_shares'],
                'acquisition_cost' => $acquisitionCost,
                'cost_per_share' => $acquisitionCost > 0 ? $acquisitionCost / $data['number_of_shares'] : 0,
                'acquired_date' => $data['transaction_date'],
                'acquisition_type' => 'purchase',
                'acquisition_reference' => $transaction->transaction_number,
                'is_active' => true,
            ]);

            return $transaction;
        });
    }

    /**
     * Redeem shares (innløsning).
     */
    public function redeemShares(array $data): ShareTransaction
    {
        return DB::transaction(function () use ($data) {
            // Create the transaction
            $transaction = ShareTransaction::create([
                'transaction_date' => $data['transaction_date'],
                'transaction_type' => 'redemption',
                'share_class_id' => $data['share_class_id'],
                'from_shareholder_id' => $data['shareholder_id'],
                'number_of_shares' => $data['number_of_shares'],
                'price_per_share' => $data['price_per_share'] ?? null,
                'total_amount' => $data['total_amount'] ?? null,
                'currency' => $data['currency'] ?? 'NOK',
                'description' => $data['description'] ?? null,
                'document_reference' => $data['document_reference'] ?? null,
                'metadata' => $data['metadata'] ?? null,
                'created_by' => $data['created_by'],
            ]);

            // Reduce shareholding
            $this->reduceShareholding(
                $data['shareholder_id'],
                $data['share_class_id'],
                $data['number_of_shares'],
                $data['transaction_date'],
                'redemption'
            );

            // Update share class total
            $shareClass = ShareClass::find($data['share_class_id']);
            $shareClass->decrement('total_shares', $data['number_of_shares']);

            return $transaction;
        });
    }

    /**
     * Reduce shareholder's shares using FIFO method.
     */
    private function reduceShareholding(
        int $shareholderId,
        int $shareClassId,
        int $sharesToReduce,
        $date,
        string $disposalType
    ): void {
        $holdings = Shareholding::where('shareholder_id', $shareholderId)
            ->where('share_class_id', $shareClassId)
            ->where('is_active', true)
            ->orderBy('acquired_date')
            ->get();

        $remaining = $sharesToReduce;

        foreach ($holdings as $holding) {
            if ($remaining <= 0) {
                break;
            }

            if ($holding->number_of_shares <= $remaining) {
                // Dispose entire holding
                $holding->dispose($disposalType, $date);
                $remaining -= $holding->number_of_shares;
            } else {
                // Partial disposal - split the holding
                $newShares = $holding->number_of_shares - $remaining;
                $proportionalCost = ($holding->acquisition_cost / $holding->number_of_shares) * $newShares;

                // Dispose original
                $holding->dispose($disposalType, $date);

                // Create new holding with remaining shares
                Shareholding::create([
                    'shareholder_id' => $holding->shareholder_id,
                    'share_class_id' => $holding->share_class_id,
                    'number_of_shares' => $newShares,
                    'acquisition_cost' => $proportionalCost,
                    'cost_per_share' => $holding->cost_per_share,
                    'acquired_date' => $holding->acquired_date,
                    'acquisition_type' => $holding->acquisition_type,
                    'acquisition_reference' => $holding->acquisition_reference,
                    'is_active' => true,
                ]);

                $remaining = 0;
            }
        }
    }

    /**
     * Create a dividend declaration.
     */
    public function declareDividend(array $data): Dividend
    {
        $shareClass = ShareClass::find($data['share_class_id']);

        $totalAmount = $data['total_amount']
            ?? ($shareClass->total_shares * $data['amount_per_share']);

        return Dividend::create([
            'fiscal_year' => $data['fiscal_year'],
            'declaration_date' => $data['declaration_date'],
            'record_date' => $data['record_date'],
            'payment_date' => $data['payment_date'],
            'share_class_id' => $data['share_class_id'],
            'amount_per_share' => $data['amount_per_share'],
            'total_amount' => $totalAmount,
            'dividend_type' => $data['dividend_type'] ?? 'ordinary',
            'status' => 'declared',
            'description' => $data['description'] ?? null,
            'resolution_reference' => $data['resolution_reference'] ?? null,
            'created_by' => $data['created_by'],
        ]);
    }

    /**
     * Get shareholder register at a specific date.
     */
    public function getShareholderRegisterAtDate(Carbon $date): Collection
    {
        return Shareholder::with(['shareholdings' => function ($query) use ($date) {
            $query->activeAtDate($date)->with('shareClass');
        }])
            ->whereHas('shareholdings', function ($query) use ($date) {
                $query->activeAtDate($date);
            })
            ->ordered()
            ->get()
            ->map(function ($shareholder) {
                $totalShares = $shareholder->shareholdings->sum('number_of_shares');
                $totalCapitalShares = ShareClass::active()->sum('total_shares');
                $ownershipPct = $totalCapitalShares > 0 ? ($totalShares / $totalCapitalShares) * 100 : 0;

                return [
                    'shareholder' => $shareholder,
                    'holdings' => $shareholder->shareholdings,
                    'total_shares' => $totalShares,
                    'ownership_percentage' => round($ownershipPct, 2),
                ];
            });
    }

    /**
     * Get ownership summary by share class.
     */
    public function getOwnershipSummary(): array
    {
        $shareClasses = ShareClass::active()->ordered()->get();
        $shareholders = Shareholder::active()->with('activeShareholdings.shareClass')->ordered()->get();

        $summary = [
            'share_classes' => [],
            'total_shares' => 0,
            'total_capital' => 0,
            'shareholders' => [],
        ];

        foreach ($shareClasses as $class) {
            $summary['share_classes'][] = [
                'id' => $class->id,
                'code' => $class->code,
                'name' => $class->name,
                'par_value' => $class->par_value,
                'total_shares' => $class->total_shares,
                'total_capital' => $class->getTotalCapital(),
                'shareholder_count' => $class->getShareholderCount(),
            ];
            $summary['total_shares'] += $class->total_shares;
            $summary['total_capital'] += $class->getTotalCapital();
        }

        foreach ($shareholders as $shareholder) {
            $shareholderData = [
                'id' => $shareholder->id,
                'name' => $shareholder->name,
                'type' => $shareholder->shareholder_type,
                'identifier' => $shareholder->getIdentifier(),
                'holdings_by_class' => [],
                'total_shares' => 0,
                'ownership_percentage' => 0,
            ];

            foreach ($shareholder->activeShareholdings as $holding) {
                $classCode = $holding->shareClass->code;
                if (! isset($shareholderData['holdings_by_class'][$classCode])) {
                    $shareholderData['holdings_by_class'][$classCode] = 0;
                }
                $shareholderData['holdings_by_class'][$classCode] += $holding->number_of_shares;
                $shareholderData['total_shares'] += $holding->number_of_shares;
            }

            if ($summary['total_shares'] > 0) {
                $shareholderData['ownership_percentage'] = round(
                    ($shareholderData['total_shares'] / $summary['total_shares']) * 100,
                    2
                );
            }

            $summary['shareholders'][] = $shareholderData;
        }

        // Sort shareholders by ownership descending
        usort($summary['shareholders'], fn ($a, $b) => $b['total_shares'] <=> $a['total_shares']);

        return $summary;
    }

    /**
     * Get transaction history for a period.
     */
    public function getTransactionHistory(Carbon $fromDate, Carbon $toDate, ?int $shareholderId = null): Collection
    {
        $query = ShareTransaction::with(['shareClass', 'fromShareholder', 'toShareholder', 'creator'])
            ->whereBetween('transaction_date', [$fromDate, $toDate])
            ->ordered();

        if ($shareholderId) {
            $query->forShareholder($shareholderId);
        }

        return $query->get();
    }

    /**
     * Get dividend history for a year.
     */
    public function getDividendHistory(int $year): Collection
    {
        return Dividend::with(['shareClass', 'creator'])
            ->forYear($year)
            ->ordered()
            ->get();
    }

    /**
     * Create annual shareholder report (RF-1086).
     */
    public function createShareholderReport(int $year, int $createdBy): ShareholderReport
    {
        $report = ShareholderReport::create([
            'year' => $year,
            'report_date' => Carbon::create($year, 12, 31),
            'share_capital' => 0,
            'total_shares' => 0,
            'number_of_shareholders' => 0,
            'status' => 'draft',
            'created_by' => $createdBy,
        ]);

        $report->generateAllSummaries();

        return $report;
    }

    /**
     * Get capital changes during a year.
     */
    public function getCapitalChanges(int $year): array
    {
        $yearStart = Carbon::create($year, 1, 1);
        $yearEnd = Carbon::create($year, 12, 31);

        // Opening balance
        $openingShares = Shareholding::activeAtDate($yearStart->copy()->subDay())
            ->sum('number_of_shares');

        $openingCapital = ShareClass::active()->get()
            ->sum(fn ($class) => $class->par_value * Shareholding::where('share_class_id', $class->id)
                ->activeAtDate($yearStart->copy()->subDay())
                ->sum('number_of_shares'));

        // Changes during year
        $issues = ShareTransaction::ofType('issue')
            ->inYear($year)
            ->sum('number_of_shares');

        $redemptions = ShareTransaction::ofType('redemption')
            ->inYear($year)
            ->sum('number_of_shares');

        // Closing balance
        $closingShares = Shareholding::activeAtDate($yearEnd)
            ->sum('number_of_shares');

        $closingCapital = ShareClass::active()->get()
            ->sum(fn ($class) => $class->par_value * Shareholding::where('share_class_id', $class->id)
                ->activeAtDate($yearEnd)
                ->sum('number_of_shares'));

        return [
            'year' => $year,
            'opening' => [
                'shares' => $openingShares,
                'capital' => $openingCapital,
            ],
            'changes' => [
                'issues' => $issues,
                'redemptions' => $redemptions,
                'net_change' => $issues - $redemptions,
            ],
            'closing' => [
                'shares' => $closingShares,
                'capital' => $closingCapital,
            ],
        ];
    }

    /**
     * Calculate dividend per shareholder for a given dividend.
     *
     * @return Collection<int, array{shareholder: Shareholder, shares: int, gross_amount: float, withholding_tax: float, net_amount: float}>
     */
    public function calculateDividendDistribution(Dividend $dividend): Collection
    {
        $eligibleShareholders = $dividend->getEligibleShareholders();

        return collect($eligibleShareholders)->map(function ($item) {
            $shareholder = $item['shareholder'];
            $shares = $item['shares'];
            $grossAmount = $item['amount'];

            // Calculate withholding tax (kildeskatt)
            // Norwegian residents: no withholding (handled via tax return)
            // Foreign shareholders: may have withholding tax
            $withholdingRate = $shareholder->isNorwegian() ? 0 : 0.25; // 25% for foreign shareholders
            $withholdingTax = $grossAmount * $withholdingRate;
            $netAmount = $grossAmount - $withholdingTax;

            return [
                'shareholder' => $shareholder,
                'shares' => $shares,
                'gross_amount' => $grossAmount,
                'withholding_tax' => $withholdingTax,
                'net_amount' => $netAmount,
            ];
        });
    }
}
