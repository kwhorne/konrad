<?php

namespace App\Livewire;

use App\Models\GoodsReceipt;
use App\Services\GoodsReceiptService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class GoodsReceiptManager extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public $search = '';

    public $filterStatus = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function post(int $id, GoodsReceiptService $service)
    {
        $receipt = GoodsReceipt::findOrFail($id);
        $this->authorize('post', $receipt);

        try {
            $service->post($receipt);
            $this->dispatch('toast', message: 'Varemottak bokfort', type: 'success');
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function delete(int $id, GoodsReceiptService $service)
    {
        $receipt = GoodsReceipt::findOrFail($id);
        $this->authorize('delete', $receipt);

        try {
            $service->delete($receipt);
            $this->dispatch('toast', message: 'Varemottak slettet', type: 'success');
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        $query = GoodsReceipt::where('company_id', auth()->user()->current_company_id)
            ->with(['contact', 'stockLocation', 'purchaseOrder', 'creator'])
            ->when($this->search, function ($q) {
                $q->where(function ($q2) {
                    $q2->where('receipt_number', 'like', "%{$this->search}%")
                        ->orWhere('supplier_delivery_note', 'like', "%{$this->search}%")
                        ->orWhereHas('contact', function ($q3) {
                            $q3->where('company_name', 'like', "%{$this->search}%");
                        });
                });
            })
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->orderByDesc('receipt_date')
            ->orderByDesc('id');

        return view('livewire.goods-receipt-manager', [
            'receipts' => $query->paginate(20),
        ]);
    }
}
