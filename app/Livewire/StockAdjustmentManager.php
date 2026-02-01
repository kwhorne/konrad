<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\StockLocation;
use App\Rules\ExistsInCompany;
use App\Services\StockService;
use Livewire\Component;

class StockAdjustmentManager extends Component
{
    public $product_id = '';

    public $stock_location_id = '';

    public $quantity = 0;

    public $unit_cost = '';

    public $notes = '';

    protected function rules(): array
    {
        return [
            'product_id' => ['required', new ExistsInCompany('products')],
            'stock_location_id' => ['required', new ExistsInCompany('stock_locations')],
            'quantity' => 'required|numeric|not_in:0',
            'unit_cost' => 'nullable|numeric|min:0',
            'notes' => 'required|string|max:500',
        ];
    }

    protected $messages = [
        'product_id.required' => 'Velg et produkt.',
        'stock_location_id.required' => 'Velg en lokasjon.',
        'quantity.required' => 'Antall er pakrevd.',
        'quantity.not_in' => 'Antall kan ikke vaere 0.',
        'notes.required' => 'Begrunnelse er pakrevd.',
    ];

    public function save(StockService $stockService)
    {
        $this->validate();

        $product = Product::findOrFail($this->product_id);
        $location = StockLocation::findOrFail($this->stock_location_id);

        try {
            $stockService->adjust(
                product: $product,
                stockLocation: $location,
                quantity: (float) $this->quantity,
                unitCost: $this->unit_cost ? (float) $this->unit_cost : null,
                notes: $this->notes
            );

            $this->dispatch('toast', message: 'Justering opprettet', type: 'success');
            $this->reset(['product_id', 'stock_location_id', 'quantity', 'unit_cost', 'notes']);
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        $companyId = auth()->user()->current_company_id;

        $products = Product::where('company_id', $companyId)
            ->where('is_stocked', true)
            ->active()
            ->ordered()
            ->get();

        $locations = StockLocation::where('company_id', $companyId)
            ->active()
            ->ordered()
            ->get();

        return view('livewire.stock-adjustment-manager', [
            'products' => $products,
            'locations' => $locations,
        ]);
    }
}
