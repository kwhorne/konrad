<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $query = Account::query()->ordered();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('account_number', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('class')) {
            $query->byClass($request->class);
        }

        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        $accounts = $query->paginate(50)->withQueryString();

        return view('accounts.index', compact('accounts'));
    }

    public function create()
    {
        $parentAccounts = Account::active()->ordered()->get();

        return view('accounts.create', compact('parentAccounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_number' => 'required|string|max:10|unique:accounts,account_number',
            'name' => 'required|string|max:255',
            'account_class' => 'required|in:1,2,3,4,5,6,7,8',
            'account_type' => 'required|in:asset,liability,equity,revenue,expense',
            'parent_id' => 'nullable|exists:accounts,id',
            'description' => 'nullable|string',
            'vat_code' => 'nullable|string|max:10',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['is_system'] = false;

        Account::create($validated);

        return redirect()->route('accounts.index')
            ->with('success', 'Kontoen ble opprettet.');
    }

    public function show(Account $account)
    {
        return redirect()->route('accounts.edit', $account);
    }

    public function edit(Account $account)
    {
        $parentAccounts = Account::active()
            ->where('id', '!=', $account->id)
            ->ordered()
            ->get();

        return view('accounts.edit', compact('account', 'parentAccounts'));
    }

    public function update(Request $request, Account $account)
    {
        $validated = $request->validate([
            'account_number' => 'required|string|max:10|unique:accounts,account_number,'.$account->id,
            'name' => 'required|string|max:255',
            'account_class' => 'required|in:1,2,3,4,5,6,7,8',
            'account_type' => 'required|in:asset,liability,equity,revenue,expense',
            'parent_id' => 'nullable|exists:accounts,id',
            'description' => 'nullable|string',
            'vat_code' => 'nullable|string|max:10',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        // Don't allow changing system accounts' core properties
        if ($account->is_system) {
            unset($validated['account_number'], $validated['account_class'], $validated['account_type']);
        }

        $account->update($validated);

        return redirect()->route('accounts.index')
            ->with('success', 'Kontoen ble oppdatert.');
    }

    public function destroy(Account $account)
    {
        if ($account->is_system) {
            return redirect()->route('accounts.index')
                ->with('error', 'Systemkontoer kan ikke slettes.');
        }

        if ($account->voucherLines()->exists()) {
            return redirect()->route('accounts.index')
                ->with('error', 'Kan ikke slette konto som har posteringer.');
        }

        if ($account->children()->exists()) {
            return redirect()->route('accounts.index')
                ->with('error', 'Kan ikke slette konto som har underkontoer.');
        }

        $account->delete();

        return redirect()->route('accounts.index')
            ->with('success', 'Kontoen ble slettet.');
    }
}
