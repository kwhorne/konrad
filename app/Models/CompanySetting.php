<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class CompanySetting extends Model
{
    protected $fillable = [
        'company_name',
        'organization_number',
        'vat_number',
        'address',
        'postal_code',
        'city',
        'country',
        'phone',
        'email',
        'website',
        'bank_name',
        'bank_account',
        'iban',
        'swift',
        'logo_path',
        'invoice_terms',
        'quote_terms',
        'order_terms',
        'default_payment_days',
        'default_quote_validity_days',
        'document_footer',
    ];

    protected $casts = [
        'default_payment_days' => 'integer',
        'default_quote_validity_days' => 'integer',
    ];

    /**
     * Get the current company settings (singleton pattern).
     */
    public static function current(): ?self
    {
        return Cache::remember('company_settings', 3600, function () {
            return static::first();
        });
    }

    /**
     * Clear the cached settings.
     */
    public static function clearCache(): void
    {
        Cache::forget('company_settings');
    }

    /**
     * Get or create the company settings.
     */
    public static function getOrCreate(): self
    {
        $settings = static::first();

        if (! $settings) {
            $settings = static::create([
                'company_name' => config('app.name', 'Mitt Firma'),
            ]);
        }

        return $settings;
    }

    /**
     * Get the full address as a single string.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            trim($this->postal_code.' '.$this->city),
            $this->country !== 'Norge' ? $this->country : null,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get the formatted organization number.
     */
    public function getFormattedOrgNumberAttribute(): ?string
    {
        if (! $this->organization_number) {
            return null;
        }

        // Format as XXX XXX XXX
        $number = preg_replace('/\D/', '', $this->organization_number);

        if (strlen($number) === 9) {
            return substr($number, 0, 3).' '.substr($number, 3, 3).' '.substr($number, 6, 3);
        }

        return $this->organization_number;
    }

    /**
     * Get the formatted bank account number.
     */
    public function getFormattedBankAccountAttribute(): ?string
    {
        if (! $this->bank_account) {
            return null;
        }

        // Format as XXXX.XX.XXXXX
        $number = preg_replace('/\D/', '', $this->bank_account);

        if (strlen($number) === 11) {
            return substr($number, 0, 4).'.'.substr($number, 4, 2).'.'.substr($number, 6, 5);
        }

        return $this->bank_account;
    }

    /**
     * Get the logo URL.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        return Storage::url($this->logo_path);
    }

    /**
     * Check if the company has a logo.
     */
    public function hasLogo(): bool
    {
        return $this->logo_path && Storage::exists($this->logo_path);
    }

    /**
     * Delete the logo file.
     */
    public function deleteLogo(): void
    {
        if ($this->logo_path && Storage::exists($this->logo_path)) {
            Storage::delete($this->logo_path);
        }

        $this->update(['logo_path' => null]);
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::saved(function () {
            static::clearCache();
        });

        static::deleted(function () {
            static::clearCache();
        });
    }
}
