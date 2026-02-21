<?php

namespace App\Livewire;

use App\Models\StockLevel;
use App\Models\StockLocation;
use Livewire\Component;
use Livewire\WithPagination;

class StockLevelManager extends Component
{
    use WithPagination;

    public $search = '';

    public $filterLocation = '';

    public $filterBelowReorder = false;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $companyId = auth()->user()->current_company_id;

        $baseQuery = StockLevel::where('company_id', $companyId)
            ->when($this->search, function ($q) {
                $q->whereHas('product', function ($q2) {
                    $q2->where('name', 'like', "%{$this->search}%")
                        ->orWhere('sku', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterLocation, fn ($q) => $q->where('stock_location_id', $this->filterLocation))
            ->when($this->filterBelowReorder, function ($q) {
                $q->whereHas('product', function ($q2) {
                    $q2->whereNotNull('reorder_point');
                })->whereRaw('quantity_on_hand - quantity_reserved <= (SELECT reorder_point FROM products WHERE products.id = stock_levels.product_id)');
            });

        $totals = (clone $baseQuery)->selectRaw(
            'SUM(quantity_on_hand) as total_on_hand, '.
            'SUM(quantity_reserved) as total_reserved, '.
            'SUM(quantity_on_hand - quantity_reserved) as total_available, '.
            'SUM(quantity_on_hand * average_cost) as total_stock_value'
        )->first();

        $query = (clone $baseQuery)
            ->with(['product', 'stockLocation'])
            ->orderBy('product_id');

        $locations = StockLocation::where('company_id', $companyId)->active()->ordered()->get();

        return view('livewire.stock-level-manager', [
            'stockLevels' => $query->paginate(25),
            'locations' => $locations,
            'totals' => $totals,
        ]);
    }
}
