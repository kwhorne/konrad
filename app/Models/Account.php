<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'account_number',
        'name',
        'account_class',
        'account_type',
        'parent_id',
        'is_system',
        'is_active',
        'description',
        'vat_code',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id')->orderBy('account_number');
    }

    public function voucherLines(): HasMany
    {
        return $this->hasMany(VoucherLine::class);
    }

    public function supplierInvoiceLines(): HasMany
    {
        return $this->hasMany(SupplierInvoiceLine::class);
    }

    public function getBalanceAttribute(): float
    {
        $totals = $this->voucherLines()
            ->whereHas('voucher', fn ($q) => $q->where('is_posted', true))
            ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->first();

        $debit = $totals->total_debit ?? 0;
        $credit = $totals->total_credit ?? 0;

        // Debit-normal accounts (assets, expenses): balance = debit - credit
        // Credit-normal accounts (liabilities, equity, revenue): balance = credit - debit
        if (in_array($this->account_type, ['asset', 'expense'])) {
            return $debit - $credit;
        }

        return $credit - $debit;
    }

    public function getClassNameAttribute(): string
    {
        return config("accounting.account_classes.{$this->account_class}.name", 'Ukjent');
    }

    public function getReportCategoryAttribute(): ?string
    {
        return config("accounting.account_classes.{$this->account_class}.report_category");
    }

    public function getTypeNameAttribute(): string
    {
        return match ($this->account_type) {
            'asset' => 'Eiendel',
            'liability' => 'Gjeld',
            'equity' => 'Egenkapital',
            'revenue' => 'Inntekt',
            'expense' => 'Kostnad',
            default => 'Ukjent',
        };
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByClass($query, string $class)
    {
        return $query->where('account_class', $class);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('account_type', $type);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('account_number');
    }

    public function scopeRootLevel($query)
    {
        return $query->whereNull('parent_id');
    }
}
