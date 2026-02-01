<?php

namespace App\Http\Controllers;

use App\Models\VatRate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VatRateController extends Controller
{
    public function index()
    {
        $vatRates = VatRate::ordered()->paginate(20);

        return view('vat-rates.index', compact('vatRates'));
    }

    public function create()
    {
        return view('vat-rates.create');
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->current_company_id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'required', 'string', 'max:50',
                Rule::unique('vat_rates', 'code')->where('company_id', $companyId),
            ],
            'rate' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['is_default'] = $request->has('is_default');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        if ($validated['is_default']) {
            VatRate::where('is_default', true)->update(['is_default' => false]);
        }

        VatRate::create($validated);

        return redirect()->route('vat-rates.index')
            ->with('success', 'Momssatsen ble opprettet.');
    }

    public function show(VatRate $vatRate)
    {
        return redirect()->route('vat-rates.edit', $vatRate);
    }

    public function edit(VatRate $vatRate)
    {
        return view('vat-rates.edit', compact('vatRate'));
    }

    public function update(Request $request, VatRate $vatRate)
    {
        $companyId = auth()->user()->current_company_id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'required', 'string', 'max:50',
                Rule::unique('vat_rates', 'code')->where('company_id', $companyId)->ignore($vatRate->id),
            ],
            'rate' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['is_default'] = $request->has('is_default');

        if ($validated['is_default'] && ! $vatRate->is_default) {
            VatRate::where('is_default', true)->update(['is_default' => false]);
        }

        $vatRate->update($validated);

        return redirect()->route('vat-rates.index')
            ->with('success', 'Momssatsen ble oppdatert.');
    }

    public function destroy(VatRate $vatRate)
    {
        if ($vatRate->productTypes()->exists()) {
            return redirect()->route('vat-rates.index')
                ->with('error', 'Kan ikke slette momssats som er i bruk.');
        }

        $vatRate->delete();

        return redirect()->route('vat-rates.index')
            ->with('success', 'Momssatsen ble slettet.');
    }
}
