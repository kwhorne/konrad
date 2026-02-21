<?php

namespace App\Livewire;

use App\Models\StockLocation;
use App\Models\StockTransaction;
use Livewire\Component;
use Livewire\WithPagination;

class StockTransactionManager extends Component
{
    use WithPagination;

    public $search = '';

    public $filterLocation = '';

    public $filterType = '';

    public $dateFrom = '';

    public $dateTo = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $companyId = auth()->user()->current_company_id;

        $baseQuery = StockTransaction::where('company_id', $companyId)
            ->when($this->search, function ($q) {
                $q->where(function ($q2) {
                    $q2->where('transaction_number', 'like', "%{$this->search}%")
                        ->orWhereHas('product', function ($q3) {
                            $q3->where('name', 'like', "%{$this->search}%");
                        });
                });
            })
            ->when($this->filterLocation, fn ($q) => $q->where('stock_location_id', $this->filterLocation))
            ->when($this->filterType, fn ($q) => $q->where('transaction_type', $this->filterType))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('transaction_date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('transaction_date', '<=', $this->dateTo));

        $totals = (clone $baseQuery)->selectRaw(
            'COUNT(*) as total_count, '.
            'SUM(quantity) as total_quantity, '.
            'SUM(CASE WHEN unit_cost IS NOT NULL THEN quantity * unit_cost ELSE 0 END) as total_amount'
        )->first();

        $query = (clone $baseQuery)
            ->with(['product', 'stockLocation', 'creator'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id');

        $locations = StockLocation::where('company_id', $companyId)->active()->ordered()->get();

        return view('livewire.stock-transaction-manager', [
            'transactions' => $query->paginate(25),
            'locations' => $locations,
            'totals' => $totals,
        ]);
    }
}
