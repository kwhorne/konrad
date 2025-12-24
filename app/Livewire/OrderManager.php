<?php

namespace App\Livewire;

use App\Models\Contact;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\Project;
use App\Models\VatRate;
use Livewire\Component;
use Livewire\WithPagination;

class OrderManager extends Component
{
    use WithPagination;

    public $showModal = false;

    public $showLineModal = false;

    public $editingId = null;

    public $search = '';

    public $filterStatus = '';

    public $filterContact = '';

    // Order form fields
    public $title = '';

    public $description = '';

    public $contact_id = '';

    public $project_id = '';

    public $order_status_id = '';

    public $order_date = '';

    public $delivery_date = '';

    public $customer_reference = '';

    public $payment_terms_days = 30;

    public $terms_conditions = '';

    public $internal_notes = '';

    public $customer_name = '';

    public $customer_address = '';

    public $customer_postal_code = '';

    public $customer_city = '';

    public $customer_country = 'Norge';

    public $delivery_address = '';

    public $delivery_postal_code = '';

    public $delivery_city = '';

    public $delivery_country = 'Norge';

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

    // Currently editing order for lines
    public $currentOrderId = null;

    public $orderLines = [];

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'contact_id' => 'required|exists:contacts,id',
            'project_id' => 'nullable|exists:projects,id',
            'order_status_id' => 'nullable|exists:order_statuses,id',
            'order_date' => 'nullable|date',
            'delivery_date' => 'nullable|date|after_or_equal:order_date',
            'customer_reference' => 'nullable|string|max:100',
            'payment_terms_days' => 'nullable|integer|min:0',
            'terms_conditions' => 'nullable|string',
            'internal_notes' => 'nullable|string',
            'customer_name' => 'nullable|string|max:255',
            'customer_address' => 'nullable|string|max:255',
            'customer_postal_code' => 'nullable|string|max:20',
            'customer_city' => 'nullable|string|max:100',
            'customer_country' => 'nullable|string|max:100',
            'delivery_address' => 'nullable|string|max:255',
            'delivery_postal_code' => 'nullable|string|max:20',
            'delivery_city' => 'nullable|string|max:100',
            'delivery_country' => 'nullable|string|max:100',
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
        'delivery_date.after_or_equal' => 'Leveringsdato må være etter eller lik ordredato.',
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
                $this->delivery_address = $contact->address;
                $this->delivery_postal_code = $contact->postal_code;
                $this->delivery_city = $contact->city;
                $this->delivery_country = $contact->country ?? 'Norge';
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
            $order = Order::with('lines.product')->findOrFail($id);

