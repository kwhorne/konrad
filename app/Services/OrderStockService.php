<?php

namespace App\Services;

use App\Models\InventorySettings;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\StockLocation;

class OrderStockService
{
    public function __construct(
        private StockService $stockService
    ) {}

    /**
     * Reserve stock for all stocked products in an order.
     * Called when order status changes to 'confirmed'.
     */
    public function reserveStockForOrder(Order $order): void
    {
        $settings = InventorySettings::forCompany($order->company_id);

        // Check if auto-reservation is enabled
        if (! $settings?->auto_reserve_on_order) {
            return;
        }

        $stockLocation = $this->getDefaultStockLocation($order->company_id);
        if (! $stockLocation) {
            return;
        }

        $order->load('lines.product');

        foreach ($order->lines as $line) {
            if (! $line->product?->is_stocked) {
                continue;
            }

            try {
                $this->stockService->reserve(
                    product: $line->product,
                    stockLocation: $stockLocation,
                    quantity: $line->quantity,
                    referenceType: OrderLine::class,
                    referenceId: $line->id
                );
            } catch (\InvalidArgumentException $e) {
                // Log but don't fail - insufficient stock shouldn't block order
                \Log::warning("Could not reserve stock for order line {$line->id}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Release all stock reservations for an order.
     * Called when order is cancelled.
     */
    public function releaseReservationsForOrder(Order $order): void
    {
        $stockLocation = $this->getDefaultStockLocation($order->company_id);
        if (! $stockLocation) {
            return;
        }

        $order->load('lines.product');

        foreach ($order->lines as $line) {
            if (! $line->product?->is_stocked) {
                continue;
            }

            $this->stockService->releaseReservation(
                product: $line->product,
                stockLocation: $stockLocation,
                referenceType: OrderLine::class,
                referenceId: $line->id
            );
        }
    }

    /**
     * Issue stock for an invoice (fulfill reservations or direct issue).
     * Called when order is converted to invoice.
     */
    public function issueStockForInvoice(Invoice $invoice): void
    {
        $stockLocation = $this->getDefaultStockLocation($invoice->company_id);
        if (! $stockLocation) {
            return;
        }

        $invoice->load(['lines.product', 'lines.orderLine']);

        foreach ($invoice->lines as $line) {
            if (! $line->product?->is_stocked) {
                continue;
            }

            // Try to fulfill existing reservation from order line
            if ($line->order_line_id) {
                try {
                    $this->stockService->fulfillReservation(
                        product: $line->product,
                        stockLocation: $stockLocation,
                        referenceType: OrderLine::class,
                        referenceId: $line->order_line_id
                    );

                    continue;
                } catch (\InvalidArgumentException $e) {
                    // No reservation found, fall through to direct issue
                }
            }

            // Direct issue if no reservation
            try {
                $this->stockService->issue(
                    product: $line->product,
                    stockLocation: $stockLocation,
                    quantity: $line->quantity,
                    referenceType: InvoiceLine::class,
                    referenceId: $line->id,
                    notes: "Faktura {$invoice->invoice_number}"
                );
            } catch (\InvalidArgumentException $e) {
                \Log::warning("Could not issue stock for invoice line {$line->id}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Check if all products in an order have sufficient stock.
     *
     * @return array{sufficient: bool, shortages: array}
     */
    public function checkStockAvailability(Order $order): array
    {
        $stockLocation = $this->getDefaultStockLocation($order->company_id);
        $shortages = [];

        if (! $stockLocation) {
            return ['sufficient' => true, 'shortages' => []];
        }

        $order->load('lines.product.stockLevels');

        foreach ($order->lines as $line) {
            if (! $line->product?->is_stocked) {
                continue;
            }

            $available = $this->stockService->getAvailableStock($line->product, $stockLocation);

            if ($available < $line->quantity) {
                $shortages[] = [
                    'product' => $line->product->name,
                    'required' => $line->quantity,
                    'available' => $available,
                    'shortage' => $line->quantity - $available,
                ];
            }
        }

        return [
            'sufficient' => empty($shortages),
            'shortages' => $shortages,
        ];
    }

    /**
     * Get the default stock location for a company.
     */
    private function getDefaultStockLocation(int $companyId): ?StockLocation
    {
        $settings = InventorySettings::forCompany($companyId);

        if ($settings?->default_stock_location_id) {
            return StockLocation::find($settings->default_stock_location_id);
        }

        // Fall back to first active location
        return StockLocation::where('company_id', $companyId)
            ->where('is_active', true)
            ->first();
    }
}
