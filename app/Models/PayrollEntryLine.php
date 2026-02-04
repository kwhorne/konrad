<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollEntryLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_entry_id',
        'pay_type_id',
        'description',
        'quantity',
        'rate',
        'amount',
        'timesheet_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'rate' => 'decimal:2',
            'amount' => 'decimal:2',
        ];
    }

    /**
     * Get the payroll entry this line belongs to.
     */
    public function payrollEntry(): BelongsTo
    {
        return $this->belongsTo(PayrollEntry::class);
    }

    /**
     * Get the pay type for this line.
     */
    public function payType(): BelongsTo
    {
        return $this->belongsTo(PayType::class);
    }

    /**
     * Get the linked timesheet (if any).
     */
    public function timesheet(): BelongsTo
    {
        return $this->belongsTo(Timesheet::class);
    }

    /**
     * Calculate the amount based on quantity and rate.
     */
    public function calculateAmount(): float
    {
        return $this->quantity * $this->rate;
    }

    /**
     * Check if this line is a deduction.
     */
    public function getIsDeductionAttribute(): bool
    {
        return $this->payType?->category === PayType::CATEGORY_TREKK;
    }
}
