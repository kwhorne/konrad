<?php

namespace App\Livewire;

use App\Models\CompanySetting;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class CompanySettingsManager extends Component
{
    use WithFileUploads;

    // Company information
    public string $company_name = '';

    public ?string $organization_number = '';

    public ?string $vat_number = '';

    // Address
    public ?string $address = '';

    public ?string $postal_code = '';

    public ?string $city = '';

    public string $country = 'Norge';

    // Contact
    public ?string $phone = '';

    public ?string $email = '';

    public ?string $website = '';

    // Bank
    public ?string $bank_name = '';

    public ?string $bank_account = '';

    public ?string $iban = '';

    public ?string $swift = '';

    // Logo
    public $logo;

    public ?string $current_logo_path = null;

    // Document settings
    public ?string $invoice_terms = '';

    public ?string $quote_terms = '';

    public ?string $order_terms = '';

    public int $default_payment_days = 14;

    public int $default_quote_validity_days = 30;

    public ?string $document_footer = '';

    public function mount(): void
    {
        $settings = CompanySetting::getOrCreate();

        $this->company_name = $settings->company_name ?? '';
        $this->organization_number = $settings->organization_number ?? '';
        $this->vat_number = $settings->vat_number ?? '';
        $this->address = $settings->address ?? '';
        $this->postal_code = $settings->postal_code ?? '';
        $this->city = $settings->city ?? '';
        $this->country = $settings->country ?? 'Norge';
        $this->phone = $settings->phone ?? '';
        $this->email = $settings->email ?? '';
        $this->website = $settings->website ?? '';
        $this->bank_name = $settings->bank_name ?? '';
        $this->bank_account = $settings->bank_account ?? '';
        $this->iban = $settings->iban ?? '';
        $this->swift = $settings->swift ?? '';
        $this->current_logo_path = $settings->logo_path;
        $this->invoice_terms = $settings->invoice_terms ?? '';
        $this->quote_terms = $settings->quote_terms ?? '';
        $this->order_terms = $settings->order_terms ?? '';
        $this->default_payment_days = $settings->default_payment_days ?? 14;
        $this->default_quote_validity_days = $settings->default_quote_validity_days ?? 30;
        $this->document_footer = $settings->document_footer ?? '';
    }

    public function save(): void
    {
        $this->validate([
            'company_name' => 'required|string|max:255',
            'organization_number' => 'nullable|string|max:20',
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
            'order_terms' => 'nullable|string',
            'default_payment_days' => 'required|integer|min:1|max:365',
            'default_quote_validity_days' => 'required|integer|min:1|max:365',
            'document_footer' => 'nullable|string',
        ]);

        $settings = CompanySetting::getOrCreate();

        // Handle logo upload
        $logoPath = $settings->logo_path;
        if ($this->logo) {
            // Delete old logo if exists
            if ($settings->logo_path && Storage::exists($settings->logo_path)) {
                Storage::delete($settings->logo_path);
            }
            $logoPath = $this->logo->store('logos', 'public');
        }

        $settings->update([
            'company_name' => $this->company_name,
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
            'order_terms' => $this->order_terms ?: null,
            'default_payment_days' => $this->default_payment_days,
            'default_quote_validity_days' => $this->default_quote_validity_days,
            'document_footer' => $this->document_footer ?: null,
        ]);

        $this->current_logo_path = $logoPath;
        $this->logo = null;

        $this->dispatch('toast', message: 'Firmainnstillinger lagret', variant: 'success');
    }

    public function deleteLogo(): void
    {
        $settings = CompanySetting::first();

        if ($settings && $settings->logo_path) {
            if (Storage::disk('public')->exists($settings->logo_path)) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            $settings->update(['logo_path' => null]);
            $this->current_logo_path = null;

            $this->dispatch('toast', message: 'Logo slettet', variant: 'success');
        }
    }

    public function render()
    {
        return view('livewire.company-settings-manager');
    }
}
