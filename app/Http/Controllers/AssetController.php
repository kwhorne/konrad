<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\User;
use App\Rules\UserInCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::with(['creator', 'responsibleUser']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('asset_number', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === 'true');
        }

        $assets = $query->orderBy('created_at', 'desc')->paginate(15);

        $stats = [
            'total' => Asset::count(),
            'in_use' => Asset::where('status', 'in_use')->count(),
            'available' => Asset::where('status', 'available')->count(),
            'maintenance' => Asset::where('status', 'maintenance')->count(),
        ];

        return view('assets.index', compact('assets', 'stats'));
    }

    public function create()
    {
        $users = User::orderBy('name')->get();

        return view('assets.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'serial_number' => 'nullable|string|max:255',
            'asset_model' => 'nullable|string|max:255',
            'purchase_price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'purchase_date' => 'nullable|date',
            'supplier' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'group' => 'nullable|string|max:255',
            'insurance_number' => 'nullable|string|max:255',
            'invoice_number' => 'nullable|string|max:255',
            'invoice_date' => 'nullable|date',
            'warranty_from' => 'nullable|date',
            'warranty_until' => 'nullable|date',
            'status' => 'required|in:in_use,available,maintenance,retired,lost,sold',
            'condition' => 'required|in:excellent,good,fair,poor,broken',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
            'responsible_user_id' => ['nullable', new UserInCompany],
        ]);

        $validated['created_by'] = Auth::id();

        // Handle file uploads from Livewire component
        $attachments = [];
        if ($request->has('livewire_attachments')) {
            $livewireAttachments = json_decode($request->input('livewire_attachments'), true);

            foreach ($livewireAttachments as $tempPath) {
                $file = \Livewire\Features\SupportFileUploads\TemporaryUploadedFile::createFromLivewire($tempPath);
                $filename = time().'_'.uniqid().'_'.$file->getClientOriginalName();
                $path = $file->storeAs('assets', $filename, 'public');

                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ];
            }
        }

        $validated['attachments'] = ! empty($attachments) ? json_encode($attachments) : null;

        $asset = Asset::create($validated);

        return redirect()->route('assets.show', $asset)
            ->with('success', 'Eiendel opprettet!');
    }

    public function show(Asset $asset)
    {
        $asset->load(['creator', 'responsibleUser']);

        return view('assets.show', compact('asset'));
    }

    public function edit(Asset $asset)
    {
        $users = User::orderBy('name')->get();

        return view('assets.edit', compact('asset', 'users'));
    }

    public function update(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'serial_number' => 'nullable|string|max:255',
            'asset_model' => 'nullable|string|max:255',
            'purchase_price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'purchase_date' => 'nullable|date',
            'supplier' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'group' => 'nullable|string|max:255',
            'insurance_number' => 'nullable|string|max:255',
            'invoice_number' => 'nullable|string|max:255',
            'invoice_date' => 'nullable|date',
            'warranty_from' => 'nullable|date',
            'warranty_until' => 'nullable|date',
            'status' => 'required|in:in_use,available,maintenance,retired,lost,sold',
            'condition' => 'required|in:excellent,good,fair,poor,broken',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
            'responsible_user_id' => ['nullable', new UserInCompany],
        ]);

        // Handle new file uploads
        if ($request->has('livewire_attachments')) {
            $existingAttachments = $asset->attachments ? json_decode($asset->attachments, true) : [];
            $livewireAttachments = json_decode($request->input('livewire_attachments'), true);

            foreach ($livewireAttachments as $tempPath) {
                $file = \Livewire\Features\SupportFileUploads\TemporaryUploadedFile::createFromLivewire($tempPath);
                $filename = time().'_'.uniqid().'_'.$file->getClientOriginalName();
                $path = $file->storeAs('assets', $filename, 'public');

                $existingAttachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ];
            }

            $validated['attachments'] = json_encode($existingAttachments);
        }

        $asset->update($validated);

        return redirect()->route('assets.show', $asset)
            ->with('success', 'Eiendel oppdatert!');
    }

    public function destroy(Asset $asset)
    {
        $asset->delete();

        return redirect()->route('assets.index')
            ->with('success', 'Eiendel slettet!');
    }
}
