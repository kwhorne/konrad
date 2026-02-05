<?php

namespace App\Livewire;

use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class PurchaseOrderManager extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public $search = '';

    public $filterStatus = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function approve(int $id, PurchaseOrderService $service)
    {
        $po = PurchaseOrder::findOrFail($id);
        $this->authorize('approve', $po);

        try {
            $service->approve($po);
            $this->dispatch('toast', message: 'Innkjopsordre godkjent', type: 'success');
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function markAsSent(int $id, PurchaseOrderService $service)
    {
        $po = PurchaseOrder::findOrFail($id);
        $this->authorize('markAsSent', $po);

        try {
            $service->markAsSent($po);
            $this->dispatch('toast', message: 'Innkjopsordre merket som sendt', type: 'success');
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function cancel(int $id, PurchaseOrderService $service)
    {
        $po = PurchaseOrder::findOrFail($id);
        $this->authorize('cancel', $po);

        try {
            $service->cancel($po);
            $this->dispatch('toast', message: 'Innkjopsordre kansellert', type: 'success');
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        $query = PurchaseOrder::where('company_id', auth()->user()->current_company_id)
            ->with(['contact', 'stockLocation', 'creator'])
            ->when($this->search, function ($q) {
                $q->where(function ($q2) {
                    $q2->where('po_number', 'like', "%{$this->search}%")
                        ->orWhereHas('contact', function ($q3) {
                            $q3->where('company_name', 'like', "%{$this->search}%");
                        });
                });
            })
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->ordered();

        return view('livewire.purchase-order-manager', [
            'purchaseOrders' => $query->paginate(20),
        ]);
    }
}
