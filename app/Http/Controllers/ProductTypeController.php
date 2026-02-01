<?php

namespace App\Http\Controllers;

use App\Models\ProductType;
use App\Models\VatRate;
use App\Rules\ExistsInCompany;
use Illuminate\Http\Request;

class ProductTypeController extends Controller
{
    public function index()
    {
        $productTypes = ProductType::with('vatRate')->ordered()->withCount('products')->paginate(20);

        return view('product-types.index', compact('productTypes'));
    }

    public function create()
    {
        $vatRates = VatRate::active()->ordered()->get();

        return view('product-types.create', compact('vatRates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:product_types,code',
            'vat_rate_id' => ['required', new ExistsInCompany('vat_rates')],
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        ProductType::create($validated);

        return redirect()->route('product-types.index')
            ->with('success', 'Varetypen ble opprettet.');
    }

    public function show(ProductType $productType)
    {
        return redirect()->route('product-types.edit', $productType);
    }

    public function edit(ProductType $productType)
    {
        $vatRates = VatRate::active()->ordered()->get();

        return view('product-types.edit', compact('productType', 'vatRates'));
    }

    public function update(Request $request, ProductType $productType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:product_types,code,'.$productType->id,
            'vat_rate_id' => ['required', new ExistsInCompany('vat_rates')],
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $productType->update($validated);

        return redirect()->route('product-types.index')
            ->with('success', 'Varetypen ble oppdatert.');
    }

    public function destroy(ProductType $productType)
    {
        if ($productType->products()->exists()) {
            return redirect()->route('product-types.index')
                ->with('error', 'Kan ikke slette varetype som er i bruk.');
        }

        $productType->delete();

        return redirect()->route('product-types.index')
            ->with('success', 'Varetypen ble slettet.');
    }
}
