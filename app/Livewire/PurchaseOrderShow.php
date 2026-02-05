<?php

namespace App\Livewire;

use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class PurchaseOrderShow extends Component
{
    use AuthorizesRequests;

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
        $this->authorize('view', $this->purchaseOrder);
    }

    public function submitForApproval(PurchaseOrderService $service): void
    {
        $this->authorize('submitForApproval', $this->purchaseOrder);

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
        $this->authorize('approve', $this->purchaseOrder);

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
        $this->authorize('markAsSent', $this->purchaseOrder);

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
        $this->authorize('cancel', $this->purchaseOrder);

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
