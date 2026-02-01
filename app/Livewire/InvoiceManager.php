<?php

namespace App\Livewire;

use App\Livewire\Traits\HasStatusColorMapping;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\InvoicePayment;
use App\Models\InvoiceStatus;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Project;
use App\Models\VatRate;
use App\Rules\ExistsInCompany;
use App\Services\ContactFormPopulator;
use App\Services\DocumentLineService;
use App\Services\InvoiceService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class InvoiceManager extends Component
{
    use AuthorizesRequests, HasStatusColorMapping, WithPagination;

    // Modal states
    public $showModal = false;

    public $showLineModal = false;

    public $showPaymentModal = false;

    // Filter states
    public $search = '';

    public $filterStatus = '';

    public $filterContact = '';

    public $filterType = 'all';

    // Invoice form fields
    public $editingId = null;

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

    // Current context
    public $currentInvoiceId = null;

    public $invoiceLines = [];

    public $invoicePayments = [];

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

    public function mount(ContactFormPopulator $populator, InvoiceService $invoiceService): void
    {
        if (request()->has('contact_id')) {
            $contact = Contact::find(request()->get('contact_id'));
            if ($contact) {
                $this->contact_id = $contact->id;
                $this->applyContactFields($populator->populateForInvoice($contact));
                $this->invoice_date = now()->format('Y-m-d');
                $this->due_date = $invoiceService->calculateDueDateString($this->invoice_date, $this->payment_terms_days);
                $this->showModal = true;
            }
        }
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'contact_id' => ['required', new ExistsInCompany('contacts')],
            'project_id' => ['nullable', new ExistsInCompany('projects')],
            'invoice_status_id' => ['nullable', new ExistsInCompany('invoice_statuses')],
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
            'line_product_id' => ['nullable', new ExistsInCompany('products')],
            'line_description' => 'required|string',
            'line_quantity' => 'required|numeric',
            'line_unit' => 'required|string|max:20',
            'line_unit_price' => 'required|numeric|min:0',
            'line_discount_percent' => 'nullable|numeric|min:0|max:100',
            'line_vat_rate_id' => ['nullable', new ExistsInCompany('vat_rates')],
            'line_vat_percent' => 'required|numeric|min:0|max:100',
        ];
    }

    protected function paymentRules(): array
    {
        return [
            'payment_method_id' => ['required', new ExistsInCompany('payment_methods')],
            'payment_date' => 'required|date',
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_reference' => 'nullable|string|max:100',
            'payment_notes' => 'nullable|string',
        ];
    }

    // Filter updates
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

    public function updatedContactId($value, ContactFormPopulator $populator, InvoiceService $invoiceService): void
    {
        if ($value && $contact = Contact::find($value)) {
            $this->applyContactFields($populator->populateForInvoice($contact));
            $this->updateDueDate($invoiceService);
        }
    }

    public function updatedPaymentTermsDays(InvoiceService $invoiceService): void
    {
        $this->updateDueDate($invoiceService);
    }

    public function updatedLineProductId($value, DocumentLineService $lineService): void
    {
        if ($value && $product = Product::with('productType.vatRate')->find($value)) {
            $fields = $lineService->populateFromProduct($product);
            $this->line_description = $fields['description'];
            $this->line_unit_price = $fields['unit_price'];
            $this->line_unit = $fields['unit'];
            $this->line_vat_rate_id = $fields['vat_rate_id'] ?? '';
            $this->line_vat_percent = $fields['vat_percent'];
        }
    }

    public function updatedLineVatRateId($value): void
    {
        if ($value && $vatRate = VatRate::find($value)) {
            $this->line_vat_percent = $vatRate->rate;
        }
    }

    // Invoice CRUD
    public function openModal($id, InvoiceService $invoiceService): void
    {
        if ($id) {
            $invoice = Invoice::findOrFail($id);
            $this->authorize('view', $invoice);
        } else {
            $this->authorize('create', Invoice::class);
        }

        $this->resetForm();

        if ($id) {
            $this->loadInvoice($id);
        } else {
            $this->invoice_date = date('Y-m-d');
            $this->due_date = date('Y-m-d', strtotime('+30 days'));
            $defaultStatus = $invoiceService->getDefaultStatus();
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

    public function save(InvoiceService $invoiceService): void
    {
        $this->validate();

        if ($this->editingId) {
            $invoice = Invoice::findOrFail($this->editingId);
            $this->authorize('update', $invoice);
        } else {
            $this->authorize('create', Invoice::class);
        }

        $data = $this->getInvoiceData();
        $data['reminder_date'] = $invoiceService->prepareReminderDate($this->due_date, $this->reminder_days);

        if ($this->editingId) {
            Invoice::findOrFail($this->editingId)->update($data);
            $this->dispatch('toast', message: 'Fakturaen ble oppdatert', variant: 'success');
            $this->closeModal();
        } else {
            $invoice = Invoice::create($data);
            $this->editingId = $invoice->id;
            $this->currentInvoiceId = $invoice->id;
            $this->loadInvoiceLines();
            $this->dispatch('toast', message: 'Fakturaen ble opprettet. Du kan nå legge til linjer.', variant: 'success');
        }
    }

    public function delete($id): void
    {
        $invoice = Invoice::findOrFail($id);
        $this->authorize('delete', $invoice);

        $invoice->delete();
        session()->flash('success', 'Fakturaen ble slettet.');
    }

    public function createCreditNote($id, InvoiceService $invoiceService): void
    {
        $invoice = Invoice::findOrFail($id);
        $this->authorize('createCreditNote', $invoice);

        try {
            $creditNote = $invoiceService->createCreditNote($invoice);
            session()->flash('success', 'Kreditnota '.$creditNote->invoice_number.' ble opprettet.');
        } catch (\InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function markAsSent($id, InvoiceService $invoiceService): void
    {
        $invoice = Invoice::findOrFail($id);
        $this->authorize('markAsSent', $invoice);

        $invoiceService->markAsSent($invoice);
        session()->flash('success', 'Fakturaen ble merket som sendt.');
    }

    // Line management
    public function openLineModal($lineId, DocumentLineService $lineService): void
    {
        $this->resetLineForm();

        if ($lineId) {
            $this->loadLine($lineId);
        } else {
            $defaults = $lineService->getDefaultLineValues();
            $this->line_vat_rate_id = $defaults['vat_rate_id'] ?? '';
            $this->line_vat_percent = $defaults['vat_percent'];
        }

        $this->showLineModal = true;
    }

    public function closeLineModal(): void
    {
        $this->showLineModal = false;
        $this->resetLineForm();
    }

    public function saveLine(DocumentLineService $lineService): void
    {
        $this->validate($this->lineRules());

        $invoice = Invoice::findOrFail($this->currentInvoiceId);
        $this->authorize('update', $invoice);

        $lineService->saveLine($invoice, $this->getLineData(), $this->editingLineId);

        $this->loadInvoiceLines();
        $this->closeLineModal();
    }

    public function deleteLine($lineId, DocumentLineService $lineService): void
    {
        $line = InvoiceLine::findOrFail($lineId);
        $this->authorize('update', $line->invoice);

        $lineService->deleteLine($line);
        $this->loadInvoiceLines();
    }

    // Payment management
    public function openPaymentModal($paymentId = null): void
    {
        $this->resetPaymentForm();

        if ($paymentId) {
            $this->loadPayment($paymentId);
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

    public function savePayment(InvoiceService $invoiceService): void
    {
        $this->validate($this->paymentRules());

        $invoice = Invoice::findOrFail($this->currentInvoiceId);
        $this->authorize('recordPayment', $invoice);

        $data = $this->getPaymentData();

        if ($this->editingPaymentId) {
            $payment = InvoicePayment::findOrFail($this->editingPaymentId);
            $invoiceService->updatePayment($payment, $data);
        } else {
            $invoiceService->recordPayment($invoice, $data);
        }

        $this->loadInvoicePayments();
        $this->closePaymentModal();
    }

    public function deletePayment($paymentId, InvoiceService $invoiceService): void
    {
        $payment = InvoicePayment::findOrFail($paymentId);
        $this->authorize('recordPayment', $payment->invoice);

        $invoiceService->deletePayment($payment);
        $this->loadInvoicePayments();
    }

    public function render()
    {
        return view('livewire.invoice-manager', [
            'invoices' => $this->getInvoicesQuery()->paginate(15),
            'statuses' => InvoiceStatus::active()->ordered()->get(),
            'contacts' => Contact::active()->ordered()->get(),
            'projects' => Project::active()->ordered()->get(),
            'products' => Product::active()->ordered()->get(),
            'vatRates' => VatRate::active()->ordered()->get(),
            'paymentMethods' => PaymentMethod::active()->ordered()->get(),
        ]);
    }

    // Private helpers
    private function applyContactFields(array $fields): void
    {
        foreach ($fields as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    private function updateDueDate(InvoiceService $invoiceService): void
    {
        if ($this->invoice_date && $this->payment_terms_days) {
            $this->due_date = $invoiceService->calculateDueDateString($this->invoice_date, $this->payment_terms_days);
        }
    }

    private function loadInvoice(int $id): void
    {
        $invoice = Invoice::with(['lines.product', 'payments'])->findOrFail($id);

        $this->editingId = $id;
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
    }

    private function loadLine(int $lineId): void
    {
        $line = InvoiceLine::findOrFail($lineId);
        $this->editingLineId = $lineId;
        $this->line_product_id = $line->product_id ?? '';
        $this->line_description = $line->description;
        $this->line_quantity = $line->quantity;
        $this->line_unit = $line->unit;
        $this->line_unit_price = $line->unit_price;
        $this->line_discount_percent = $line->discount_percent;
        $this->line_vat_rate_id = $line->vat_rate_id ?? '';
        $this->line_vat_percent = $line->vat_percent;
    }

    private function loadPayment(int $paymentId): void
    {
        $payment = InvoicePayment::findOrFail($paymentId);
        $this->editingPaymentId = $paymentId;
        $this->payment_method_id = $payment->payment_method_id ?? '';
        $this->payment_date = $payment->payment_date?->format('Y-m-d') ?? '';
        $this->payment_amount = $payment->amount;
        $this->payment_reference = $payment->reference;
        $this->payment_notes = $payment->notes;
    }

    private function loadInvoiceLines(): void
    {
        $this->invoiceLines = $this->currentInvoiceId
            ? InvoiceLine::with(['product', 'vatRate'])->where('invoice_id', $this->currentInvoiceId)->ordered()->get()->toArray()
            : [];
    }

    private function loadInvoicePayments(): void
    {
        $this->invoicePayments = $this->currentInvoiceId
            ? InvoicePayment::with(['paymentMethod', 'creator'])->where('invoice_id', $this->currentInvoiceId)->ordered()->get()->toArray()
            : [];
    }

    private function getInvoiceData(): array
    {
        return [
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
    }

    private function getLineData(): array
    {
        return [
            'product_id' => $this->line_product_id ?: null,
            'description' => $this->line_description,
            'quantity' => $this->line_quantity,
            'unit' => $this->line_unit,
            'unit_price' => $this->line_unit_price,
            'discount_percent' => $this->line_discount_percent ?? 0,
            'vat_rate_id' => $this->line_vat_rate_id ?: null,
            'vat_percent' => $this->line_vat_percent,
        ];
    }

    private function getPaymentData(): array
    {
        return [
            'payment_method_id' => $this->payment_method_id,
            'payment_date' => $this->payment_date,
            'amount' => $this->payment_amount,
            'reference' => $this->payment_reference,
            'notes' => $this->payment_notes,
        ];
    }

    private function getInvoicesQuery()
    {
        $query = Invoice::with(['contact', 'project', 'invoiceStatus', 'creator', 'lines', 'payments'])->ordered();

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

        return $query;
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
}
