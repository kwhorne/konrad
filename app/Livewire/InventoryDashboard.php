<?php

namespace App\Livewire;

use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\StockLevel;
use App\Models\StockTransaction;
use Livewire\Component;

class InventoryDashboard extends Component
{
    public function render()
    {
        $companyId = auth()->user()->current_company_id;

        // Core KPI aggregates
        $kpis = StockLevel::where('company_id', $companyId)
            ->selectRaw(
                'COUNT(DISTINCT product_id) as total_skus, '.
                'SUM(quantity_on_hand) as total_units, '.
                'SUM(quantity_reserved) as total_reserved, '.
                'SUM(quantity_on_hand * average_cost) as total_stock_value, '.
                'COUNT(CASE WHEN quantity_on_hand <= 0 THEN 1 END) as zero_stock_count'
            )
            ->first();

        // Products below reorder point
        $criticalItems = StockLevel::where('company_id', $companyId)
            ->with('product')
            ->whereHas('product', fn ($q) => $q->whereNotNull('reorder_point'))
            ->whereRaw('(quantity_on_hand - quantity_reserved) <= (SELECT reorder_point FROM products WHERE products.id = stock_levels.product_id)')
            ->orderByRaw('(quantity_on_hand - quantity_reserved) - (SELECT reorder_point FROM products WHERE products.id = stock_levels.product_id)')
            ->get()
            ->map(function ($level) {
                $available = (float) $level->quantity_on_hand - (float) $level->quantity_reserved;
                $reorderPoint = (float) $level->product->reorder_point;

                return [
                    'name' => $level->product->name,
                    'sku' => $level->product->sku,
                    'available' => $available,
                    'reorder_point' => $reorderPoint,
                    'shortfall' => max(0, $reorderPoint - $available),
                    'is_critical' => $available <= 0,
                ];
            });

        // Top 5 products by inventory value (ABC analysis)
        $totalStockValue = (float) ($kpis->total_stock_value ?? 0);
        $topValueProducts = StockLevel::where('company_id', $companyId)
            ->with('product')
            ->where('quantity_on_hand', '>', 0)
            ->selectRaw('*, (quantity_on_hand * average_cost) as line_value')
            ->orderByDesc('line_value')
            ->limit(5)
            ->get()
            ->map(function ($level) use ($totalStockValue) {
                return [
                    'name' => $level->product?->name ?? '—',
                    'sku' => $level->product?->sku,
                    'quantity' => (float) $level->quantity_on_hand,
                    'average_cost' => (float) $level->average_cost,
                    'value' => (float) $level->line_value,
                    'pct' => $totalStockValue > 0 ? round(((float) $level->line_value / $totalStockValue) * 100, 1) : 0,
                ];
            });

        // Open purchase orders
        $openPurchaseOrders = PurchaseOrder::where('company_id', $companyId)
            ->with('contact')
            ->whereNotIn('status', ['received', 'cancelled'])
            ->orderBy('expected_date')
            ->limit(8)
            ->get();

        $openPurchaseOrdersTotal = PurchaseOrder::where('company_id', $companyId)
            ->whereNotIn('status', ['received', 'cancelled'])
            ->sum('total');

        // Recent transactions
        $recentTransactions = StockTransaction::where('company_id', $companyId)
            ->with(['product', 'stockLocation'])
            ->latest('transaction_date')
            ->limit(8)
            ->get();

        // Recent goods receipts
        $recentReceipts = GoodsReceipt::where('company_id', $companyId)
            ->with(['contact', 'stockLocation'])
            ->latest('receipt_date')
            ->limit(5)
            ->get();

        return view('livewire.inventory-dashboard', [
            'kpis' => $kpis,
            'criticalItems' => $criticalItems,
            'topValueProducts' => $topValueProducts,
            'openPurchaseOrders' => $openPurchaseOrders,
            'openPurchaseOrdersTotal' => $openPurchaseOrdersTotal,
            'recentTransactions' => $recentTransactions,
            'recentReceipts' => $recentReceipts,
        ]);
    }
}
