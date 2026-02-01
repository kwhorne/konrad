<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\User;
use App\Services\ContactService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function __construct(
        private ContactService $contactService
    ) {}

    public function index(Request $request): View
    {
        $contacts = $this->contactService->search(
            filters: $request->only(['search', 'type', 'status', 'is_active']),
            perPage: 15
        );

        $stats = $this->contactService->getStats();

        return view('contacts.index', compact('contacts', 'stats'));
    }

    public function create(): View
    {
        $users = User::all();

        return view('contacts.create', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateContact($request);
        $validated['created_by'] = Auth::id();
        $validated['is_active'] = $request->has('is_active');

        // Parse contact persons from JSON
        $contactPersons = [];
        if ($request->filled('contact_persons')) {
            $contactPersons = json_decode($request->contact_persons, true) ?? [];
        }

        // Parse attachment paths from JSON
        $attachmentPaths = [];
        if ($request->filled('livewire_attachments')) {
            $attachmentPaths = json_decode($request->livewire_attachments, true) ?? [];
        }

        $contact = $this->contactService->create($validated, $contactPersons, $attachmentPaths);

        return redirect()->route('contacts.edit', $contact)
            ->with('success', 'Kontakten ble opprettet.');
    }

    public function show(Contact $contact): RedirectResponse
    {
        // CRM-pattern: redirect directly to edit mode
        return redirect()->route('contacts.edit', $contact);
    }

    public function edit(Contact $contact): View
    {
        $contact->load(['contactPersons', 'accountManager', 'creator']);
        $users = User::all();

        return view('contacts.edit', compact('contact', 'users'));
    }

    public function update(Request $request, Contact $contact): RedirectResponse
    {
        $validated = $this->validateContact($request);
        $validated['is_active'] = $request->has('is_active');

        // Parse contact persons from JSON
        $contactPersons = [];
        if ($request->filled('contact_persons')) {
            $contactPersons = json_decode($request->contact_persons, true) ?? [];
        }

        // Parse attachment paths from JSON
        $attachmentPaths = [];
        if ($request->filled('livewire_attachments')) {
            $attachmentPaths = json_decode($request->livewire_attachments, true) ?? [];
        }

        $this->contactService->update($contact, $validated, $contactPersons, $attachmentPaths);

        return redirect()->route('contacts.edit', $contact)
            ->with('success', 'Endringene ble lagret.');
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        $this->contactService->delete($contact);

        return redirect()->route('contacts.index')
            ->with('success', 'Kontakten ble slettet.');
    }

    /**
     * Validate contact request data.
     *
     * @return array<string, mixed>
     */
    private function validateContact(Request $request): array
    {
        return $request->validate([
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
            'contact_persons' => 'nullable|string',
        ]);
    }
}
