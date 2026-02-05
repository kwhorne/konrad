<?php

namespace App\Livewire;

use App\Livewire\Traits\HasStatusColorMapping;
use App\Models\Contact;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\Project;
use App\Models\VatRate;
use App\Rules\ExistsInCompany;
use App\Services\ContactFormPopulator;
use App\Services\DocumentConversionService;
use App\Services\DocumentLineService;
use Flux\Flux;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class OrderManager extends Component
{
    use AuthorizesRequests, HasStatusColorMapping, WithPagination;

    // Modal states
    public $showModal = false;

    public $showLineModal = false;

    // Filter states
    public $search = '';

    public $filterStatus = '';

    public $filterContact = '';

    // Order form fields
    public $editingId = null;

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

    // Current context
    public $currentOrderId = null;

    public $orderLines = [];

    protected $messages = [
        'title.required' => 'Tittel er påkrevd.',
        'contact_id.required' => 'Kunde er påkrevd.',
        'delivery_date.after_or_equal' => 'Leveringsdato må være etter eller lik ordredato.',
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
                $this->applyContactFields($populator->populateForOrder($contact));
                $this->order_date = now()->format('Y-m-d');
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
            'order_status_id' => ['nullable', new ExistsInCompany('order_statuses')],
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
            $this->applyContactFields($populator->populateForOrder($contact));
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

    // Order CRUD
    public function openModal($id = null): void
    {
        if ($id) {
            $order = Order::findOrFail($id);
            $this->authorize('view', $order);
        } else {
            $this->authorize('create', Order::class);
        }

        $this->resetForm();

        if ($id) {
            $this->loadOrder($id);
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

        if ($this->editingId) {
            $order = Order::findOrFail($this->editingId);
            $this->authorize('update', $order);
        } else {
            $this->authorize('create', Order::class);
        }

        $data = $this->getOrderData();

        if ($this->editingId) {
            $order->update($data);
            Flux::toast(text: 'Ordren ble oppdatert', variant: 'success');
            $this->closeModal();
        } else {
            $order = Order::create($data);
            $this->editingId = $order->id;
            $this->currentOrderId = $order->id;
            $this->loadOrderLines();
            Flux::toast(text: 'Ordren ble opprettet. Du kan nå legge til linjer.', variant: 'success');
        }
    }

    public function delete($id): void
    {
        $order = Order::findOrFail($id);
        $this->authorize('delete', $order);

        $order->delete();
        session()->flash('success', 'Ordren ble slettet.');
    }

    public function convertToInvoice($id, DocumentConversionService $conversionService): void
    {
        $order = Order::findOrFail($id);
        $this->authorize('convertToInvoice', $order);

        try {
            $invoice = $conversionService->convertOrderToInvoice($order);
            session()->flash('success', 'Ordren ble konvertert til faktura '.$invoice->invoice_number.'.');
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

        $order = Order::findOrFail($this->currentOrderId);
        $this->authorize('update', $order);

        $lineService->saveLine($order, $this->getLineData(), $this->editingLineId);

        $this->loadOrderLines();
        $this->closeLineModal();
    }

    public function deleteLine($lineId, DocumentLineService $lineService): void
    {
        $line = OrderLine::findOrFail($lineId);
        $this->authorize('update', $line->order);

        $lineService->deleteLine($line);
        $this->loadOrderLines();
    }

    public function render()
    {
        return view('livewire.order-manager', [
            'orders' => $this->getOrdersQuery()->paginate(15),
            'statuses' => OrderStatus::active()->ordered()->get(),
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

    private function loadOrder(int $id): void
    {
        $order = Order::with('lines.product')->findOrFail($id);

        $this->editingId = $id;
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
    }

    private function loadLine(int $lineId): void
    {
        $line = OrderLine::findOrFail($lineId);
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

    private function loadOrderLines(): void
    {
        $this->orderLines = $this->currentOrderId
            ? OrderLine::with(['product', 'vatRate'])->where('order_id', $this->currentOrderId)->ordered()->get()->toArray()
            : [];
    }

    private function getOrderData(): array
    {
        return [
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

    private function getOrdersQuery()
    {
        $query = Order::with(['contact', 'project', 'orderStatus', 'creator', 'lines'])->ordered();

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

        return $query;
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
}
