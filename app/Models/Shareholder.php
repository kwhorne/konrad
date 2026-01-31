<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class Shareholder extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $fillable = [
        'shareholder_type',
        'name',
        'national_id',
        'organization_number',
        'country_code',
        'address',
        'postal_code',
        'city',
        'email',
        'phone',
        'is_active',
        'notes',
        'contact_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // Encrypt/decrypt national_id (fødselsnummer)
    public function setNationalIdAttribute(?string $value): void
    {
        $this->attributes['national_id'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getNationalIdAttribute(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Exception) {
            return null;
        }
    }

    // Relationships
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function shareholdings(): HasMany
    {
        return $this->hasMany(Shareholding::class);
    }

    public function activeShareholdings(): HasMany
    {
        return $this->hasMany(Shareholding::class)->where('is_active', true);
    }

    public function transactionsFrom(): HasMany
    {
        return $this->hasMany(ShareTransaction::class, 'from_shareholder_id');
    }

    public function transactionsTo(): HasMany
    {
        return $this->hasMany(ShareTransaction::class, 'to_shareholder_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePersons($query)
    {
        return $query->where('shareholder_type', 'person');
    }

    public function scopeCompanies($query)
    {
        return $query->where('shareholder_type', 'company');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }

    // Accessors
    public function getTypeLabel(): string
    {
        return match ($this->shareholder_type) {
            'person' => 'Person',
            'company' => 'Selskap',
            default => $this->shareholder_type,
        };
    }

    public function getTypeBadgeColor(): string
    {
        return match ($this->shareholder_type) {
            'person' => 'info',
            'company' => 'primary',
            default => 'outline',
        };
    }

    public function getMaskedNationalId(): ?string
    {
        $nationalId = $this->national_id;

        if (! $nationalId || strlen($nationalId) < 6) {
            return null;
        }

        // Show first 6 digits (birth date), mask the rest
        return substr($nationalId, 0, 6).'*****';
    }

    public function getFullAddress(): string
    {
        $parts = array_filter([
            $this->address,
            $this->postal_code ? $this->postal_code.' '.$this->city : $this->city,
            $this->country_code !== 'NO' ? $this->country_code : null,
        ]);

        return implode(', ', $parts) ?: '-';
    }

    public function getIdentifier(): string
    {
        if ($this->shareholder_type === 'company' && $this->organization_number) {
            return $this->organization_number;
        }

        return $this->getMaskedNationalId() ?? '-';
    }

    // Business methods
    public function getTotalShares(): int
    {
        return $this->activeShareholdings()->sum('number_of_shares');
    }

    public function getTotalSharesByClass(int $shareClassId): int
    {
        return $this->activeShareholdings()
            ->where('share_class_id', $shareClassId)
            ->sum('number_of_shares');
    }

    public function getOwnershipPercentage(): float
    {
        $totalCompanyShares = ShareClass::where('is_active', true)->sum('total_shares');

        if ($totalCompanyShares === 0) {
            return 0;
        }

        return round(($this->getTotalShares() / $totalCompanyShares) * 100, 2);
    }

    public function getVotingPercentage(): float
    {
        $shareholdings = $this->activeShareholdings()->with('shareClass')->get();

        $totalVotes = ShareClass::where('is_active', true)
            ->where('has_voting_rights', true)
            ->get()
            ->sum(fn ($class) => $class->total_shares * $class->voting_weight);

        if ($totalVotes === 0) {
            return 0;
        }

        $shareholderVotes = $shareholdings
            ->filter(fn ($sh) => $sh->shareClass->has_voting_rights)
            ->sum(fn ($sh) => $sh->number_of_shares * $sh->shareClass->voting_weight);

        return round(($shareholderVotes / $totalVotes) * 100, 2);
    }

    public function getTotalAcquisitionCost(): float
    {
        return $this->activeShareholdings()->sum('acquisition_cost') ?? 0;
    }

    public function isNorwegian(): bool
    {
        return $this->country_code === 'NO';
    }
}
