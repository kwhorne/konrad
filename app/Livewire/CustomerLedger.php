<?php

namespace App\Livewire;

use App\Services\LedgerService;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerLedger extends Component
{
    use WithPagination;

    public string $search = '';

    public string $sortBy = 'due_date';

    public string $sortDirection = 'asc';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function getAgingProperty(): array
    {
        return app(LedgerService::class)->getCustomerAging();
    }

    public function getTotalBalanceProperty(): float
    {
        return app(LedgerService::class)->getTotalCustomerBalance();
    }

    public function render()
    {
        $query = \App\Models\Invoice::with(['contact', 'invoiceStatus'])
            ->where('invoice_type', 'invoice')
            ->where('balance', '>', 0);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('invoice_number', 'like', "%{$this->search}%")
                    ->orWhere('customer_name', 'like', "%{$this->search}%")
                    ->orWhereHas('contact', function ($q) {
                        $q->where('company_name', 'like', "%{$this->search}%");
                    });
            });
        }

        $invoices = $query->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(20);

        return view('livewire.customer-ledger', [
            'invoices' => $invoices,
        ]);
    }
}
