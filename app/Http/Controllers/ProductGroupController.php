<?php

namespace App\Http\Controllers;

use App\Models\ProductGroup;
use Illuminate\Http\Request;

class ProductGroupController extends Controller
{
    public function index()
    {
        $productGroups = ProductGroup::ordered()->withCount('products')->paginate(20);

        return view('product-groups.index', compact('productGroups'));
    }

    public function create()
    {
        return view('product-groups.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:product_groups,code',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        ProductGroup::create($validated);

        return redirect()->route('product-groups.index')
            ->with('success', 'Varegruppen ble opprettet.');
    }

    public function show(ProductGroup $productGroup)
    {
        return redirect()->route('product-groups.edit', $productGroup);
    }

    public function edit(ProductGroup $productGroup)
    {
        return view('product-groups.edit', compact('productGroup'));
    }

    public function update(Request $request, ProductGroup $productGroup)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:product_groups,code,'.$productGroup->id,
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $productGroup->update($validated);

        return redirect()->route('product-groups.index')
            ->with('success', 'Varegruppen ble oppdatert.');
    }

    public function destroy(ProductGroup $productGroup)
    {
        if ($productGroup->products()->exists()) {
            return redirect()->route('product-groups.index')
                ->with('error', 'Kan ikke slette varegruppe som er i bruk.');
        }

        $productGroup->delete();

        return redirect()->route('product-groups.index')
            ->with('success', 'Varegruppen ble slettet.');
    }
}
