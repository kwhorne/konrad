<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\ProductType;
use App\Models\Unit;
use App\Rules\ExistsInCompany;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class ProductManager extends Component
{
    use AuthorizesRequests, WithPagination;

    public $showModal = false;

    public $editingId = null;

    public $search = '';

    public $filterGroup = '';

    public $filterType = '';

    // Form fields
    public $name = '';

    public $sku = '';

    public $description = '';

    public $product_group_id = '';

    public $product_type_id = '';

    public $unit_id = '';

    public $price = '';

    public $cost_price = '';

    public $sort_order = 0;

    public $is_active = true;

    protected function rules(): array
    {
        $companyId = auth()->user()->current_company_id;

        $skuRule = Rule::unique('products', 'sku')
            ->where('company_id', $companyId);

        if ($this->editingId) {
            $skuRule->ignore($this->editingId);
        }

        return [
            'name' => 'required|string|max:255',
            'sku' => ['required', 'string', 'max:100', $skuRule],
            'description' => 'nullable|string',
            'product_group_id' => ['nullable', new ExistsInCompany('product_groups')],
            'product_type_id' => ['required', new ExistsInCompany('product_types')],
            'unit_id' => ['required', new ExistsInCompany('units')],
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ];
    }

    protected $messages = [
        'name.required' => 'Navn er påkrevd.',
        'sku.required' => 'SKU er påkrevd.',
        'sku.unique' => 'Denne SKU-en er allerede i bruk.',
        'product_type_id.required' => 'Varetype er påkrevd.',
        'unit_id.required' => 'Enhet er påkrevd.',
        'price.required' => 'Pris er påkrevd.',
        'price.numeric' => 'Pris må være et tall.',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterGroup(): void
    {
        $this->resetPage();
    }

    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    public function openModal($id = null): void
    {
        if ($id) {
            $product = Product::findOrFail($id);
            $this->authorize('view', $product);
        } else {
            $this->authorize('create', Product::class);
        }

        $this->resetForm();

        if ($id) {
            $this->editingId = $id;
            $product = Product::findOrFail($id);

            $this->name = $product->name;
            $this->sku = $product->sku;
            $this->description = $product->description;
            $this->product_group_id = $product->product_group_id ?? '';
            $this->product_type_id = $product->product_type_id;
            $this->unit_id = $product->unit_id;
            $this->price = $product->price;
            $this->cost_price = $product->cost_price;
            $this->sort_order = $product->sort_order;
            $this->is_active = $product->is_active;
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
            $product = Product::findOrFail($this->editingId);
            $this->authorize('update', $product);
        } else {
            $this->authorize('create', Product::class);
        }

        $data = [
            'name' => $this->name,
            'sku' => $this->sku,
            'description' => $this->description,
            'product_group_id' => $this->product_group_id ?: null,
            'product_type_id' => $this->product_type_id,
            'unit_id' => $this->unit_id,
            'price' => $this->price,
            'cost_price' => $this->cost_price ?: null,
            'sort_order' => $this->sort_order ?? 0,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            $product = Product::findOrFail($this->editingId);
            $product->update($data);
            session()->flash('success', 'Produktet ble oppdatert.');
        } else {
            Product::create($data);
            session()->flash('success', 'Produktet ble opprettet.');
        }

        $this->closeModal();
    }

    public function delete($id): void
    {
        $product = Product::findOrFail($id);
        $this->authorize('delete', $product);

        $product->delete();
        session()->flash('success', 'Produktet ble slettet.');
    }

    public function toggleActive($id): void
    {
        $product = Product::findOrFail($id);
        $this->authorize('update', $product);

        $product->update(['is_active' => ! $product->is_active]);
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->sku = '';
        $this->description = '';
        $this->product_group_id = '';
        $this->product_type_id = '';
        $this->unit_id = '';
        $this->price = '';
        $this->cost_price = '';
        $this->sort_order = 0;
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $query = Product::with(['productGroup', 'productType.vatRate', 'unit'])
            ->ordered();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('sku', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->filterGroup) {
            $query->where('product_group_id', $this->filterGroup);
        }

        if ($this->filterType) {
            $query->where('product_type_id', $this->filterType);
        }

        return view('livewire.product-manager', [
            'products' => $query->paginate(15),
            'productGroups' => ProductGroup::active()->ordered()->get(),
            'productTypes' => ProductType::active()->with('vatRate')->ordered()->get(),
            'units' => Unit::active()->ordered()->get(),
        ]);
    }
}
