<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'invoice_number',
        'description',
        'amount',
        'due_date',
        'sent_at',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'due_date' => 'date',
            'sent_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function isPaid(): bool
    {
        return $this->paid_at !== null;
    }

    public function isOverdue(): bool
    {
        return $this->paid_at === null && $this->due_date->isPast();
    }

    public function getStatusAttribute(): string
    {
        if ($this->isPaid()) {
            return 'paid';
        }

        if ($this->isOverdue()) {
            return 'overdue';
        }

        return 'pending';
    }

    public function getAmountFormattedAttribute(): string
    {
        return number_format($this->amount / 100, 0, ',', ' ').' kr';
    }

    /**
     * Generate the next invoice number for a given year.
     */
    public static function generateInvoiceNumber(): string
    {
        $year = now()->year;
        $prefix = 'KON-'.$year.'-';

        $last = static::where('invoice_number', 'like', $prefix.'%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $next = $last ? (int) substr($last, strlen($prefix)) + 1 : 1;

        return $prefix.str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function scopeUnpaid($query)
    {
        return $query->whereNull('paid_at');
    }

    public function scopeOverdue($query)
    {
        return $query->whereNull('paid_at')->where('due_date', '<', now());
    }
}