            $this->title = $order->title;
            $this->description = $order->description;
            $this->contact_id = $order->contact_id ?? '';
            $this->project_id = $order->project_id ?? '';
            $this->order_status_id = $order->order_status_id ?? '';
            $this->order_date = $order->order_date?->format('Y-m-d') ?? '';
            $this->delivery_date = $order->delivery_date?->format('Y-m-d') ?? '';
            $this->customer_reference = $order->customer_reference;
            $this->payment_terms_days = $order->payment_terms_days ?? 30;
            $this->terms_conditions = $order->terms_conditions;
            $this->internal_notes = $order->internal_notes;
            $this->customer_name = $order->customer_name;
            $this->customer_address = $order->customer_address;
            $this->customer_postal_code = $order->customer_postal_code;
            $this->customer_city = $order->customer_city;
            $this->customer_country = $order->customer_country ?? 'Norge';
            $this->delivery_address = $order->delivery_address;
            $this->delivery_postal_code = $order->delivery_postal_code;
            $this->delivery_city = $order->delivery_city;
            $this->delivery_country = $order->delivery_country ?? 'Norge';
            $this->is_active = $order->is_active;
            $this->currentOrderId = $id;
            $this->loadOrderLines();
        } else {
            $this->order_date = date('Y-m-d');
            $this->delivery_date = date('Y-m-d', strtotime('+14 days'));
            $defaultStatus = OrderStatus::where('code', 'draft')->first();
            if ($defaultStatus) {
                $this->order_status_id = $defaultStatus->id;
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
            'order_status_id' => $this->order_status_id ?: null,
            'order_date' => $this->order_date ?: null,
            'delivery_date' => $this->delivery_date ?: null,
            'customer_reference' => $this->customer_reference,
            'payment_terms_days' => $this->payment_terms_days,
            'terms_conditions' => $this->terms_conditions,
            'internal_notes' => $this->internal_notes,
            'customer_name' => $this->customer_name,
            'customer_address' => $this->customer_address,
            'customer_postal_code' => $this->customer_postal_code,
            'customer_city' => $this->customer_city,
            'customer_country' => $this->customer_country,
            'delivery_address' => $this->delivery_address,
            'delivery_postal_code' => $this->delivery_postal_code,
            'delivery_city' => $this->delivery_city,
            'delivery_country' => $this->delivery_country,
            'is_active' => $this->is_active,
            'created_by' => $this->editingId ? Order::find($this->editingId)->created_by : auth()->id(),
        ];

        if ($this->editingId) {
            $order = Order::findOrFail($this->editingId);
            $order->update($data);
            session()->flash('success', 'Ordren ble oppdatert.');
        } else {
            $order = Order::create($data);
            $this->editingId = $order->id;
            $this->currentOrderId = $order->id;
            session()->flash('success', 'Ordren ble opprettet.');
        }

        $this->closeModal();
    }

    public function delete($id): void
    {
        Order::findOrFail($id)->delete();
        session()->flash('success', 'Ordren ble slettet.');
    }

    public function convertToInvoice($id): void
    {
        $order = Order::findOrFail($id);

        if (! $order->can_convert) {
            session()->flash('error', 'Ordren kan ikke konverteres til faktura.');

            return;
        }

        $invoice = $order->convertToInvoice();
        session()->flash('success', 'Ordren ble konvertert til faktura '.$invoice->invoice_number.'.');
    }

    // Order Lines Management
    public function openLineModal($lineId = null): void
    {
        $this->resetLineForm();

        if ($lineId) {
            $this->editingLineId = $lineId;
            $line = OrderLine::findOrFail($lineId);

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
            'order_id' => $this->currentOrderId,
            'product_id' => $this->line_product_id ?: null,
            'description' => $this->line_description,
            'quantity' => $this->line_quantity,
            'unit' => $this->line_unit,
            'unit_price' => $this->line_unit_price,
            'discount_percent' => $this->line_discount_percent ?? 0,
            'vat_rate_id' => $this->line_vat_rate_id ?: null,
            'vat_percent' => $this->line_vat_percent,
            'sort_order' => $this->editingLineId
                ? OrderLine::find($this->editingLineId)->sort_order
                : OrderLine::where('order_id', $this->currentOrderId)->count(),
        ];

        if ($this->editingLineId) {
            OrderLine::findOrFail($this->editingLineId)->update($data);
        } else {
            OrderLine::create($data);
        }

        $this->loadOrderLines();
        $this->closeLineModal();
    }

    public function deleteLine($lineId): void
    {
        OrderLine::findOrFail($lineId)->delete();
        $this->loadOrderLines();
    }

    private function loadOrderLines(): void
    {
        if ($this->currentOrderId) {
            $this->orderLines = OrderLine::with(['product', 'vatRate'])
                ->where('order_id', $this->currentOrderId)
                ->ordered()
                ->get()
                ->toArray();
        } else {
            $this->orderLines = [];
        }
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->title = '';
        $this->description = '';
        $this->contact_id = '';
        $this->project_id = '';
        $this->order_status_id = '';
        $this->order_date = '';
        $this->delivery_date = '';
        $this->customer_reference = '';
        $this->payment_terms_days = 30;
        $this->terms_conditions = '';
        $this->internal_notes = '';
        $this->customer_name = '';
        $this->customer_address = '';
        $this->customer_postal_code = '';
        $this->customer_city = '';
        $this->customer_country = 'Norge';
        $this->delivery_address = '';
        $this->delivery_postal_code = '';
        $this->delivery_city = '';
        $this->delivery_country = 'Norge';
        $this->is_active = true;
        $this->currentOrderId = null;
        $this->orderLines = [];
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
        $query = Order::with([
            'contact',
            'project',
            'orderStatus',
            'creator',
            'lines',
        ])->ordered();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('order_number', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%')
                    ->orWhere('customer_name', 'like', '%'.$this->search.'%')
                    ->orWhere('customer_reference', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->filterStatus) {
            $query->where('order_status_id', $this->filterStatus);
        }

        if ($this->filterContact) {
            $query->where('contact_id', $this->filterContact);
        }

        return view('livewire.order-manager', [
            'orders' => $query->paginate(15),
            'statuses' => OrderStatus::active()->ordered()->get(),
            'contacts' => Contact::active()->ordered()->get(),
            'projects' => Project::active()->ordered()->get(),
            'products' => Product::active()->ordered()->get(),
            'vatRates' => VatRate::active()->ordered()->get(),
        ]);
    }
}
