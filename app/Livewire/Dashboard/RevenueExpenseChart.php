<?php

namespace App\Livewire\Dashboard;

use App\Models\VoucherLine;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class RevenueExpenseChart extends Component
{
    public array $chartData = [];

    public function mount(): void
    {
        $this->loadChartData();
    }

    public function loadChartData(): void
    {
        // Get last 12 months of data
        $startDate = now()->subMonths(11)->startOfMonth();
        $endDate = now()->endOfMonth();

        // Query revenue (account_class = 3, credit-normal so revenue = credit - debit)
        $revenueData = VoucherLine::query()
            ->join('vouchers', 'voucher_lines.voucher_id', '=', 'vouchers.id')
            ->join('accounts', 'voucher_lines.account_id', '=', 'accounts.id')
            ->where('vouchers.is_posted', true)
            ->where('accounts.account_class', '3')
            ->whereBetween('vouchers.voucher_date', [$startDate, $endDate])
            ->select(
                DB::raw("DATE_FORMAT(vouchers.voucher_date, '%Y-%m') as month"),
                DB::raw('SUM(voucher_lines.credit) - SUM(voucher_lines.debit) as amount')
            )
            ->groupBy('month')
            ->pluck('amount', 'month')
            ->toArray();

        // Query expenses (account_class in 4,5,6,7, debit-normal so expense = debit - credit)
        $expenseData = VoucherLine::query()
            ->join('vouchers', 'voucher_lines.voucher_id', '=', 'vouchers.id')
            ->join('accounts', 'voucher_lines.account_id', '=', 'accounts.id')
            ->where('vouchers.is_posted', true)
            ->whereIn('accounts.account_class', ['4', '5', '6', '7'])
            ->whereBetween('vouchers.voucher_date', [$startDate, $endDate])
            ->select(
                DB::raw("DATE_FORMAT(vouchers.voucher_date, '%Y-%m') as month"),
                DB::raw('SUM(voucher_lines.debit) - SUM(voucher_lines.credit) as amount')
            )
            ->groupBy('month')
            ->pluck('amount', 'month')
            ->toArray();

        // Build chart data for last 12 months
        $this->chartData = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $monthKey = $current->format('Y-m');
            $this->chartData[] = [
                'month' => $current->translatedFormat('M Y'),
                'date' => $current->format('Y-m-d'),
                'revenue' => (float) ($revenueData[$monthKey] ?? 0),
                'expenses' => (float) ($expenseData[$monthKey] ?? 0),
            ];
            $current->addMonth();
        }
    }

    public function render()
    {
        return view('livewire.dashboard.revenue-expense-chart');
    }
}
