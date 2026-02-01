<?php

namespace App\Livewire;

use App\Livewire\Traits\HasStatusColorMapping;
use App\Models\Contact;
use App\Models\Product;
use App\Models\Project;
use App\Models\Quote;
use App\Models\QuoteLine;
use App\Models\QuoteStatus;
use App\Models\VatRate;
use App\Rules\ExistsInCompany;
use App\Services\ContactFormPopulator;
use App\Services\DocumentConversionService;
use App\Services\DocumentLineService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class QuoteManager extends Component
{
    use AuthorizesRequests, HasStatusColorMapping, WithPagination;

    // Modal states
    public $showModal = false;

    public $showLineModal = false;

    // Filter states
    public $search = '';

    public $filterStatus = '';

    public $filterContact = '';

    // Quote form fields
    public $editingId = null;

    public $title = '';

    public $description = '';

    public $contact_id = '';

    public $project_id = '';

    public $quote_status_id = '';

    public $quote_date = '';

    public $valid_until = '';

    public $payment_terms_days = 30;

    public $terms_conditions = '';

    public $internal_notes = '';

    public $customer_name = '';

    public $customer_address = '';

    public $customer_postal_code = '';

    public $customer_city = '';

    public $customer_country = 'Norge';

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

    // Current context
    public $currentQuoteId = null;

    public $quoteLines = [];

    protected $messages = [
        'title.required' => 'Tittel er påkrevd.',
        'contact_id.required' => 'Kunde er påkrevd.',
        'valid_until.after_or_equal' => 'Gyldig til må være etter eller lik tilbudsdato.',
        'line_description.required' => 'Beskrivelse er påkrevd.',
        'line_quantity.required' => 'Antall er påkrevd.',
        'line_unit_price.required' => 'Pris er påkrevd.',
    ];

    public function mount(ContactFormPopulator $populator): void
    {
        if (request()->has('contact_id')) {
            $contact = Contact::find(request()->get('contact_id'));
            if ($contact) {
                $this->contact_id = $contact->id;
                $this->applyContactFields($populator->populateForQuote($contact));
                $this->quote_date = now()->format('Y-m-d');
                $this->valid_until = now()->addDays(30)->format('Y-m-d');
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
            'quote_status_id' => ['nullable', new ExistsInCompany('quote_statuses')],
            'quote_date' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:quote_date',
            'payment_terms_days' => 'nullable|integer|min:0',
            'terms_conditions' => 'nullable|string',
            'internal_notes' => 'nullable|string',
            'customer_name' => 'nullable|string|max:255',
            'customer_address' => 'nullable|string|max:255',
            'customer_postal_code' => 'nullable|string|max:20',
            'customer_city' => 'nullable|string|max:100',
            'customer_country' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ];
    }

    protected function lineRules(): array
    {
        return [
            'line_product_id' => ['nullable', new ExistsInCompany('products')],
            'line_description' => 'required|string',
            'line_quantity' => 'required|numeric|min:0.01',
            'line_unit' => 'required|string|max:20',
            'line_unit_price' => 'required|numeric|min:0',
            'line_discount_percent' => 'nullable|numeric|min:0|max:100',
            'line_vat_rate_id' => ['nullable', new ExistsInCompany('vat_rates')],
            'line_vat_percent' => 'required|numeric|min:0|max:100',
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

    public function updatedContactId($value, ContactFormPopulator $populator): void
    {
        if ($value && $contact = Contact::find($value)) {
            $this->applyContactFields($populator->populateForQuote($contact));
        }
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

    // Quote CRUD
    public function openModal($id = null): void
    {
        if ($id) {
            $quote = Quote::findOrFail($id);
            $this->authorize('view', $quote);
        } else {
            $this->authorize('create', Quote::class);
        }

        $this->resetForm();

        if ($id) {
            $this->loadQuote($id);
        } else {
            $this->quote_date = date('Y-m-d');
            $this->valid_until = date('Y-m-d', strtotime('+30 days'));
            $defaultStatus = QuoteStatus::where('code', 'draft')->first();
            if ($defaultStatus) {
                $this->quote_status_id = $defaultStatus->id;
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

        if ($this->editingId) {
            $quote = Quote::findOrFail($this->editingId);
            $this->authorize('update', $quote);
        } else {
            $this->authorize('create', Quote::class);
        }

        $data = $this->getQuoteData();

        if ($this->editingId) {
            $quote->update($data);
            $this->dispatch('toast', message: 'Tilbudet ble oppdatert', variant: 'success');
            $this->closeModal();
        } else {
            $quote = Quote::create($data);
            $this->editingId = $quote->id;
            $this->currentQuoteId = $quote->id;
            $this->loadQuoteLines();
            $this->dispatch('toast', message: 'Tilbudet ble opprettet. Du kan nå legge til linjer.', variant: 'success');
        }
    }

    public function delete($id): void
    {
        $quote = Quote::findOrFail($id);
        $this->authorize('delete', $quote);

        $quote->delete();
        session()->flash('success', 'Tilbudet ble slettet.');
    }

    public function convertToOrder($id, DocumentConversionService $conversionService): void
    {
        $quote = Quote::findOrFail($id);
        $this->authorize('convertToOrder', $quote);

        try {
            $order = $conversionService->convertQuoteToOrder($quote);
            session()->flash('success', 'Tilbudet ble konvertert til ordre '.$order->order_number.'.');
        } catch (\InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
        }
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

        $quote = Quote::findOrFail($this->currentQuoteId);
        $this->authorize('update', $quote);

        $lineService->saveLine($quote, $this->getLineData(), $this->editingLineId);

        $this->loadQuoteLines();
        $this->closeLineModal();
    }

    public function deleteLine($lineId, DocumentLineService $lineService): void
    {
        $line = QuoteLine::findOrFail($lineId);
        $this->authorize('update', $line->quote);

        $lineService->deleteLine($line);
        $this->loadQuoteLines();
    }

    public function render()
    {
        return view('livewire.quote-manager', [
            'quotes' => $this->getQuotesQuery()->paginate(15),
            'statuses' => QuoteStatus::active()->ordered()->get(),
            'contacts' => Contact::active()->ordered()->get(),
            'projects' => Project::active()->ordered()->get(),
            'products' => Product::active()->ordered()->get(),
            'vatRates' => VatRate::active()->ordered()->get(),
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

    private function loadQuote(int $id): void
    {
        $quote = Quote::with('lines.product')->findOrFail($id);

        $this->editingId = $id;
        $this->title = $quote->title;
        $this->description = $quote->description;
        $this->contact_id = $quote->contact_id ?? '';
        $this->project_id = $quote->project_id ?? '';
        $this->quote_status_id = $quote->quote_status_id ?? '';
        $this->quote_date = $quote->quote_date?->format('Y-m-d') ?? '';
        $this->valid_until = $quote->valid_until?->format('Y-m-d') ?? '';
        $this->payment_terms_days = $quote->payment_terms_days ?? 30;
        $this->terms_conditions = $quote->terms_conditions;
        $this->internal_notes = $quote->internal_notes;
        $this->customer_name = $quote->customer_name;
        $this->customer_address = $quote->customer_address;
        $this->customer_postal_code = $quote->customer_postal_code;
        $this->customer_city = $quote->customer_city;
        $this->customer_country = $quote->customer_country ?? 'Norge';
        $this->is_active = $quote->is_active;
        $this->currentQuoteId = $id;
        $this->loadQuoteLines();
    }

    private function loadLine(int $lineId): void
    {
        $line = QuoteLine::findOrFail($lineId);
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

    private function loadQuoteLines(): void
    {
        $this->quoteLines = $this->currentQuoteId
            ? QuoteLine::with(['product', 'vatRate'])->where('quote_id', $this->currentQuoteId)->ordered()->get()->toArray()
            : [];
    }

    private function getQuoteData(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'contact_id' => $this->contact_id ?: null,
            'project_id' => $this->project_id ?: null,
            'quote_status_id' => $this->quote_status_id ?: null,
            'quote_date' => $this->quote_date ?: null,
            'valid_until' => $this->valid_until ?: null,
            'payment_terms_days' => $this->payment_terms_days,
            'terms_conditions' => $this->terms_conditions,
            'internal_notes' => $this->internal_notes,
            'customer_name' => $this->customer_name,
            'customer_address' => $this->customer_address,
            'customer_postal_code' => $this->customer_postal_code,
            'customer_city' => $this->customer_city,
            'customer_country' => $this->customer_country,
            'is_active' => $this->is_active,
            'created_by' => $this->editingId ? Quote::find($this->editingId)->created_by : auth()->id(),
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

    private function getQuotesQuery()
    {
        $query = Quote::with(['contact', 'project', 'quoteStatus', 'creator', 'lines'])->ordered();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('quote_number', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%')
                    ->orWhere('customer_name', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->filterStatus) {
            $query->where('quote_status_id', $this->filterStatus);
        }

        if ($this->filterContact) {
            $query->where('contact_id', $this->filterContact);
        }

        return $query;
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->title = '';
        $this->description = '';
        $this->contact_id = '';
        $this->project_id = '';
        $this->quote_status_id = '';
        $this->quote_date = '';
        $this->valid_until = '';
        $this->payment_terms_days = 30;
        $this->terms_conditions = '';
        $this->internal_notes = '';
        $this->customer_name = '';
        $this->customer_address = '';
        $this->customer_postal_code = '';
        $this->customer_city = '';
        $this->customer_country = 'Norge';
        $this->is_active = true;
        $this->currentQuoteId = null;
        $this->quoteLines = [];
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
}
