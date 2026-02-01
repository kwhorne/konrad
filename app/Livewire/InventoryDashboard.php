<?php

namespace App\Livewire;

use App\Models\GoodsReceipt;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\StockLevel;
use App\Models\StockTransaction;
use Livewire\Component;

class InventoryDashboard extends Component
{
    public function render()
    {
        $companyId = auth()->user()->current_company_id;

        // Count of stocked products
        $stockedProductsCount = Product::where('company_id', $companyId)
            ->where('is_stocked', true)
            ->count();

        // Total inventory value (quantity * average cost)
        $totalValue = StockLevel::where('company_id', $companyId)
            ->selectRaw('SUM(quantity_on_hand * average_cost) as total')
            ->value('total') ?? 0;

        // Open purchase orders (not received or cancelled)
        $openPurchaseOrders = PurchaseOrder::where('company_id', $companyId)
            ->whereNotIn('status', ['received', 'cancelled'])
            ->count();

        // Products below reorder point
        $belowReorderPoint = StockLevel::where('company_id', $companyId)
            ->whereHas('product', function ($q) {
                $q->whereNotNull('reorder_point');
            })
            ->get()
            ->filter(function ($level) {
                $available = $level->quantity_on_hand - $level->quantity_reserved;

                return $available <= $level->product->reorder_point;
            })
            ->count();

        // Recent stock transactions
        $recentTransactions = StockTransaction::where('company_id', $companyId)
            ->with(['product', 'stockLocation'])
            ->latest('transaction_date')
            ->limit(5)
            ->get();

        // Recent goods receipts
        $recentReceipts = GoodsReceipt::where('company_id', $companyId)
            ->with(['contact', 'stockLocation'])
            ->latest('receipt_date')
            ->limit(5)
            ->get();

        return view('livewire.inventory-dashboard', [
            'stockedProductsCount' => $stockedProductsCount,
            'totalValue' => $totalValue,
            'openPurchaseOrders' => $openPurchaseOrders,
            'belowReorderPoint' => $belowReorderPoint,
            'recentTransactions' => $recentTransactions,
            'recentReceipts' => $recentReceipts,
        ]);
    }
}
