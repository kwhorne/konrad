<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'module_id',
        'is_enabled',
        'enabled_at',
        'expires_at',
        'enabled_by',
        'stripe_subscription_id',
        'stripe_subscription_status',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'enabled_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function isActive(): bool
    {
        if (! $this->is_enabled) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function enabledByAdmin(): bool
    {
        return $this->enabled_by === 'admin';
    }

    public function enabledByStripe(): bool
    {
        return $this->enabled_by === 'stripe';
    }
}
