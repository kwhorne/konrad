<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

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
            ->withPivot(['role', 'is_default', 'joined_at'])
            ->withTimestamps();
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
}
