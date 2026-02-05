<?php

namespace App\Livewire;

use App\Models\GoodsReceipt;
use App\Services\GoodsReceiptService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class GoodsReceiptShow extends Component
{
    use AuthorizesRequests;

    public GoodsReceipt $goodsReceipt;

    public function mount(int $goodsReceiptId): void
    {
        $this->goodsReceipt = GoodsReceipt::with([
            'contact',
            'stockLocation',
            'purchaseOrder',
            'creator',
            'poster',
            'lines.product',
            'lines.purchaseOrderLine',
        ])->findOrFail($goodsReceiptId);
        $this->authorize('view', $this->goodsReceipt);
    }

    public function post(GoodsReceiptService $service): void
    {
        $this->authorize('post', $this->goodsReceipt);

        try {
            $service->post($this->goodsReceipt);
            $this->goodsReceipt->refresh();
            $this->dispatch('toast', message: 'Varemottak bokfort', type: 'success');
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function reverse(GoodsReceiptService $service): void
    {
        $this->authorize('reverse', $this->goodsReceipt);

        try {
            $service->reverse($this->goodsReceipt);
            $this->goodsReceipt->refresh();
            $this->dispatch('toast', message: 'Varemottak reversert', type: 'success');
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function delete(GoodsReceiptService $service): void
    {
        $this->authorize('delete', $this->goodsReceipt);

        try {
            $service->delete($this->goodsReceipt);
            $this->dispatch('toast', message: 'Varemottak slettet', type: 'success');
            $this->redirect(route('purchasing.goods-receipts.index'), navigate: true);
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.goods-receipt-show');
    }
}
