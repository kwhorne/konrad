<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'is_premium',
        'price_monthly',
        'stripe_price_id',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_premium' => 'boolean',
            'is_active' => 'boolean',
            'price_monthly' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_modules')
            ->withPivot(['is_enabled', 'enabled_at', 'expires_at', 'enabled_by', 'stripe_subscription_id', 'stripe_subscription_status'])
            ->withTimestamps();
    }

    public function companyModules(): HasMany
    {
        return $this->hasMany(CompanyModule::class);
    }

    public function getPriceFormattedAttribute(): string
    {
        return number_format($this->price_monthly / 100, 0, ',', ' ').' kr/mnd';
    }

    public function scopePremium($query)
    {
        return $query->where('is_premium', true);
    }

    public function scopeStandard($query)
    {
        return $query->where('is_premium', false);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
