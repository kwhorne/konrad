<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::ordered()->paginate(20);

        return view('units.index', compact('units'));
    }

    public function create()
    {
        return view('units.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:units,code',
            'symbol' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        Unit::create($validated);

        return redirect()->route('units.index')
            ->with('success', 'Enheten ble opprettet.');
    }

    public function show(Unit $unit)
    {
        return redirect()->route('units.edit', $unit);
    }

    public function edit(Unit $unit)
    {
        return view('units.edit', compact('unit'));
    }

    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:units,code,'.$unit->id,
            'symbol' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $unit->update($validated);

        return redirect()->route('units.index')
            ->with('success', 'Enheten ble oppdatert.');
    }

    public function destroy(Unit $unit)
    {
        if ($unit->products()->exists()) {
            return redirect()->route('units.index')
                ->with('error', 'Kan ikke slette enhet som er i bruk.');
        }

        $unit->delete();

        return redirect()->route('units.index')
            ->with('success', 'Enheten ble slettet.');
    }
}
