<?php

namespace App\Livewire;

use App\Models\Contact;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Quote;
use Livewire\Component;

class ContactDocumentsManager extends Component
{
    public int $contactId;

    public bool $showDetailModal = false;

    public ?string $detailType = null;

    public ?int $detailId = null;

    public function mount(int $contactId): void
    {
        $this->contactId = $contactId;
    }

    public function showDetail(string $type, int $id): void
    {
        $this->detailType = $type;
        $this->detailId = $id;
        $this->showDetailModal = true;
    }

    public function closeDetail(): void
    {
        $this->showDetailModal = false;
        $this->detailType = null;
        $this->detailId = null;
    }

    public function getContactProperty(): Contact
    {
        return Contact::findOrFail($this->contactId);
    }

    public function getQuotesProperty()
    {
        return Quote::where('contact_id', $this->contactId)
            ->with('quoteStatus')
            ->orderByDesc('created_at')
            ->get();
    }

    public function getOrdersProperty()
    {
        return Order::where('contact_id', $this->contactId)
            ->with('orderStatus')
            ->orderByDesc('created_at')
            ->get();
    }

    public function getInvoicesProperty()
    {
        return Invoice::where('contact_id', $this->contactId)
            ->with('invoiceStatus')
            ->orderByDesc('created_at')
            ->get();
    }

    public function getSelectedDocumentProperty(): Quote|Order|Invoice|null
    {
        if (! $this->detailType || ! $this->detailId) {
            return null;
        }

        return match ($this->detailType) {
            'quote' => Quote::with(['lines', 'quoteStatus', 'project'])->find($this->detailId),
            'order' => Order::with(['lines', 'orderStatus', 'project'])->find($this->detailId),
            'invoice' => Invoice::with(['lines', 'invoiceStatus', 'project', 'payments'])->find($this->detailId),
            default => null,
        };
    }

    public function getStatusColorClass(string $color): string
    {
        return match ($color) {
            'blue' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
            'green' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
            'yellow' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
            'red' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
            'purple' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
            default => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300',
        };
    }

    public function render()
    {
        return view('livewire.contact-documents-manager');
    }
}
