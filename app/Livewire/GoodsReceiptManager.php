<?php

namespace App\Livewire;

use App\Models\GoodsReceipt;
use App\Services\GoodsReceiptService;
use Flux\Flux;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class GoodsReceiptManager extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public $search = '';

    public $filterStatus = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function post(int $id, GoodsReceiptService $service): void
    {
        $receipt = GoodsReceipt::findOrFail($id);
        $this->authorize('post', $receipt);

        try {
            $service->post($receipt);
            Flux::toast(text: 'Varemottak bokfort', variant: 'success');
        } catch (\InvalidArgumentException $e) {
            Flux::toast(text: $e->getMessage(), variant: 'danger');
        }
    }

    public function delete(int $id, GoodsReceiptService $service): void
    {
        $receipt = GoodsReceipt::findOrFail($id);
        $this->authorize('delete', $receipt);

        try {
            $service->delete($receipt);
            Flux::toast(text: 'Varemottak slettet', variant: 'success');
        } catch (\InvalidArgumentException $e) {
            Flux::toast(text: $e->getMessage(), variant: 'danger');
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
