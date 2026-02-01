<?php

namespace App\Http\Controllers;

use App\Models\ActivityType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ActivityTypeController extends Controller
{
    public function index()
    {
        $activityTypes = ActivityType::ordered()->paginate(20);

        return view('activity-types.index', compact('activityTypes'));
    }

    public function create()
    {
        return view('activity-types.create');
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->current_company_id;

        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('activity_types', 'name')->where('company_id', $companyId),
            ],
            'icon' => 'required|string|max:100',
            'color' => 'required|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        ActivityType::create($validated);

        return redirect()->route('activity-types.index')
            ->with('success', 'Aktivitetstypen ble opprettet.');
    }

    public function show(ActivityType $activityType)
    {
        return redirect()->route('activity-types.edit', $activityType);
    }

    public function edit(ActivityType $activityType)
    {
        return view('activity-types.edit', compact('activityType'));
    }

    public function update(Request $request, ActivityType $activityType)
    {
        $companyId = auth()->user()->current_company_id;

        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('activity_types', 'name')->where('company_id', $companyId)->ignore($activityType->id),
            ],
            'icon' => 'required|string|max:100',
            'color' => 'required|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $activityType->update($validated);

        return redirect()->route('activity-types.index')
            ->with('success', 'Aktivitetstypen ble oppdatert.');
    }

    public function destroy(ActivityType $activityType)
    {
        if ($activityType->activities()->exists()) {
            return redirect()->route('activity-types.index')
                ->with('error', 'Kan ikke slette aktivitetstype som er i bruk.');
        }

        $activityType->delete();

        return redirect()->route('activity-types.index')
            ->with('success', 'Aktivitetstypen ble slettet.');
    }
}
