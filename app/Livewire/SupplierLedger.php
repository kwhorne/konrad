<?php

namespace App\Livewire;

use App\Models\SupplierInvoice;
use App\Services\LedgerService;
use Livewire\Component;
use Livewire\WithPagination;

class SupplierLedger extends Component
{
    use WithPagination;

    public string $search = '';

    public string $sortBy = 'due_date';

    public string $sortDirection = 'asc';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    private const SORTABLE_COLUMNS = [
        'internal_number',
        'invoice_date',
        'due_date',
        'total',
        'balance',
    ];

    public function sort(string $column): void
    {
        if (! in_array($column, self::SORTABLE_COLUMNS)) {
            return;
        }

        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function getAgingProperty(): array
    {
        return app(LedgerService::class)->getSupplierAging();
    }

    public function getTotalBalanceProperty(): float
    {
        return app(LedgerService::class)->getTotalSupplierBalance();
    }

    public function render()
    {
        $query = SupplierInvoice::with(['contact'])
            ->where('balance', '>', 0);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('invoice_number', 'like', "%{$this->search}%")
                    ->orWhere('internal_number', 'like', "%{$this->search}%")
                    ->orWhereHas('contact', function ($q) {
                        $q->where('company_name', 'like', "%{$this->search}%");
                    });
            });
        }

        $invoices = $query->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(20);

        return view('livewire.supplier-ledger', [
            'invoices' => $invoices,
        ]);
    }
}
