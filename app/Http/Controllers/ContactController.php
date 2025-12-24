<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $query = Contact::with(['primaryContact', 'accountManager'])
            ->where(function ($q) use ($request) {
                if ($request->filled('search')) {
                    $search = $request->search;
                    $q->where('company_name', 'like', "%{$search}%")
                        ->orWhere('contact_number', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('organization_number', 'like', "%{$search}%");
                }
            });

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === 'true');
        }

        $contacts = $query->latest()->paginate(15);

        $stats = [
            'total' => Contact::count(),
            'customers' => Contact::where('type', 'customer')->count(),
            'suppliers' => Contact::where('type', 'supplier')->count(),
            'partners' => Contact::where('type', 'partner')->count(),
        ];

        return view('contacts.index', compact('contacts', 'stats'));
    }

    public function create()
    {
        $users = User::all();

        return view('contacts.create', compact('users'));
    }

    public function store(Request $request)
    {
        \Log::info('Contact store method called', ['request' => $request->all()]);

        $validated = $request->validate([
            'type' => 'required|in:customer,supplier,partner,prospect,competitor,other',
            'company_name' => 'required|string|max:255',
            'organization_number' => 'nullable|string|max:255',
            'industry' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:255',
            'fax' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'billing_address' => 'nullable|string|max:255',
            'billing_postal_code' => 'nullable|string|max:255',
            'billing_city' => 'nullable|string|max:255',
            'billing_country' => 'nullable|string|max:255',
            'customer_category' => 'nullable|in:a,b,c',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms_days' => 'nullable|integer|min:0',
            'payment_method' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:255',
            'linkedin' => 'nullable|url|max:255',
            'facebook' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'tags' => 'nullable|array',
            'status' => 'required|in:active,inactive,prospect,archived',
            'is_active' => 'nullable|boolean',
            'customer_since' => 'nullable|date',
            'last_contact_date' => 'nullable|date',
            'account_manager_id' => 'nullable|exists:users,id',
            'livewire_attachments' => 'nullable|string',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['is_active'] = $request->has('is_active');

        // Handle file uploads from Livewire
        if ($request->filled('livewire_attachments')) {
            $attachmentPaths = json_decode($request->livewire_attachments, true);
            $attachments = [];

            foreach ($attachmentPaths as $path) {
                if (Storage::disk('local')->exists($path)) {
                    $file = Storage::disk('local')->get($path);
                    $filename = basename($path);
                    $uniqueFilename = Str::uuid().'_'.$filename;

                    Storage::disk('public')->put('contacts/'.$uniqueFilename, $file);

                    $attachments[] = [
                        'name' => $filename,
                        'path' => 'contacts/'.$uniqueFilename,
                        'size' => Storage::disk('local')->size($path),
                        'mime_type' => Storage::disk('local')->mimeType($path) ?? 'application/octet-stream',
                    ];

                    Storage::disk('local')->delete($path);
                }
            }

            $validated['attachments'] = $attachments;
        }

        $contact = Contact::create($validated);

        // Handle contact persons
        if ($request->filled('contact_persons')) {
            $persons = json_decode($request->contact_persons, true);
            foreach ($persons as $personData) {
                $contact->contactPersons()->create($personData);
            }
        }

        return redirect()->route('contacts.edit', $contact)
            ->with('success', 'Kontakten ble opprettet.');
    }

    public function show(Contact $contact)
    {
        // CRM-pattern: redirect directly to edit mode
        return redirect()->route('contacts.edit', $contact);
    }

    public function edit(Contact $contact)
    {
        $contact->load(['contactPersons', 'accountManager', 'creator']);
        $users = User::all();

        return view('contacts.edit', compact('contact', 'users'));
    }

    public function update(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'type' => 'required|in:customer,supplier,partner,prospect,competitor,other',
            'company_name' => 'required|string|max:255',
            'organization_number' => 'nullable|string|max:255',
            'industry' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:255',
            'fax' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'billing_address' => 'nullable|string|max:255',
            'billing_postal_code' => 'nullable|string|max:255',
            'billing_city' => 'nullable|string|max:255',
            'billing_country' => 'nullable|string|max:255',
            'customer_category' => 'nullable|in:a,b,c',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms_days' => 'nullable|integer|min:0',
            'payment_method' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:255',
            'linkedin' => 'nullable|url|max:255',
            'facebook' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'tags' => 'nullable|array',
            'status' => 'required|in:active,inactive,prospect,archived',
            'is_active' => 'nullable|boolean',
            'customer_since' => 'nullable|date',
            'last_contact_date' => 'nullable|date',
            'account_manager_id' => 'nullable|exists:users,id',
            'livewire_attachments' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');

        // Handle new file uploads from Livewire
        if ($request->filled('livewire_attachments')) {
            $attachmentPaths = json_decode($request->livewire_attachments, true);
            $existingAttachments = $contact->attachments ?? [];
            $newAttachments = [];

            foreach ($attachmentPaths as $path) {
                if (Storage::disk('local')->exists($path)) {
                    $file = Storage::disk('local')->get($path);
                    $filename = basename($path);
                    $uniqueFilename = Str::uuid().'_'.$filename;

                    Storage::disk('public')->put('contacts/'.$uniqueFilename, $file);

                    $newAttachments[] = [
                        'name' => $filename,
                        'path' => 'contacts/'.$uniqueFilename,
                        'size' => Storage::disk('local')->size($path),
                        'mime_type' => Storage::disk('local')->mimeType($path),
                    ];

                    Storage::disk('local')->delete($path);
                }
            }

            $validated['attachments'] = array_merge($existingAttachments, $newAttachments);
        }

        $contact->update($validated);

        // Handle contact persons - sync them
        if ($request->filled('contact_persons')) {
            $persons = json_decode($request->contact_persons, true);

            // Delete existing persons and recreate
            $contact->contactPersons()->delete();

            foreach ($persons as $personData) {
                $contact->contactPersons()->create($personData);
            }
        }

        return redirect()->route('contacts.edit', $contact)
            ->with('success', 'Endringene ble lagret.');
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect()->route('contacts.index')
            ->with('success', 'Kontakten ble slettet.');
    }
}
