<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeePayrollSettings extends Model
{
    use BelongsToCompany, HasFactory;

    public const LONN_TYPE_FAST = 'fast';

    public const LONN_TYPE_TIME = 'time';

    public const SKATT_TYPE_TABELLTREKK = 'tabelltrekk';

    public const SKATT_TYPE_PROSENTTREKK = 'prosenttrekk';

    public const SKATT_TYPE_KILDESKATT = 'kildeskatt';

    public const SKATT_TYPE_FRIKORT = 'frikort';

    protected $fillable = [
        'user_id',
        'ansattnummer',
        'personnummer',
        'personal_email',
        'phone',
        'address_street',
        'address_postal_code',
        'address_city',
        'address_country',
        'birth_date',
        'emergency_contact_name',
        'emergency_contact_relation',
        'emergency_contact_phone',
        'ansatt_fra',
        'ansatt_til',
        'stillingsprosent',
        'stilling',
        'lonn_type',
        'maanedslonn',
        'timelonn',
        'aarslonn',
        'skatt_type',
        'skattetabell',
        'skatteprosent',
        'frikort_belop',
        'frikort_brukt',
        'skattekort_gyldig_fra',
        'skattekort_gyldig_til',
        'skattekort_hentet_at',
        'skattekort_data',
        'feriepenger_prosent',
        'ferie_5_uker',
        'over_60',
        'otp_enabled',
        'otp_prosent',
        'kontonummer',
        'aa_arbeidsforhold_id',
        'aa_synced_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'ansatt_fra' => 'date',
            'ansatt_til' => 'date',
            'stillingsprosent' => 'decimal:2',
            'maanedslonn' => 'decimal:2',
            'timelonn' => 'decimal:2',
            'aarslonn' => 'decimal:2',
            'skatteprosent' => 'decimal:2',
            'frikort_belop' => 'decimal:2',
            'frikort_brukt' => 'decimal:2',
            'skattekort_gyldig_fra' => 'date',
            'skattekort_gyldig_til' => 'date',
            'skattekort_hentet_at' => 'datetime',
            'skattekort_data' => 'array',
            'feriepenger_prosent' => 'decimal:2',
            'ferie_5_uker' => 'boolean',
            'over_60' => 'boolean',
            'otp_enabled' => 'boolean',
            'otp_prosent' => 'decimal:2',
            'aa_synced_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the user (employee) for these settings.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all payroll entries for this employee.
     */
    public function payrollEntries(): HasMany
    {
        return $this->hasMany(PayrollEntry::class, 'user_id', 'user_id');
    }

    /**
     * Get all holiday pay balances for this employee.
     */
    public function holidayPayBalances(): HasMany
    {
        return $this->hasMany(HolidayPayBalance::class, 'user_id', 'user_id');
    }

    /**
     * Calculate the effective feriepenger rate.
     */
    public function getEffectiveFeriepengerProsentAttribute(): float
    {
        $rate = $this->feriepenger_prosent;

        // 5 uker ferie gir 12% (eller 14.3% for over 60)
        if ($this->ferie_5_uker) {
            $rate = $this->over_60 ? 14.3 : 12.0;
        } elseif ($this->over_60) {
            $rate = 12.5; // 10.2% + 2.3% tillegg
        }

        return $rate;
    }

    /**
     * Get the label for salary type.
     */
    public function getLonnTypeLabelAttribute(): string
    {
        return match ($this->lonn_type) {
            self::LONN_TYPE_FAST => 'Fastlonn',
            self::LONN_TYPE_TIME => 'Timelonn',
            default => 'Ukjent',
        };
    }

    /**
     * Get the label for tax type.
     */
    public function getSkattTypeLabelAttribute(): string
    {
        return match ($this->skatt_type) {
            self::SKATT_TYPE_TABELLTREKK => 'Tabelltrekk',
            self::SKATT_TYPE_PROSENTTREKK => 'Prosenttrekk',
            self::SKATT_TYPE_KILDESKATT => 'Kildeskatt',
            self::SKATT_TYPE_FRIKORT => 'Frikort',
            default => 'Ukjent',
        };
    }

    /**
     * Check if the employee is currently employed.
     */
    public function getIsCurrentlyEmployedAttribute(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->ansatt_fra && $this->ansatt_fra->isFuture()) {
            return false;
        }

        if ($this->ansatt_til && $this->ansatt_til->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Get remaining frikort amount.
     */
    public function getRemainingFrikortAttribute(): float
    {
        if ($this->skatt_type !== self::SKATT_TYPE_FRIKORT || ! $this->frikort_belop) {
            return 0;
        }

        return max(0, $this->frikort_belop - $this->frikort_brukt);
    }

    /**
     * Get full address as a single string.
     */
    public function getFullAddressAttribute(): ?string
    {
        $parts = array_filter([
            $this->address_street,
            trim(($this->address_postal_code ?? '').' '.($this->address_city ?? '')),
        ]);

        return $parts ? implode(', ', $parts) : null;
    }

    /**
     * Calculate age from birth date.
     */
    public function getAgeAttribute(): ?int
    {
        return $this->birth_date?->age;
    }

    /**
     * Scope to get only active employees.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get currently employed.
     */
    public function scopeCurrentlyEmployed($query)
    {
        return $query->active()
            ->where(function ($q) {
                $q->whereNull('ansatt_fra')
                    ->orWhere('ansatt_fra', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ansatt_til')
                    ->orWhere('ansatt_til', '>=', now());
            });
    }
}
