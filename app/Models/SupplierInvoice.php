<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierInvoice extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'internal_number',
        'contact_id',
        'department_id',
        'invoice_date',
        'due_date',
        'subtotal',
        'vat_total',
        'total',
        'paid_amount',
        'balance',
        'status',
        'voucher_id',
        'description',
        'attachment',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'vat_total' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (SupplierInvoice $invoice) {
            if (empty($invoice->internal_number)) {
                $invoice->internal_number = static::generateInternalNumber();
            }
            if ($invoice->balance === null) {
                $invoice->balance = $invoice->total ?? 0;
            }
        });
    }

    public static function generateInternalNumber(): string
    {
        $prefix = 'LF';
        $year = date('Y');

        $lastInvoice = static::withTrashed()
            ->whereYear('created_at', $year)
            ->orderByDesc('id')
            ->first();

        $pattern = '/'.$prefix.'-'.$year.'-(\d+)/';
        if ($lastInvoice && preg_match($pattern, $lastInvoice->internal_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $year, $nextNumber);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(SupplierInvoiceLine::class)->orderBy('sort_order');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class)->orderBy('payment_date');
    }

    public function recalculateTotals(): void
    {
        $subtotal = 0;
        $vatTotal = 0;

        foreach ($this->lines as $line) {
            $lineSubtotal = $line->quantity * $line->unit_price;
            $lineVat = $lineSubtotal * ($line->vat_percent / 100);

            $subtotal += $lineSubtotal;
            $vatTotal += $lineVat;
        }

        $total = $subtotal + $vatTotal;

        $this->update([
            'subtotal' => $subtotal,
            'vat_total' => $vatTotal,
            'total' => $total,
            'balance' => $total - $this->paid_amount,
        ]);
    }

    public function updatePaidAmount(): void
    {
        $paidAmount = $this->payments()->sum('amount');

        $newStatus = $this->status;
        if ($paidAmount >= $this->total && $this->total > 0) {
            $newStatus = 'paid';
        } elseif ($paidAmount > 0) {
            $newStatus = 'partially_paid';
        }

        $this->update([
            'paid_amount' => $paidAmount,
            'balance' => $this->total - $paidAmount,
            'status' => $newStatus,
        ]);
    }

    public function approve(): bool
    {
        if ($this->status !== 'draft') {
            return false;
        }

        $this->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return true;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Utkast',
            'approved' => 'Godkjent',
            'paid' => 'Betalt',
            'partially_paid' => 'Delvis betalt',
            default => 'Ukjent',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'zinc',
            'approved' => 'blue',
            'paid' => 'green',
            'partially_paid' => 'yellow',
            default => 'zinc',
        };
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->balance <= 0 && $this->total > 0;
    }

    public function getIsOverdueAttribute(): bool
    {
        if ($this->is_paid || ! $this->due_date) {
            return false;
        }

        return $this->due_date->isPast();
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('balance', '>', 0);
    }

    public function scopeOverdue($query)
    {
        return $query->where('balance', '>', 0)
            ->whereNotNull('due_date')
            ->where('due_date', '<', now());
    }

    public function scopeOrdered($query)
    {
        return $query->orderByDesc('invoice_date')->orderByDesc('id');
    }
}
