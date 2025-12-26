<?php

namespace App\Livewire;

use App\Models\Contact;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\InvoicePayment;
use App\Models\InvoiceStatus;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Project;
use App\Models\VatRate;
use Livewire\Component;
use Livewire\WithPagination;

class InvoiceManager extends Component
{
    use WithPagination;

    public $showModal = false;

    public $showLineModal = false;

    public $showPaymentModal = false;

    public $editingId = null;

    public $search = '';

    public $filterStatus = '';

    public $filterContact = '';

    public $filterType = 'all';

    // Invoice form fields
    public $invoice_type = 'invoice';

    public $title = '';

    public $description = '';

    public $contact_id = '';

    public $project_id = '';

    public $invoice_status_id = '';

    public $invoice_date = '';

    public $due_date = '';

    public $payment_terms_days = 30;

    public $reminder_days = 14;

    public $terms_conditions = '';

    public $internal_notes = '';

    public $customer_name = '';

    public $customer_address = '';

    public $customer_postal_code = '';

    public $customer_city = '';

    public $customer_country = 'Norge';

    public $our_reference = '';

    public $customer_reference = '';

    public $is_active = true;

    // Line form fields
    public $editingLineId = null;

    public $line_product_id = '';

    public $line_description = '';

    public $line_quantity = 1;

    public $line_unit = 'stk';

    public $line_unit_price = '';

    public $line_discount_percent = 0;

    public $line_vat_rate_id = '';

    public $line_vat_percent = 25;

    // Payment form fields
    public $editingPaymentId = null;

    public $payment_method_id = '';

    public $payment_date = '';

    public $payment_amount = '';

    public $payment_reference = '';

    public $payment_notes = '';

    // Currently editing invoice
    public $currentInvoiceId = null;

    public $invoiceLines = [];

    public $invoicePayments = [];

