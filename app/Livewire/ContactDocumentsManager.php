<?php

namespace App\Livewire;

use App\Mail\DocumentMail;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Quote;
use App\Models\QuoteStatus;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class ContactDocumentsManager extends Component
{
    public int $contactId;

    public bool $showDetailModal = false;

    public ?string $detailType = null;

    public ?int $detailId = null;

    public ?int $selectedStatusId = null;

    public function mount(int $contactId): void
    {
        $this->contactId = $contactId;
    }

    public function showDetail(string $type, int $id): void
    {
        $this->detailType = $type;
        $this->detailId = $id;

        // Set the current status for quote
        if ($type === 'quote') {
            $quote = Quote::find($id);
            $this->selectedStatusId = $quote?->quote_status_id;
        }

        $this->showDetailModal = true;
    }

    public function closeDetail(): void
    {
        $this->showDetailModal = false;
        $this->detailType = null;
        $this->detailId = null;
        $this->selectedStatusId = null;
    }

    public function updateQuoteStatus(): void
    {
        if ($this->detailType !== 'quote' || ! $this->detailId || ! $this->selectedStatusId) {
            return;
        }

        $quote = Quote::find($this->detailId);
        if ($quote) {
            $quote->update(['quote_status_id' => $this->selectedStatusId]);
            $this->dispatch('$refresh');
        }
    }

    public function sendQuoteEmail(): void
    {
        if ($this->detailType !== 'quote' || ! $this->detailId) {
            return;
        }

        $quote = Quote::with('contact')->find($this->detailId);
        if (! $quote) {
            return;
        }

        $email = $quote->contact?->email;
        if (! $email) {
            session()->flash('error', 'Kontakten har ingen e-postadresse.');

            return;
        }

        Mail::to($email)->send(new DocumentMail($quote));

        $quote->update(['sent_at' => now()]);

        // Update status to 'sent' if it exists
        $sentStatus = QuoteStatus::where('code', 'sent')->first();
        if ($sentStatus) {
            $quote->update(['quote_status_id' => $sentStatus->id]);
            $this->selectedStatusId = $sentStatus->id;
        }

        $this->dispatch('$refresh');
        session()->flash('success', 'Tilbudet ble sendt til '.$email);
    }

    public function getQuoteStatusesProperty()
    {
        return QuoteStatus::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
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
