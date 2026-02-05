<?php

namespace App\Livewire;

use App\Models\Contact;
use App\Models\GoodsReceipt;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\StockLocation;
use App\Rules\ExistsInCompany;
use App\Services\GoodsReceiptService;
use Flux\Flux;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class GoodsReceiptForm extends Component
{
    use AuthorizesRequests;

    public ?PurchaseOrder $purchaseOrder = null;

    public ?GoodsReceipt $goodsReceipt = null;

    // Form fields
    public $contact_id = '';

    public $stock_location_id = '';

    public $receipt_date = '';

    public $supplier_delivery_note = '';

    public $notes = '';

    // Lines for PO-based receipt
    public $receiptLines = [];

    // Lines for standalone receipt
    public $manualLines = [];

    public $showLineModal = false;

    public $line_product_id = '';

    public $line_description = '';

    public $line_quantity = 1;

    public $line_unit_cost = '';

    public $editingLineIndex = null;

    protected $messages = [
        'contact_id.required' => 'Leverandor er pakrevd.',
        'stock_location_id.required' => 'Lagerlokasjon er pakrevd.',
        'receipt_date.required' => 'Mottaksdato er pakrevd.',
    ];

    public function mount(): void
    {
        $this->receipt_date = now()->format('Y-m-d');

        // Check if we're creating from a PO
        if (request()->has('po')) {
            $this->loadFromPurchaseOrder(request()->get('po'));
        } else {
            // Set default location for standalone receipt
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
            'receipt_date' => 'required|date',
            'supplier_delivery_note' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ];
    }

    public function loadFromPurchaseOrder(int $poId): void
    {
        $this->purchaseOrder = PurchaseOrder::with('lines.product')
            ->where('company_id', auth()->user()->current_company_id)
            ->findOrFail($poId);

        $this->contact_id = $this->purchaseOrder->contact_id;
        $this->stock_location_id = $this->purchaseOrder->stock_location_id;

        // Load outstanding lines
        $this->receiptLines = [];
        foreach ($this->purchaseOrder->lines as $line) {
            $outstanding = $line->quantity - $line->quantity_received;
            if ($outstanding > 0) {
                $this->receiptLines[] = [
                    'po_line_id' => $line->id,
                    'product_id' => $line->product_id,
                    'product_name' => $line->product?->name ?? $line->description,
                    'product_sku' => $line->product?->sku,
                    'ordered' => $line->quantity,
                    'previously_received' => $line->quantity_received,
                    'outstanding' => $outstanding,
                    'quantity' => $outstanding, // Default to full outstanding
                    'unit_cost' => $line->unit_price,
                ];
            }
        }
    }

    public function save(GoodsReceiptService $service): void
    {
        $this->authorize('create', GoodsReceipt::class);
        $this->validate();

        if ($this->purchaseOrder) {
            // Create from PO
            $lineQuantities = collect($this->receiptLines)
                ->filter(fn ($line) => $line['quantity'] > 0)
                ->mapWithKeys(fn ($line) => [
                    $line['po_line_id'] => [
                        'quantity' => $line['quantity'],
                        'unit_cost' => $line['unit_cost'],
                    ],
                ])
                ->toArray();

            if (empty($lineQuantities)) {
                Flux::toast(text: 'Angi mottatt antall for minst en linje', variant: 'danger');

                return;
            }

            try {
                $receipt = $service->createFromPurchaseOrder($this->purchaseOrder, $lineQuantities);

                // Update additional fields
                $receipt->update([
                    'supplier_delivery_note' => $this->supplier_delivery_note,
                    'notes' => $this->notes,
                    'receipt_date' => $this->receipt_date,
                ]);

                Flux::toast(text: 'Varemottak opprettet', variant: 'success');
                $this->redirect(route('purchasing.goods-receipts.show', $receipt), navigate: true);
            } catch (\InvalidArgumentException $e) {
                Flux::toast(text: $e->getMessage(), variant: 'danger');
            }
        } else {
            // Create standalone receipt
            if (empty($this->manualLines)) {
                Flux::toast(text: 'Legg til minst en linje', variant: 'danger');

                return;
            }

            try {
                $lines = collect($this->manualLines)->map(fn ($line) => [
                    'product_id' => $line['product_id'] ?: null,
                    'description' => $line['description'],
                    'quantity_received' => $line['quantity'],
                    'unit_cost' => $line['unit_cost'],
                ])->toArray();

                $receipt = $service->createStandalone(
                    contactId: $this->contact_id,
                    stockLocationId: $this->stock_location_id,
                    lines: $lines
                );

                $receipt->update([
                    'supplier_delivery_note' => $this->supplier_delivery_note,
                    'notes' => $this->notes,
                    'receipt_date' => $this->receipt_date,
                ]);

                Flux::toast(text: 'Varemottak opprettet', variant: 'success');
                $this->redirect(route('purchasing.goods-receipts.show', $receipt), navigate: true);
            } catch (\InvalidArgumentException $e) {
                Flux::toast(text: $e->getMessage(), variant: 'danger');
            }
        }
    }

    public function openLineModal(?int $index = null): void
    {
        $this->resetLineForm();

        if ($index !== null && isset($this->manualLines[$index])) {
            $line = $this->manualLines[$index];
            $this->editingLineIndex = $index;
            $this->line_product_id = $line['product_id'] ?? '';
            $this->line_description = $line['description'];
            $this->line_quantity = $line['quantity'];
            $this->line_unit_cost = $line['unit_cost'];
        }

        $this->showLineModal = true;
    }

    public function closeLineModal(): void
    {
        $this->showLineModal = false;
        $this->resetLineForm();
    }

    public function updatedLineProductId($value): void
    {
        if ($value && $product = Product::find($value)) {
            $this->line_description = $product->name;
            $this->line_unit_cost = $product->cost_price ?? 0;
        }
    }

    public function saveLine(): void
    {
        $this->validate([
            'line_description' => 'required|string',
            'line_quantity' => 'required|numeric|min:0.01',
            'line_unit_cost' => 'required|numeric|min:0',
        ]);

        $lineData = [
            'product_id' => $this->line_product_id ?: null,
            'description' => $this->line_description,
            'quantity' => $this->line_quantity,
            'unit_cost' => $this->line_unit_cost,
        ];

        if ($this->editingLineIndex !== null) {
            $this->manualLines[$this->editingLineIndex] = $lineData;
        } else {
            $this->manualLines[] = $lineData;
        }

        $this->closeLineModal();
    }

    public function deleteLine(int $index): void
    {
        unset($this->manualLines[$index]);
        $this->manualLines = array_values($this->manualLines);
    }

    private function resetLineForm(): void
    {
        $this->editingLineIndex = null;
        $this->line_product_id = '';
        $this->line_description = '';
        $this->line_quantity = 1;
        $this->line_unit_cost = '';
    }

    public function render()
    {
        $companyId = auth()->user()->current_company_id;

        return view('livewire.goods-receipt-form', [
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
            'openPurchaseOrders' => PurchaseOrder::where('company_id', $companyId)
                ->open()
                ->ordered()
                ->with('contact')
                ->get(),
        ]);
    }
}
