<?php

namespace App\Livewire;

use App\Models\Contact;
use App\Models\Product;
use App\Models\Project;
use App\Models\Quote;
use App\Models\QuoteLine;
use App\Models\QuoteStatus;
use App\Models\VatRate;
use Livewire\Component;
use Livewire\WithPagination;

class QuoteManager extends Component
{
    use WithPagination;

    public $showModal = false;

    public $showLineModal = false;

    public $editingId = null;

    public $search = '';

    public $filterStatus = '';

    public $filterContact = '';

    // Quote form fields
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

    // Currently editing quote for lines
    public $currentQuoteId = null;

    public $quoteLines = [];

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'contact_id' => 'required|exists:contacts,id',
            'project_id' => 'nullable|exists:projects,id',
            'quote_status_id' => 'nullable|exists:quote_statuses,id',
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
            'line_product_id' => 'nullable|exists:products,id',
            'line_description' => 'required|string',
            'line_quantity' => 'required|numeric|min:0.01',
            'line_unit' => 'required|string|max:20',
            'line_unit_price' => 'required|numeric|min:0',
            'line_discount_percent' => 'nullable|numeric|min:0|max:100',
            'line_vat_rate_id' => 'nullable|exists:vat_rates,id',
            'line_vat_percent' => 'required|numeric|min:0|max:100',
        ];
    }

    protected $messages = [
        'title.required' => 'Tittel er påkrevd.',
        'contact_id.required' => 'Kunde er påkrevd.',
        'valid_until.after_or_equal' => 'Gyldig til må være etter eller lik tilbudsdato.',
        'line_description.required' => 'Beskrivelse er påkrevd.',
        'line_quantity.required' => 'Antall er påkrevd.',
        'line_unit_price.required' => 'Pris er påkrevd.',
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
            }
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
            $quote = Quote::with('lines.product')->findOrFail($id);

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

        $data = [
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

        if ($this->editingId) {
            $quote = Quote::findOrFail($this->editingId);
            $quote->update($data);
            session()->flash('success', 'Tilbudet ble oppdatert.');
        } else {
            $quote = Quote::create($data);
            $this->editingId = $quote->id;
            $this->currentQuoteId = $quote->id;
            session()->flash('success', 'Tilbudet ble opprettet.');
        }

        $this->closeModal();
    }

    public function delete($id): void
    {
        Quote::findOrFail($id)->delete();
        session()->flash('success', 'Tilbudet ble slettet.');
    }

    public function convertToOrder($id): void
    {
        $quote = Quote::findOrFail($id);

        if (! $quote->can_convert) {
            session()->flash('error', 'Tilbudet kan ikke konverteres til ordre.');

            return;
        }

        $order = $quote->convertToOrder();
        session()->flash('success', 'Tilbudet ble konvertert til ordre '.$order->order_number.'.');
    }

    // Quote Lines Management
    public function openLineModal($lineId = null): void
    {
        $this->resetLineForm();

        if ($lineId) {
            $this->editingLineId = $lineId;
            $line = QuoteLine::findOrFail($lineId);

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
            'quote_id' => $this->currentQuoteId,
            'product_id' => $this->line_product_id ?: null,
            'description' => $this->line_description,
            'quantity' => $this->line_quantity,
            'unit' => $this->line_unit,
            'unit_price' => $this->line_unit_price,
            'discount_percent' => $this->line_discount_percent ?? 0,
            'vat_rate_id' => $this->line_vat_rate_id ?: null,
            'vat_percent' => $this->line_vat_percent,
            'sort_order' => $this->editingLineId
                ? QuoteLine::find($this->editingLineId)->sort_order
                : QuoteLine::where('quote_id', $this->currentQuoteId)->count(),
        ];

        if ($this->editingLineId) {
            QuoteLine::findOrFail($this->editingLineId)->update($data);
        } else {
            QuoteLine::create($data);
        }

        $this->loadQuoteLines();
        $this->closeLineModal();
    }

    public function deleteLine($lineId): void
    {
        QuoteLine::findOrFail($lineId)->delete();
        $this->loadQuoteLines();
    }

    private function loadQuoteLines(): void
    {
        if ($this->currentQuoteId) {
            $this->quoteLines = QuoteLine::with(['product', 'vatRate'])
                ->where('quote_id', $this->currentQuoteId)
                ->ordered()
                ->get()
                ->toArray();
        } else {
            $this->quoteLines = [];
        }
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

    public function getStatusColorClass($color): string
    {
        return match ($color) {
            'blue' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
            'green' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
            'red' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
            'amber' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
            'purple' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
            'zinc' => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300',
            default => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300',
        };
    }

    public function render()
    {
        $query = Quote::with([
            'contact',
            'project',
            'quoteStatus',
            'creator',
            'lines',
        ])->ordered();

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

        return view('livewire.quote-manager', [
            'quotes' => $query->paginate(15),
            'statuses' => QuoteStatus::active()->ordered()->get(),
            'contacts' => Contact::active()->ordered()->get(),
            'projects' => Project::active()->ordered()->get(),
            'products' => Product::active()->ordered()->get(),
            'vatRates' => VatRate::active()->ordered()->get(),
        ]);
    }
}
