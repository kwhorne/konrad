<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Services\OrderStockService;

class OrderObserver
{
    public function __construct(
        private OrderStockService $orderStockService
    ) {}

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // Skip if inventory feature is disabled
        if (! config('features.inventory')) {
            return;
        }

        // Check if order_status_id changed
        if (! $order->wasChanged('order_status_id')) {
            return;
        }

        $oldStatusId = $order->getOriginal('order_status_id');
        $newStatusId = $order->order_status_id;

        $oldStatus = $oldStatusId ? OrderStatus::find($oldStatusId)?->code : null;
        $newStatus = $newStatusId ? OrderStatus::find($newStatusId)?->code : null;

        // Reserve stock when order is confirmed
        if ($newStatus === 'confirmed' && $oldStatus !== 'confirmed') {
            $this->orderStockService->reserveStockForOrder($order);
        }

        // Release reservations when order is cancelled
        if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
            $this->orderStockService->releaseReservationsForOrder($order);
        }
    }
}
