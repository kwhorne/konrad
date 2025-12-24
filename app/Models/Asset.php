<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'asset_number',
        'title',
        'description',
        'serial_number',
        'asset_model',
        'purchase_price',
        'currency',
        'purchase_date',
        'supplier',
        'manufacturer',
        'location',
        'department',
        'group',
        'insurance_number',
        'invoice_number',
        'invoice_date',
        'warranty_from',
        'warranty_until',
        'status',
        'condition',
        'is_active',
        'notes',
        'attachments',
        'images',
        'created_by',
        'responsible_user_id',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'invoice_date' => 'date',
        'warranty_from' => 'date',
        'warranty_until' => 'date',
        'purchase_price' => 'decimal:2',
        'is_active' => 'boolean',
        'attachments' => 'array',
        'images' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function getFormattedPriceAttribute(): string
    {
        if (! $this->purchase_price) {
            return '-';
        }

        return number_format($this->purchase_price, 2, ',', ' ').' '.$this->currency;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'in_use' => 'I bruk',
            'available' => 'Tilgjengelig',
            'maintenance' => 'Vedlikehold',
            'retired' => 'Utfaset',
            'lost' => 'Tapt',
            'sold' => 'Solgt',
            default => $this->status,
        };
    }

    public function getConditionLabelAttribute(): string
    {
        return match ($this->condition) {
            'excellent' => 'Utmerket',
            'good' => 'God',
            'fair' => 'Akseptabel',
            'poor' => 'Dårlig',
            'broken' => 'Ødelagt',
            default => $this->condition,
        };
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'in_use' => 'success',
            'available' => 'primary',
            'maintenance' => 'warning',
            'retired' => 'outline',
            'lost' => 'danger',
            'sold' => 'outline',
            default => 'outline',
        };
    }

    public function getConditionBadgeColorAttribute(): string
    {
        return match ($this->condition) {
            'excellent' => 'success',
            'good' => 'primary',
            'fair' => 'warning',
            'poor' => 'danger',
            'broken' => 'danger',
            default => 'outline',
        };
    }

    public function getWarrantyStatusAttribute(): ?string
    {
        if (! $this->warranty_until) {
            return null;
        }

        if ($this->warranty_until->isPast()) {
            return 'expired';
        }

        if ($this->warranty_until->diffInDays(now()) <= 30) {
            return 'expiring_soon';
        }

        return 'active';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($asset) {
            if (! $asset->asset_number) {
                $asset->asset_number = 'A-'.date('Y').'-'.str_pad(static::whereYear('created_at', date('Y'))->count() + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
