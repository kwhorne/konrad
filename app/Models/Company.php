<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Cashier\Billable;

class Company extends Model
{
    use Billable, HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
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
        'default_payment_days',
        'default_quote_validity_days',
        'document_footer',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'default_payment_days' => 'integer',
            'default_quote_validity_days' => 'integer',
        ];
    }

    /**
     * Get all users belonging to this company.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['role', 'is_default', 'joined_at', 'department_id'])
            ->withTimestamps();
    }

    /**
     * Get all departments for this company.
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    /**
     * Get the owner of this company.
     */
    public function owner(): ?User
    {
        return $this->users()->wherePivot('role', 'owner')->first();
    }

    /**
     * Get managers of this company.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    public function managers()
    {
        return $this->users()->wherePivot('role', 'manager')->get();
    }

    /**
     * Get all members (non-owner, non-manager) of this company.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    public function members()
    {
        return $this->users()->wherePivot('role', 'member')->get();
    }

    /**
     * Check if a user is the owner of this company.
     */
    public function isOwner(User $user): bool
    {
        return $this->users()
            ->wherePivot('user_id', $user->id)
            ->wherePivot('role', 'owner')
            ->exists();
    }

    /**
     * Check if a user is a manager of this company.
     */
    public function isManager(User $user): bool
    {
        return $this->users()
            ->wherePivot('user_id', $user->id)
            ->wherePivot('role', 'manager')
            ->exists();
    }

    /**
     * Check if a user can manage this company (owner or manager).
     */
    public function canBeMangedBy(User $user): bool
    {
        return $this->users()
            ->wherePivot('user_id', $user->id)
            ->whereIn('company_user.role', ['owner', 'manager'])
            ->exists();
    }

    /**
     * Check if a user belongs to this company.
     */
    public function hasUser(User $user): bool
    {
        return $this->users()->wherePivot('user_id', $user->id)->exists();
    }

    /**
     * Get the formatted organization number (XXX XXX XXX).
     */
    public function getFormattedOrganizationNumberAttribute(): string
    {
        $orgNr = $this->organization_number;
        if (strlen($orgNr) === 9) {
            return substr($orgNr, 0, 3).' '.substr($orgNr, 3, 3).' '.substr($orgNr, 6, 3);
        }

        return $orgNr;
    }

    /**
     * Get the full address.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->postal_code ? $this->postal_code.' '.$this->city : $this->city,
            $this->country !== 'Norge' ? $this->country : null,
        ]);

        return implode(', ', $parts) ?: '';
    }

    /**
     * Get the logo URL.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if ($this->logo_path) {
            return asset('storage/'.$this->logo_path);
        }

        return null;
    }

    /**
     * Scope to get only active companies.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the inventory settings for this company.
     */
    public function inventorySettings(): HasOne
    {
        return $this->hasOne(InventorySettings::class);
    }

    /**
     * Get the accounting settings for this company.
     */
    public function accountingSettings(): HasOne
    {
        return $this->hasOne(AccountingSettings::class);
    }

    /**
     * Get all modules for this company.
     */
    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'company_modules')
            ->withPivot(['is_enabled', 'enabled_at', 'expires_at', 'enabled_by', 'stripe_subscription_id', 'stripe_subscription_status'])
            ->withTimestamps();
    }

    /**
     * Get all enabled modules for this company.
     */
    public function enabledModules(): BelongsToMany
    {
        return $this->modules()
            ->wherePivot('is_enabled', true)
            ->where(function ($query) {
                $query->whereNull('company_modules.expires_at')
                    ->orWhere('company_modules.expires_at', '>', now());
            });
    }

    /**
     * Check if this company has access to a specific module.
     */
    public function hasModule(string $slug): bool
    {
        $module = Module::where('slug', $slug)->first();

        if (! $module) {
            return false;
        }

        // Standard modules are always enabled
        if (! $module->is_premium) {
            return true;
        }

        // Check if company has this premium module enabled
        return $this->enabledModules()->where('modules.id', $module->id)->exists();
    }
}
