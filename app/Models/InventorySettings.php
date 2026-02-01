<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventorySettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'inventory_account_id',
        'cogs_account_id',
        'grni_account_id',
        'inventory_adjustment_account_id',
        'default_stock_location_id',
        'auto_reserve_on_order',
        'allow_negative_stock',
    ];

    protected $casts = [
        'auto_reserve_on_order' => 'boolean',
        'allow_negative_stock' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function inventoryAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'inventory_account_id');
    }

    public function cogsAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'cogs_account_id');
    }

    public function grniAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'grni_account_id');
    }

    public function inventoryAdjustmentAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'inventory_adjustment_account_id');
    }

    public function defaultStockLocation(): BelongsTo
    {
        return $this->belongsTo(StockLocation::class, 'default_stock_location_id');
    }

    public static function forCompany(int $companyId): ?self
    {
        return static::where('company_id', $companyId)->first();
    }

    public static function getOrCreate(int $companyId): self
    {
        return static::firstOrCreate(
            ['company_id' => $companyId],
            [
                'auto_reserve_on_order' => true,
                'allow_negative_stock' => false,
            ]
        );
    }
}
