<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class CompanyProfileManager extends Component
{
    use WithFileUploads;

    public string $name = '';

    public ?string $organization_number = '';

    public ?string $vat_number = '';

    public ?string $address = '';

    public ?string $postal_code = '';

    public ?string $city = '';

    public string $country = 'Norge';

    public ?string $phone = '';

    public ?string $email = '';

    public ?string $website = '';

    public ?string $bank_name = '';

    public ?string $bank_account = '';

    public ?string $iban = '';

    public ?string $swift = '';

    public $logo;

    public ?string $current_logo_path = null;

    public ?string $invoice_terms = '';

    public ?string $quote_terms = '';

    public int $default_payment_days = 14;

    public int $default_quote_validity_days = 30;

    public ?string $document_footer = '';

    public function mount(): void
    {
        $company = app('current.company');

        if (! $company) {
            return;
        }

        $this->name = $company->name ?? '';
        $this->organization_number = $company->organization_number ?? '';
        $this->vat_number = $company->vat_number ?? '';
        $this->address = $company->address ?? '';
        $this->postal_code = $company->postal_code ?? '';
        $this->city = $company->city ?? '';
        $this->country = $company->country ?? 'Norge';
        $this->phone = $company->phone ?? '';
        $this->email = $company->email ?? '';
        $this->website = $company->website ?? '';
        $this->bank_name = $company->bank_name ?? '';
        $this->bank_account = $company->bank_account ?? '';
        $this->iban = $company->iban ?? '';
        $this->swift = $company->swift ?? '';
        $this->current_logo_path = $company->logo_path;
        $this->invoice_terms = $company->invoice_terms ?? '';
        $this->quote_terms = $company->quote_terms ?? '';
        $this->default_payment_days = $company->default_payment_days ?? 14;
        $this->default_quote_validity_days = $company->default_quote_validity_days ?? 30;
        $this->document_footer = $company->document_footer ?? '';
    }

    public function save(): void
    {
        $company = app('current.company');

        if (! $company || ! auth()->user()->canManage($company)) {
            $this->dispatch('toast', message: 'Du har ikke tilgang til å endre selskapsinnstillinger.', variant: 'danger');

            return;
        }

        $this->validate([
            'name' => 'required|string|max:255',
            'organization_number' => 'required|string|max:20',
            'vat_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'bank_name' => 'nullable|string|max:100',
            'bank_account' => 'nullable|string|max:20',
            'iban' => 'nullable|string|max:34',
            'swift' => 'nullable|string|max:11',
            'logo' => 'nullable|image|max:2048',
            'invoice_terms' => 'nullable|string',
            'quote_terms' => 'nullable|string',
            'default_payment_days' => 'required|integer|min:1|max:365',
            'default_quote_validity_days' => 'required|integer|min:1|max:365',
            'document_footer' => 'nullable|string',
        ]);

        $logoPath = $company->logo_path;
        if ($this->logo) {
            if ($company->logo_path && Storage::disk('public')->exists($company->logo_path)) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $logoPath = $this->logo->store('logos', 'public');
        }

        $company->update([
            'name' => $this->name,
            'organization_number' => $this->organization_number ?: null,
            'vat_number' => $this->vat_number ?: null,
            'address' => $this->address ?: null,
            'postal_code' => $this->postal_code ?: null,
            'city' => $this->city ?: null,
            'country' => $this->country ?: 'Norge',
            'phone' => $this->phone ?: null,
            'email' => $this->email ?: null,
            'website' => $this->website ?: null,
            'bank_name' => $this->bank_name ?: null,
            'bank_account' => $this->bank_account ?: null,
            'iban' => $this->iban ?: null,
            'swift' => $this->swift ?: null,
            'logo_path' => $logoPath,
            'invoice_terms' => $this->invoice_terms ?: null,
            'quote_terms' => $this->quote_terms ?: null,
            'default_payment_days' => $this->default_payment_days,
            'default_quote_validity_days' => $this->default_quote_validity_days,
            'document_footer' => $this->document_footer ?: null,
        ]);

        $this->current_logo_path = $logoPath;
        $this->logo = null;

        $this->dispatch('toast', message: 'Selskapsinnstillinger lagret', variant: 'success');
    }

    public function deleteLogo(): void
    {
        $company = app('current.company');

        if (! $company || ! auth()->user()->canManage($company)) {
            return;
        }

        if ($company->logo_path) {
            if (Storage::disk('public')->exists($company->logo_path)) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $company->update(['logo_path' => null]);
            $this->current_logo_path = null;

            $this->dispatch('toast', message: 'Logo slettet', variant: 'success');
        }
    }

    public function render()
    {
        $company = app('current.company');

        return view('livewire.company-profile-manager', [
            'canManage' => $company && auth()->user()->canManage($company),
        ]);
    }
}
