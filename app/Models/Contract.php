<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'contract_number',
        'title',
        'description',
        'established_date',
        'start_date',
        'end_date',
        'duration_months',
        'notice_date',
        'notice_period_days',
        'company_name',
        'company_contact',
        'company_email',
        'company_phone',
        'department',
        'group',
        'asset_reference',
        'type',
        'status',
        'value',
        'currency',
        'payment_frequency',
        'auto_renewal',
        'renewal_period_months',
        'notes',
        'attachments',
        'created_by',
        'responsible_user_id',
    ];

    protected $casts = [
        'established_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'notice_date' => 'date',
        'value' => 'decimal:2',
        'auto_renewal' => 'boolean',
        'attachments' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function getDaysUntilExpiryAttribute(): int
    {
        return now()->diffInDays($this->end_date, false);
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->days_until_expiry <= 90 && $this->days_until_expiry > 0;
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->days_until_expiry < 0;
    }

    public function getFormattedValueAttribute(): string
    {
        if (! $this->value) {
            return '-';
        }

        return number_format($this->value, 2, ',', ' ').' '.$this->currency;
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'active' => 'success',
            'draft' => 'warning',
            'expiring_soon' => 'warning',
            'expired' => 'danger',
            'terminated' => 'danger',
            'renewed' => 'primary',
            default => 'outline',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'service' => 'Tjeneste',
            'lease' => 'Leie',
            'maintenance' => 'Vedlikehold',
            'software' => 'Programvare',
            'insurance' => 'Forsikring',
            'employment' => 'Ansettelse',
            'supplier' => 'Leverandør',
            'other' => 'Annet',
            default => $this->type,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Utkast',
            'active' => 'Aktiv',
            'expiring_soon' => 'Utgår snart',
            'expired' => 'Utgått',
            'terminated' => 'Avsluttet',
            'renewed' => 'Fornyet',
            default => $this->status,
        };
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($contract) {
            if (! $contract->contract_number) {
                $contract->contract_number = 'K-'.date('Y').'-'.str_pad(static::whereYear('created_at', date('Y'))->count() + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
