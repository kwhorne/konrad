<?php

namespace App\Livewire;

use App\Models\GoodsReceipt;
use App\Services\GoodsReceiptService;
use Livewire\Component;

class GoodsReceiptShow extends Component
{
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
    }

    public function post(GoodsReceiptService $service): void
    {
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