    public function mount(): void
    {
        // Check for contact_id in query parameters to auto-open create modal
        if (request()->has('contact_id')) {
            $contactId = request()->get('contact_id');
            $contact = Contact::find($contactId);

            if ($contact) {
                $this->contact_id = $contactId;
                $this->customer_name = $contact->company_name;
                $this->customer_address = $contact->billing_address ?? $contact->address;
                $this->customer_postal_code = $contact->billing_postal_code ?? $contact->postal_code;
                $this->customer_city = $contact->billing_city ?? $contact->city;
                $this->customer_country = $contact->billing_country ?? $contact->country ?? 'Norge';
                $this->payment_terms_days = $contact->payment_terms_days ?? 30;
                $this->invoice_date = now()->format('Y-m-d');
                $this->due_date = now()->addDays($this->payment_terms_days)->format('Y-m-d');
                $this->showModal = true;
            }
        }
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'contact_id' => 'required|exists:contacts,id',
            'project_id' => 'nullable|exists:projects,id',
            'invoice_status_id' => 'nullable|exists:invoice_statuses,id',
            'invoice_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'payment_terms_days' => 'nullable|integer|min:0',
            'reminder_days' => 'nullable|integer|min:0',
            'terms_conditions' => 'nullable|string',
            'internal_notes' => 'nullable|string',
            'customer_name' => 'nullable|string|max:255',
            'customer_address' => 'nullable|string|max:255',
            'customer_postal_code' => 'nullable|string|max:20',
            'customer_city' => 'nullable|string|max:100',
            'customer_country' => 'nullable|string|max:100',
            'our_reference' => 'nullable|string|max:100',
            'customer_reference' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ];
    }

    protected function lineRules(): array
    {
        return [
            'line_product_id' => 'nullable|exists:products,id',
            'line_description' => 'required|string',
            'line_quantity' => 'required|numeric',
            'line_unit' => 'required|string|max:20',
            'line_unit_price' => 'required|numeric|min:0',
            'line_discount_percent' => 'nullable|numeric|min:0|max:100',
            'line_vat_rate_id' => 'nullable|exists:vat_rates,id',
            'line_vat_percent' => 'required|numeric|min:0|max:100',
        ];
    }

    protected function paymentRules(): array
    {
        return [
            'payment_method_id' => 'required|exists:payment_methods,id',
            'payment_date' => 'required|date',
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_reference' => 'nullable|string|max:100',
            'payment_notes' => 'nullable|string',
        ];
    }

    protected $messages = [
        'title.required' => 'Tittel er påkrevd.',
        'contact_id.required' => 'Kunde er påkrevd.',
        'due_date.after_or_equal' => 'Forfallsdato må være etter eller lik fakturadato.',
        'line_description.required' => 'Beskrivelse er påkrevd.',
        'line_quantity.required' => 'Antall er påkrevd.',
        'line_unit_price.required' => 'Pris er påkrevd.',
        'payment_method_id.required' => 'Betalingsmåte er påkrevd.',
        'payment_date.required' => 'Betalingsdato er påkrevd.',
        'payment_amount.required' => 'Beløp er påkrevd.',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedFilterContact(): void
    {
        $this->resetPage();
    }

    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    public function updatedContactId($value): void
    {
        if ($value) {
            $contact = Contact::find($value);
            if ($contact) {
                $this->customer_name = $contact->company_name;
                $this->customer_address = $contact->address;
                $this->customer_postal_code = $contact->postal_code;
                $this->customer_city = $contact->city;
                $this->customer_country = $contact->country ?? 'Norge';
                $this->payment_terms_days = $contact->payment_terms_days ?? 30;
                $this->updateDueDate();
            }
        }
    }

    public function updatedPaymentTermsDays(): void
    {
        $this->updateDueDate();
    }

    private function updateDueDate(): void
    {
        if ($this->invoice_date && $this->payment_terms_days) {
            $this->due_date = date('Y-m-d', strtotime($this->invoice_date.' +'.$this->payment_terms_days.' days'));
        }
    }

    public function updatedLineProductId($value): void
    {
        if ($value) {
            $product = Product::with('vatRate')->find($value);
            if ($product) {
                $this->line_description = $product->name;
                $this->line_unit_price = $product->price;
                $this->line_unit = $product->unit?->abbreviation ?? 'stk';
                if ($product->vatRate) {
                    $this->line_vat_rate_id = $product->vatRate->id;
                    $this->line_vat_percent = $product->vatRate->rate;
                }
            }
        }
    }

    public function updatedLineVatRateId($value): void
    {
        if ($value) {
            $vatRate = VatRate::find($value);
            if ($vatRate) {
                $this->line_vat_percent = $vatRate->rate;
            }
        }
    }

    public function openModal($id = null): void
    {
        $this->resetForm();

        if ($id) {
            $this->editingId = $id;
            $invoice = Invoice::with(['lines.product', 'payments'])->findOrFail($id);

            $this->invoice_type = $invoice->invoice_type;
            $this->title = $invoice->title;
            $this->description = $invoice->description;
            $this->contact_id = $invoice->contact_id ?? '';
            $this->project_id = $invoice->project_id ?? '';
            $this->invoice_status_id = $invoice->invoice_status_id ?? '';
            $this->invoice_date = $invoice->invoice_date?->format('Y-m-d') ?? '';
            $this->due_date = $invoice->due_date?->format('Y-m-d') ?? '';
            $this->payment_terms_days = $invoice->payment_terms_days ?? 30;
            $this->reminder_days = $invoice->reminder_days ?? 14;
            $this->terms_conditions = $invoice->terms_conditions;
            $this->internal_notes = $invoice->internal_notes;
            $this->customer_name = $invoice->customer_name;
            $this->customer_address = $invoice->customer_address;
            $this->customer_postal_code = $invoice->customer_postal_code;
            $this->customer_city = $invoice->customer_city;
            $this->customer_country = $invoice->customer_country ?? 'Norge';
            $this->our_reference = $invoice->our_reference;
            $this->customer_reference = $invoice->customer_reference;
            $this->is_active = $invoice->is_active;
            $this->currentInvoiceId = $id;
            $this->loadInvoiceLines();
            $this->loadInvoicePayments();
        } else {
            $this->invoice_date = date('Y-m-d');
            $this->due_date = date('Y-m-d', strtotime('+30 days'));
            $defaultStatus = InvoiceStatus::where('code', 'draft')->first();
            if ($defaultStatus) {
                $this->invoice_status_id = $defaultStatus->id;
            }
        }

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $this->validate();

        $reminderDate = null;
        if ($this->due_date && $this->reminder_days) {
            $reminderDate = date('Y-m-d', strtotime($this->due_date.' +'.$this->reminder_days.' days'));
        }

        $data = [
            'invoice_type' => $this->invoice_type,
            'title' => $this->title,
            'description' => $this->description,
            'contact_id' => $this->contact_id ?: null,
            'project_id' => $this->project_id ?: null,
            'invoice_status_id' => $this->invoice_status_id ?: null,
            'invoice_date' => $this->invoice_date ?: null,
            'due_date' => $this->due_date ?: null,
            'payment_terms_days' => $this->payment_terms_days,
            'reminder_days' => $this->reminder_days,
            'reminder_date' => $reminderDate,
            'terms_conditions' => $this->terms_conditions,
            'internal_notes' => $this->internal_notes,
            'customer_name' => $this->customer_name,
            'customer_address' => $this->customer_address,
            'customer_postal_code' => $this->customer_postal_code,
            'customer_city' => $this->customer_city,
            'customer_country' => $this->customer_country,
            'our_reference' => $this->our_reference,
            'customer_reference' => $this->customer_reference,
            'is_active' => $this->is_active,
            'created_by' => $this->editingId ? Invoice::find($this->editingId)->created_by : auth()->id(),
        ];

        if ($this->editingId) {
            $invoice = Invoice::findOrFail($this->editingId);
            $invoice->update($data);
            $this->dispatch('toast', message: 'Fakturaen ble oppdatert', variant: 'success');
            $this->closeModal();
        } else {
            $invoice = Invoice::create($data);
            $this->editingId = $invoice->id;
            $this->currentInvoiceId = $invoice->id;
            $this->loadInvoiceLines();
            $this->dispatch('toast', message: 'Fakturaen ble opprettet. Du kan nå legge til linjer.', variant: 'success');
            // Don't close modal - allow adding lines
        }
    }

    public function delete($id): void
    {
        Invoice::findOrFail($id)->delete();
        session()->flash('success', 'Fakturaen ble slettet.');
    }

    public function createCreditNote($id): void
    {
        $invoice = Invoice::findOrFail($id);

        if ($invoice->is_credit_note) {
            session()->flash('error', 'Kan ikke lage kreditnota av en kreditnota.');

            return;
        }

        $creditNote = $invoice->createCreditNote();
        session()->flash('success', 'Kreditnota '.$creditNote->invoice_number.' ble opprettet.');
    }

    public function markAsSent($id): void
    {
        $invoice = Invoice::findOrFail($id);
        $sentStatus = InvoiceStatus::where('code', 'sent')->first();

        if ($sentStatus) {
            $invoice->update([
                'invoice_status_id' => $sentStatus->id,
                'sent_at' => now(),
            ]);
            session()->flash('success', 'Fakturaen ble merket som sendt.');
        }
    }

    // Invoice Lines Management
    public function openLineModal($lineId = null): void
    {
        $this->resetLineForm();

        if ($lineId) {
            $this->editingLineId = $lineId;
            $line = InvoiceLine::findOrFail($lineId);

            $this->line_product_id = $line->product_id ?? '';
            $this->line_description = $line->description;
            $this->line_quantity = $line->quantity;
            $this->line_unit = $line->unit;
            $this->line_unit_price = $line->unit_price;
            $this->line_discount_percent = $line->discount_percent;
            $this->line_vat_rate_id = $line->vat_rate_id ?? '';
            $this->line_vat_percent = $line->vat_percent;
        } else {
            $defaultVatRate = VatRate::where('is_default', true)->first() ?? VatRate::first();
            if ($defaultVatRate) {
                $this->line_vat_rate_id = $defaultVatRate->id;
                $this->line_vat_percent = $defaultVatRate->rate;
            }
        }

        $this->showLineModal = true;
    }

    public function closeLineModal(): void
    {
        $this->showLineModal = false;
        $this->resetLineForm();
    }

    public function saveLine(): void
    {
        $this->validate($this->lineRules());

        $data = [
            'invoice_id' => $this->currentInvoiceId,
            'product_id' => $this->line_product_id ?: null,
            'description' => $this->line_description,
            'quantity' => $this->line_quantity,
            'unit' => $this->line_unit,
            'unit_price' => $this->line_unit_price,
            'discount_percent' => $this->line_discount_percent ?? 0,
            'vat_rate_id' => $this->line_vat_rate_id ?: null,
            'vat_percent' => $this->line_vat_percent,
            'sort_order' => $this->editingLineId
                ? InvoiceLine::find($this->editingLineId)->sort_order
                : InvoiceLine::where('invoice_id', $this->currentInvoiceId)->count(),
        ];

        if ($this->editingLineId) {
            InvoiceLine::findOrFail($this->editingLineId)->update($data);
        } else {
            InvoiceLine::create($data);
        }

        $this->loadInvoiceLines();
        $this->closeLineModal();
    }

    public function deleteLine($lineId): void
    {
        InvoiceLine::findOrFail($lineId)->delete();
        $this->loadInvoiceLines();
    }

    // Payment Management
    public function openPaymentModal($paymentId = null): void
    {
        $this->resetPaymentForm();

        if ($paymentId) {
            $this->editingPaymentId = $paymentId;
            $payment = InvoicePayment::findOrFail($paymentId);

            $this->payment_method_id = $payment->payment_method_id ?? '';
            $this->payment_date = $payment->payment_date?->format('Y-m-d') ?? '';
            $this->payment_amount = $payment->amount;
            $this->payment_reference = $payment->reference;
            $this->payment_notes = $payment->notes;
        } else {
            $this->payment_date = date('Y-m-d');
            $invoice = Invoice::find($this->currentInvoiceId);
            if ($invoice) {
                $this->payment_amount = $invoice->balance;
            }
        }

        $this->showPaymentModal = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->resetPaymentForm();
    }

    public function savePayment(): void
    {
        $this->validate($this->paymentRules());

        $data = [
            'invoice_id' => $this->currentInvoiceId,
            'payment_method_id' => $this->payment_method_id,
            'payment_date' => $this->payment_date,
            'amount' => $this->payment_amount,
            'reference' => $this->payment_reference,
            'notes' => $this->payment_notes,
            'created_by' => auth()->id(),
        ];

        if ($this->editingPaymentId) {
            InvoicePayment::findOrFail($this->editingPaymentId)->update($data);
        } else {
            InvoicePayment::create($data);
        }

        $this->loadInvoicePayments();
        $this->closePaymentModal();
    }

    public function deletePayment($paymentId): void
    {
        InvoicePayment::findOrFail($paymentId)->delete();
        $this->loadInvoicePayments();
    }

    private function loadInvoiceLines(): void
    {
        if ($this->currentInvoiceId) {
            $this->invoiceLines = InvoiceLine::with(['product', 'vatRate'])
                ->where('invoice_id', $this->currentInvoiceId)
                ->ordered()
                ->get()
                ->toArray();
        } else {
            $this->invoiceLines = [];
        }
    }

    private function loadInvoicePayments(): void
    {
        if ($this->currentInvoiceId) {
            $this->invoicePayments = InvoicePayment::with(['paymentMethod', 'creator'])
                ->where('invoice_id', $this->currentInvoiceId)
                ->ordered()
                ->get()
                ->toArray();
        } else {
            $this->invoicePayments = [];
        }
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->invoice_type = 'invoice';
        $this->title = '';
        $this->description = '';
        $this->contact_id = '';
        $this->project_id = '';
        $this->invoice_status_id = '';
        $this->invoice_date = '';
        $this->due_date = '';
        $this->payment_terms_days = 30;
        $this->reminder_days = 14;
        $this->terms_conditions = '';
        $this->internal_notes = '';
        $this->customer_name = '';
        $this->customer_address = '';
        $this->customer_postal_code = '';
        $this->customer_city = '';
        $this->customer_country = 'Norge';
        $this->our_reference = '';
        $this->customer_reference = '';
        $this->is_active = true;
        $this->currentInvoiceId = null;
        $this->invoiceLines = [];
        $this->invoicePayments = [];
        $this->resetValidation();
    }

    private function resetLineForm(): void
    {
        $this->editingLineId = null;
        $this->line_product_id = '';
        $this->line_description = '';
        $this->line_quantity = 1;
        $this->line_unit = 'stk';
        $this->line_unit_price = '';
        $this->line_discount_percent = 0;
        $this->line_vat_rate_id = '';
        $this->line_vat_percent = 25;
    }

    private function resetPaymentForm(): void
    {
        $this->editingPaymentId = null;
        $this->payment_method_id = '';
        $this->payment_date = '';
        $this->payment_amount = '';
        $this->payment_reference = '';
        $this->payment_notes = '';
    }

    public function getStatusColorClass($color): string
    {
        return match ($color) {
            'blue' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
            'yellow' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
            'green' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
            'red' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
            'purple' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
            'zinc' => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300',
            default => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300',
        };
    }

    public function render()
    {
        $query = Invoice::with([
            'contact',
            'project',
            'invoiceStatus',
            'creator',
            'lines',
            'payments',
        ])->ordered();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('invoice_number', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%')
                    ->orWhere('customer_name', 'like', '%'.$this->search.'%')
                    ->orWhere('customer_reference', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->filterStatus) {
            $query->where('invoice_status_id', $this->filterStatus);
        }

        if ($this->filterContact) {
            $query->where('contact_id', $this->filterContact);
        }

        if ($this->filterType === 'invoices') {
            $query->invoices();
        } elseif ($this->filterType === 'credit_notes') {
            $query->creditNotes();
        }

        return view('livewire.invoice-manager', [
            'invoices' => $query->paginate(15),
            'statuses' => InvoiceStatus::active()->ordered()->get(),
            'contacts' => Contact::active()->ordered()->get(),
            'projects' => Project::active()->ordered()->get(),
            'products' => Product::active()->ordered()->get(),
            'vatRates' => VatRate::active()->ordered()->get(),
            'paymentMethods' => PaymentMethod::active()->ordered()->get(),
        ]);
    }
}
