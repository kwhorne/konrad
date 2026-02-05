<?php

namespace App\Livewire;

use App\Models\Contact;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Models\StockLocation;
use App\Models\VatRate;
use App\Rules\ExistsInCompany;
use App\Services\PurchaseOrderService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class PurchaseOrderForm extends Component
{
    use AuthorizesRequests;

    public ?PurchaseOrder $purchaseOrder = null;

    // Form fields
    public $contact_id = '';

    public $stock_location_id = '';

    public $order_date = '';

    public $expected_date = '';

    public $supplier_reference = '';

    public $shipping_address = '';

    public $notes = '';

    public $internal_notes = '';

    // Line modal
    public $showLineModal = false;

    public $editingLineId = null;

    public $line_product_id = '';

    public $line_description = '';

    public $line_quantity = 1;

    public $line_unit = 'stk';

    public $line_unit_price = '';

    public $line_discount_percent = 0;

    public $line_vat_rate_id = '';

    public $line_vat_percent = 25;

    public $lines = [];

    protected $messages = [
        'contact_id.required' => 'Leverandor er pakrevd.',
        'stock_location_id.required' => 'Mottakslokasjon er pakrevd.',
        'line_description.required' => 'Beskrivelse er pakrevd.',
        'line_quantity.required' => 'Antall er pakrevd.',
        'line_unit_price.required' => 'Pris er pakrevd.',
    ];

    public function mount(?int $purchaseOrderId = null): void
    {
        if ($purchaseOrderId) {
            $this->purchaseOrder = PurchaseOrder::with('lines.product', 'lines.vatRate')
                ->findOrFail($purchaseOrderId);
            $this->loadFromPurchaseOrder();
        } else {
            $this->order_date = now()->format('Y-m-d');
            $this->expected_date = now()->addDays(14)->format('Y-m-d');

            // Set default location
            $defaultLocation = StockLocation::where('company_id', auth()->user()->current_company_id)
                ->where('is_active', true)
                ->first();
            if ($defaultLocation) {
                $this->stock_location_id = $defaultLocation->id;
            }
        }
    }

    protected function rules(): array
    {
        return [
            'contact_id' => ['required', new ExistsInCompany('contacts')],
            'stock_location_id' => ['required', new ExistsInCompany('stock_locations')],
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:order_date',
            'supplier_reference' => 'nullable|string|max:100',
            'shipping_address' => 'nullable|string',
            'notes' => 'nullable|string',
            'internal_notes' => 'nullable|string',
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

    public function updatedLineProductId($value): void
    {
        if ($value && $product = Product::with('productType.vatRate')->find($value)) {
            $this->line_description = $product->name;
            $this->line_unit_price = $product->cost_price ?? $product->sales_price ?? 0;
            $this->line_unit = $product->unit ?? 'stk';

            if ($product->productType?->vatRate) {
                $this->line_vat_rate_id = $product->productType->vatRate->id;
                $this->line_vat_percent = $product->productType->vatRate->rate;
            }
        }
    }

    public function updatedLineVatRateId($value): void
    {
        if ($value && $vatRate = VatRate::find($value)) {
            $this->line_vat_percent = $vatRate->rate;
        }
    }

    public function save(PurchaseOrderService $service): void
    {
        $this->authorize('create', PurchaseOrder::class);
        $this->validate();

        $data = [
            'contact_id' => $this->contact_id,
            'stock_location_id' => $this->stock_location_id,
            'order_date' => $this->order_date,
            'expected_date' => $this->expected_date ?: null,
            'supplier_reference' => $this->supplier_reference,
            'shipping_address' => $this->shipping_address,
            'notes' => $this->notes,
            'internal_notes' => $this->internal_notes,
        ];

        if ($this->purchaseOrder) {
            $service->update($this->purchaseOrder, $data);
            $this->dispatch('toast', message: 'Innkjopsordre oppdatert', type: 'success');
        } else {
            $this->purchaseOrder = $service->create($data);
            $this->dispatch('toast', message: 'Innkjopsordre opprettet. Legg til linjer.', type: 'success');
        }

        $this->loadLines();
    }

    public function openLineModal(?int $lineId = null): void
    {
        $this->resetLineForm();

        if ($lineId) {
            $line = PurchaseOrderLine::findOrFail($lineId);
            $this->editingLineId = $lineId;
            $this->line_product_id = $line->product_id ?? '';
            $this->line_description = $line->description;
            $this->line_quantity = $line->quantity;
            $this->line_unit = $line->unit;
            $this->line_unit_price = $line->unit_price;
            $this->line_discount_percent = $line->discount_percent;
            $this->line_vat_rate_id = $line->vat_rate_id ?? '';
            $this->line_vat_percent = $line->vat_percent;
        } else {
            $defaultVatRate = VatRate::where('is_default', true)->first();
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

    public function saveLine(PurchaseOrderService $service): void
    {
        $this->validate($this->lineRules());

        if (! $this->purchaseOrder) {
            $this->dispatch('toast', message: 'Lagre innkjopsordren forst', type: 'error');

            return;
        }

        $lineData = [
            'product_id' => $this->line_product_id ?: null,
            'description' => $this->line_description,
            'quantity' => $this->line_quantity,
            'unit' => $this->line_unit,
            'unit_price' => $this->line_unit_price,
            'discount_percent' => $this->line_discount_percent ?? 0,
            'vat_rate_id' => $this->line_vat_rate_id ?: null,
            'vat_percent' => $this->line_vat_percent,
        ];

        if ($this->editingLineId) {
            $service->updateLine($this->purchaseOrder, $this->editingLineId, $lineData);
            $this->dispatch('toast', message: 'Linje oppdatert', type: 'success');
        } else {
            $service->addLine($this->purchaseOrder, $lineData);
            $this->dispatch('toast', message: 'Linje lagt til', type: 'success');
        }

        $this->loadLines();
        $this->purchaseOrder->refresh();
        $this->closeLineModal();
    }

    public function deleteLine(int $lineId, PurchaseOrderService $service): void
    {
        $service->removeLine($this->purchaseOrder, $lineId);
        $this->loadLines();
        $this->purchaseOrder->refresh();
        $this->dispatch('toast', message: 'Linje slettet', type: 'success');
    }

    private function loadFromPurchaseOrder(): void
    {
        $this->contact_id = $this->purchaseOrder->contact_id ?? '';
        $this->stock_location_id = $this->purchaseOrder->stock_location_id ?? '';
        $this->order_date = $this->purchaseOrder->order_date?->format('Y-m-d') ?? '';
        $this->expected_date = $this->purchaseOrder->expected_date?->format('Y-m-d') ?? '';
        $this->supplier_reference = $this->purchaseOrder->supplier_reference ?? '';
        $this->shipping_address = $this->purchaseOrder->shipping_address ?? '';
        $this->notes = $this->purchaseOrder->notes ?? '';
        $this->internal_notes = $this->purchaseOrder->internal_notes ?? '';
        $this->loadLines();
    }

    private function loadLines(): void
    {
        $this->lines = $this->purchaseOrder
            ? PurchaseOrderLine::with(['product', 'vatRate'])
                ->where('purchase_order_id', $this->purchaseOrder->id)
                ->orderBy('sort_order')
                ->get()
                ->toArray()
            : [];
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

    public function render()
    {
        $companyId = auth()->user()->current_company_id;

        return view('livewire.purchase-order-form', [
            'suppliers' => Contact::where('company_id', $companyId)
                ->where('type', 'supplier')
                ->where('is_active', true)
                ->orderBy('company_name')
                ->get(),
            'stockLocations' => StockLocation::where('company_id', $companyId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'products' => Product::where('company_id', $companyId)
                ->where('is_active', true)
                ->where('is_stocked', true)
                ->orderBy('name')
                ->get(),
            'vatRates' => VatRate::where('is_active', true)->orderBy('rate')->get(),
        ]);
    }
}
