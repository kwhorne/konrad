<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContractController extends Controller
{
    public function index(Request $request)
    {
        $query = Contract::with(['creator', 'responsibleUser']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('contract_number', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('expiring_soon')) {
            $query->whereDate('end_date', '<=', now()->addDays(90))
                ->whereDate('end_date', '>=', now());
        }

        $contracts = $query->orderBy('end_date', 'asc')->paginate(15);

        $stats = [
            'total' => Contract::count(),
            'active' => Contract::where('status', 'active')->count(),
            'expiring_soon' => Contract::whereDate('end_date', '<=', now()->addDays(90))
                ->whereDate('end_date', '>=', now())->count(),
            'expired' => Contract::whereDate('end_date', '<', now())->count(),
        ];

        return view('contracts.index', compact('contracts', 'stats'));
    }

    public function create()
    {
        $users = User::orderBy('name')->get();

        return view('contracts.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'established_date' => 'required|date',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'notice_period_days' => 'required|integer|min:0',
            'company_name' => 'required|string|max:255',
            'company_contact' => 'nullable|string|max:255',
            'company_email' => 'nullable|email',
            'company_phone' => 'nullable|string|max:50',
            'department' => 'nullable|string|max:255',
            'group' => 'nullable|string|max:255',
            'asset_reference' => 'nullable|string|max:255',
            'type' => 'required|in:service,lease,maintenance,software,insurance,employment,supplier,other',
            'status' => 'required|in:draft,active,expiring_soon,expired,terminated,renewed',
            'value' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'payment_frequency' => 'nullable|in:monthly,quarterly,yearly,one_time',
            'auto_renewal' => 'boolean',
            'renewal_period_months' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
            'responsible_user_id' => 'nullable|exists:users,id',
            'attachments.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
        ]);

        $validated['created_by'] = Auth::id();

        // Handle file uploads from Livewire component
        $attachments = [];
        if ($request->has('livewire_attachments')) {
            $livewireAttachments = json_decode($request->input('livewire_attachments'), true);

            foreach ($livewireAttachments as $tempPath) {
                // Move file from Livewire temp to permanent storage
                $file = \Livewire\Features\SupportFileUploads\TemporaryUploadedFile::createFromLivewire($tempPath);
                $filename = time().'_'.uniqid().'_'.$file->getClientOriginalName();
                $path = $file->storeAs('contracts', $filename, 'public');

                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ];
            }
        }

        $validated['attachments'] = ! empty($attachments) ? json_encode($attachments) : null;

        $contract = Contract::create($validated);

        return redirect()->route('contracts.show', $contract)
            ->with('success', 'Kontrakt opprettet!');
    }

    public function show(Contract $contract)
    {
        $contract->load(['creator', 'responsibleUser']);

        return view('contracts.show', compact('contract'));
    }

    public function edit(Contract $contract)
    {
        $users = User::orderBy('name')->get();

        return view('contracts.edit', compact('contract', 'users'));
    }

    public function update(Request $request, Contract $contract)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'established_date' => 'required|date',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'notice_period_days' => 'required|integer|min:0',
            'company_name' => 'required|string|max:255',
            'company_contact' => 'nullable|string|max:255',
            'company_email' => 'nullable|email',
            'company_phone' => 'nullable|string|max:50',
            'department' => 'nullable|string|max:255',
            'group' => 'nullable|string|max:255',
            'asset_reference' => 'nullable|string|max:255',
            'type' => 'required|in:service,lease,maintenance,software,insurance,employment,supplier,other',
            'status' => 'required|in:draft,active,expiring_soon,expired,terminated,renewed',
            'value' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'payment_frequency' => 'nullable|in:monthly,quarterly,yearly,one_time',
            'auto_renewal' => 'boolean',
            'renewal_period_months' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
            'responsible_user_id' => 'nullable|exists:users,id',
            'attachments.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
        ]);

        // Handle new file uploads
        if ($request->hasFile('attachments')) {
            $existingAttachments = $contract->attachments ? json_decode($contract->attachments, true) : [];

            foreach ($request->file('attachments') as $file) {
                $filename = time().'_'.uniqid().'_'.$file->getClientOriginalName();
                $path = $file->storeAs('contracts', $filename, 'public');

                $existingAttachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ];
            }

            $validated['attachments'] = json_encode($existingAttachments);
        }

        $contract->update($validated);

        return redirect()->route('contracts.show', $contract)
            ->with('success', 'Kontrakt oppdatert!');
    }

    public function destroy(Contract $contract)
    {
        $contract->delete();

        return redirect()->route('contracts.index')
            ->with('success', 'Kontrakt slettet!');
    }
}
