<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Voucher;
use App\Models\VoucherLine;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * Get general ledger (hovedbok) for a period
     */
    public function getGeneralLedger(Carbon $fromDate, Carbon $toDate, ?int $accountId = null): Collection
    {
        $query = Account::query()
            ->active()
            ->ordered()
            ->with(['voucherLines' => function ($q) use ($fromDate, $toDate) {
                $q->whereHas('voucher', function ($vq) use ($fromDate, $toDate) {
                    $vq->where('is_posted', true)
                        ->whereBetween('voucher_date', [$fromDate, $toDate]);
                })
                    ->with(['voucher', 'contact'])
                    ->orderBy('id');
            }]);

        if ($accountId) {
            $query->where('id', $accountId);
        }

        return $query->get()->map(function ($account) use ($fromDate) {
            // Calculate opening balance (IB)
            $openingBalance = $this->getAccountBalanceAtDate($account, $fromDate->copy()->subDay());

            // Build ledger entries
            $runningBalance = $openingBalance;
            $entries = [];

            foreach ($account->voucherLines as $line) {
                if (in_array($account->account_type, ['asset', 'expense'])) {
                    $runningBalance += $line->debit - $line->credit;
                } else {
                    $runningBalance += $line->credit - $line->debit;
                }

                $entries[] = [
                    'date' => $line->voucher->voucher_date,
                    'voucher_number' => $line->voucher->voucher_number,
                    'description' => $line->description ?: $line->voucher->description,
                    'contact' => $line->contact?->company_name,
                    'debit' => $line->debit,
                    'credit' => $line->credit,
                    'balance' => $runningBalance,
                ];
            }

            return [
                'account' => $account,
                'opening_balance' => $openingBalance,
                'entries' => $entries,
                'total_debit' => $account->voucherLines->sum('debit'),
                'total_credit' => $account->voucherLines->sum('credit'),
                'closing_balance' => $runningBalance,
            ];
        })->filter(fn ($item) => count($item['entries']) > 0 || $item['opening_balance'] != 0);
    }

    /**
     * Get voucher journal (bilagsjournal) for a period
     */
    public function getVoucherJournal(Carbon $fromDate, Carbon $toDate): Collection
    {
        return Voucher::query()
            ->with(['lines.account', 'lines.contact', 'creator'])
            ->where('is_posted', true)
            ->whereBetween('voucher_date', [$fromDate, $toDate])
            ->orderBy('voucher_date')
            ->orderBy('voucher_number')
            ->get();
    }

    /**
     * Get trial balance (saldobalanse) at a specific date
     */
    public function getTrialBalance(Carbon $atDate): Collection
    {
        return Account::query()
            ->active()
            ->ordered()
            ->get()
            ->map(function ($account) use ($atDate) {
                $totals = VoucherLine::query()
                    ->where('account_id', $account->id)
                    ->whereHas('voucher', function ($q) use ($atDate) {
                        $q->where('is_posted', true)
                            ->where('voucher_date', '<=', $atDate);
                    })
                    ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
                    ->first();

                $debit = $totals->total_debit ?? 0;
                $credit = $totals->total_credit ?? 0;

                // Calculate balance based on account type
                if (in_array($account->account_type, ['asset', 'expense'])) {
                    $balance = $debit - $credit;
                    $debitBalance = $balance > 0 ? $balance : 0;
                    $creditBalance = $balance < 0 ? abs($balance) : 0;
                } else {
                    $balance = $credit - $debit;
                    $creditBalance = $balance > 0 ? $balance : 0;
                    $debitBalance = $balance < 0 ? abs($balance) : 0;
                }

                return [
                    'account' => $account,
                    'debit' => $debitBalance,
                    'credit' => $creditBalance,
                    'has_activity' => ($debit + $credit) > 0,
                ];
            })
            ->filter(fn ($item) => $item['has_activity']);
    }

    /**
     * Get income statement (resultatregnskap) for a period
     */
    public function getIncomeStatement(Carbon $fromDate, Carbon $toDate): array
    {
        // Revenue accounts (class 3)
        $revenues = $this->getAccountBalancesForPeriod($fromDate, $toDate, ['3']);

        // Cost of goods sold (class 4)
        $costOfGoods = $this->getAccountBalancesForPeriod($fromDate, $toDate, ['4']);

        // Payroll costs (class 5)
        $payrollCosts = $this->getAccountBalancesForPeriod($fromDate, $toDate, ['5']);

        // Depreciation (class 6)
        $depreciation = $this->getAccountBalancesForPeriod($fromDate, $toDate, ['6']);

        // Other operating costs (class 7)
        $otherOperatingCosts = $this->getAccountBalancesForPeriod($fromDate, $toDate, ['7']);

        // Financial items (class 8)
        $financialItems = $this->getAccountBalancesForPeriod($fromDate, $toDate, ['8']);

        $totalRevenue = $revenues->sum('amount');
        $totalCostOfGoods = $costOfGoods->sum('amount');
        $grossProfit = $totalRevenue - $totalCostOfGoods;

        $totalPayroll = $payrollCosts->sum('amount');
        $totalDepreciation = $depreciation->sum('amount');
        $totalOtherOperating = $otherOperatingCosts->sum('amount');
        $totalOperatingCosts = $totalPayroll + $totalDepreciation + $totalOtherOperating;

        $operatingProfit = $grossProfit - $totalOperatingCosts;

        $totalFinancial = $financialItems->sum('amount');
        $profitBeforeTax = $operatingProfit - $totalFinancial;

        return [
            'revenues' => $revenues,
            'total_revenue' => $totalRevenue,
            'cost_of_goods' => $costOfGoods,
            'total_cost_of_goods' => $totalCostOfGoods,
            'gross_profit' => $grossProfit,
            'payroll_costs' => $payrollCosts,
            'total_payroll' => $totalPayroll,
            'depreciation' => $depreciation,
            'total_depreciation' => $totalDepreciation,
            'other_operating_costs' => $otherOperatingCosts,
            'total_other_operating' => $totalOtherOperating,
            'total_operating_costs' => $totalOperatingCosts,
            'operating_profit' => $operatingProfit,
            'financial_items' => $financialItems,
            'total_financial' => $totalFinancial,
            'profit_before_tax' => $profitBeforeTax,
        ];
    }

    /**
     * Get balance sheet (balanse) at a specific date
     */
    public function getBalanceSheet(Carbon $atDate): array
    {
        // Assets (class 1)
        $assets = $this->getAccountBalancesAtDate($atDate, ['1']);

        // Equity and liabilities (class 2)
        $equityAndLiabilities = $this->getAccountBalancesAtDate($atDate, ['2']);

        // Calculate profit/loss from income statement (classes 3-8)
        $incomeStatement = $this->getIncomeStatement(
            Carbon::create($atDate->year, 1, 1),
            $atDate
        );
        $currentYearProfit = $incomeStatement['profit_before_tax'];

        $totalAssets = $assets->sum('amount');
        $totalEquityAndLiabilities = $equityAndLiabilities->sum('amount') + $currentYearProfit;

        return [
            'assets' => $assets,
            'total_assets' => $totalAssets,
            'equity_and_liabilities' => $equityAndLiabilities,
            'current_year_profit' => $currentYearProfit,
            'total_equity_and_liabilities' => $totalEquityAndLiabilities,
            'is_balanced' => abs($totalAssets - $totalEquityAndLiabilities) < 0.01,
        ];
    }

    /**
     * Get account balance at a specific date
     */
    private function getAccountBalanceAtDate(Account $account, Carbon $atDate): float
    {
        $totals = VoucherLine::query()
            ->where('account_id', $account->id)
            ->whereHas('voucher', function ($q) use ($atDate) {
                $q->where('is_posted', true)
                    ->where('voucher_date', '<=', $atDate);
            })
            ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->first();

        $debit = $totals->total_debit ?? 0;
        $credit = $totals->total_credit ?? 0;

        if (in_array($account->account_type, ['asset', 'expense'])) {
            return $debit - $credit;
        }

        return $credit - $debit;
    }

    /**
     * Get account balances for a period (for income statement)
     */
    private function getAccountBalancesForPeriod(Carbon $fromDate, Carbon $toDate, array $classes): Collection
    {
        return Account::query()
            ->active()
            ->whereIn('account_class', $classes)
            ->ordered()
            ->get()
            ->map(function ($account) use ($fromDate, $toDate) {
                $totals = VoucherLine::query()
                    ->where('account_id', $account->id)
                    ->whereHas('voucher', function ($q) use ($fromDate, $toDate) {
                        $q->where('is_posted', true)
                            ->whereBetween('voucher_date', [$fromDate, $toDate]);
                    })
                    ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
                    ->first();

                $debit = $totals->total_debit ?? 0;
                $credit = $totals->total_credit ?? 0;

                // For income statement: revenue is credit-normal, expenses are debit-normal
                if ($account->account_type === 'revenue') {
                    $amount = $credit - $debit;
                } else {
                    $amount = $debit - $credit;
                }

                return [
                    'account' => $account,
                    'amount' => $amount,
                    'has_activity' => ($debit + $credit) > 0,
                ];
            })
            ->filter(fn ($item) => $item['has_activity']);
    }

    /**
     * Get account balances at a specific date (for balance sheet)
     */
    private function getAccountBalancesAtDate(Carbon $atDate, array $classes): Collection
    {
        return Account::query()
            ->active()
            ->whereIn('account_class', $classes)
            ->ordered()
            ->get()
            ->map(function ($account) use ($atDate) {
                $totals = VoucherLine::query()
                    ->where('account_id', $account->id)
                    ->whereHas('voucher', function ($q) use ($atDate) {
                        $q->where('is_posted', true)
                            ->where('voucher_date', '<=', $atDate);
                    })
                    ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
                    ->first();

                $debit = $totals->total_debit ?? 0;
                $credit = $totals->total_credit ?? 0;

                // Assets are debit-normal, liabilities/equity are credit-normal
                if ($account->account_type === 'asset') {
                    $amount = $debit - $credit;
                } else {
                    $amount = $credit - $debit;
                }

                return [
                    'account' => $account,
                    'amount' => $amount,
                    'has_activity' => ($debit + $credit) > 0,
                ];
            })
            ->filter(fn ($item) => $item['has_activity']);
    }
}
