<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $fillable = [
        'contact_number',
        'type',
        'company_name',
        'organization_number',
        'industry',
        'website',
        'email',
        'phone',
        'mobile',
        'fax',
        'address',
        'postal_code',
        'city',
        'country',
        'billing_address',
        'billing_postal_code',
        'billing_city',
        'billing_country',
        'customer_category',
        'credit_limit',
        'payment_terms_days',
        'payment_method',
        'bank_account',
        'linkedin',
        'facebook',
        'twitter',
        'description',
        'notes',
        'attachments',
        'status',
        'is_active',
        'customer_since',
        'last_contact_date',
        'created_by',
        'account_manager_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'customer_since' => 'date',
        'last_contact_date' => 'date',
        'credit_limit' => 'decimal:2',
        'payment_terms_days' => 'integer',
        'attachments' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($contact) {
            if (empty($contact->contact_number)) {
                $contact->contact_number = self::generateContactNumber();
            }
        });
    }

    public static function generateContactNumber(): string
    {
        $prefix = 'CON';
        $year = date('Y');
        $lastContact = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastContact ? (int) substr($lastContact->contact_number, -4) + 1 : 1;

        return $prefix.$year.str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function accountManager()
    {
        return $this->belongsTo(User::class, 'account_manager_id');
    }

    public function contactPersons()
    {
        return $this->hasMany(ContactPerson::class);
    }

    public function primaryContact()
    {
        return $this->hasOne(ContactPerson::class)->where('is_primary', true);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'contact_tag')
            ->withPivot('company_id')
            ->withTimestamps();
    }

    /**
     * Attach tags to the contact with company_id.
     *
     * @param  array<int>|int  $tagIds
     */
    public function attachTags(array|int $tagIds): void
    {
        $tagIds = is_array($tagIds) ? $tagIds : [$tagIds];
        $pivotData = array_fill_keys($tagIds, ['company_id' => $this->company_id]);
        $this->tags()->syncWithoutDetaching($pivotData);
    }

    /**
     * Sync tags for the contact with company_id.
     *
     * @param  array<int>  $tagIds
     */
    public function syncTags(array $tagIds): void
    {
        $pivotData = array_fill_keys($tagIds, ['company_id' => $this->company_id]);
        $this->tags()->sync($pivotData);
    }

    // Accessors
    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'customer' => 'Kunde',
            'supplier' => 'Leverandør',
            'partner' => 'Partner',
            'prospect' => 'Prospekt',
            'competitor' => 'Konkurrent',
            'other' => 'Annet',
            default => $this->type,
        };
    }

    public function getTypeBadgeColor(): string
    {
        return match ($this->type) {
            'customer' => 'success',
            'supplier' => 'info',
            'partner' => 'primary',
            'prospect' => 'warning',
            'competitor' => 'danger',
            'other' => 'outline',
            default => 'outline',
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'active' => 'Aktiv',
            'inactive' => 'Inaktiv',
            'prospect' => 'Prospekt',
            'archived' => 'Arkivert',
            default => $this->status,
        };
    }

    public function getStatusBadgeColor(): string
    {
        return match ($this->status) {
            'active' => 'success',
            'inactive' => 'outline',
            'prospect' => 'warning',
            'archived' => 'danger',
            default => 'outline',
        };
    }

    public function getCategoryLabel(): ?string
    {
        if (! $this->customer_category) {
            return null;
        }

        return match ($this->customer_category) {
            'a' => 'A-kunde',
            'b' => 'B-kunde',
            'c' => 'C-kunde',
            default => $this->customer_category,
        };
    }

    public function getFormattedCreditLimit(): string
    {
        if (! $this->credit_limit) {
            return '-';
        }

        return number_format($this->credit_limit, 2, ',', ' ').' NOK';
    }

    public function getFullAddress(): string
    {
        $parts = array_filter([
            $this->address,
            $this->postal_code ? $this->postal_code.' '.$this->city : $this->city,
            $this->country,
        ]);

        return implode(', ', $parts) ?: '-';
    }

    public function getFullBillingAddress(): ?string
    {
        if (! $this->billing_address) {
            return null;
        }

        $parts = array_filter([
            $this->billing_address,
            $this->billing_postal_code ? $this->billing_postal_code.' '.$this->billing_city : $this->billing_city,
            $this->billing_country,
        ]);

        return implode(', ', $parts);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('company_name');
    }
}
