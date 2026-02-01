<?php

namespace App\Livewire;

use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderService;
use Livewire\Component;

class PurchaseOrderShow extends Component
{
    public PurchaseOrder $purchaseOrder;

    public function mount(int $purchaseOrderId): void
    {
        $this->purchaseOrder = PurchaseOrder::with([
            'contact',
            'stockLocation',
            'creator',
            'approver',
            'lines.product',
            'lines.vatRate',
            'goodsReceipts',
        ])->findOrFail($purchaseOrderId);
    }

    public function submitForApproval(PurchaseOrderService $service): void
    {
        try {
            $service->submitForApproval($this->purchaseOrder);
            $this->purchaseOrder->refresh();
            $this->dispatch('toast', message: 'Innkjopsordre sendt til godkjenning', type: 'success');
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function approve(PurchaseOrderService $service): void
    {
        try {
            $service->approve($this->purchaseOrder);
            $this->purchaseOrder->refresh();
            $this->dispatch('toast', message: 'Innkjopsordre godkjent', type: 'success');
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function markAsSent(PurchaseOrderService $service): void
    {
        try {
            $service->markAsSent($this->purchaseOrder);
            $this->purchaseOrder->refresh();
            $this->dispatch('toast', message: 'Innkjopsordre merket som sendt', type: 'success');
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function cancel(PurchaseOrderService $service): void
    {
        try {
            $service->cancel($this->purchaseOrder);
            $this->purchaseOrder->refresh();
            $this->dispatch('toast', message: 'Innkjopsordre kansellert', type: 'success');
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.purchase-order-show');
    }
}
