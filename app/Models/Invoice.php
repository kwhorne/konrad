<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'invoice_type',
        'title',
        'description',
        'contact_id',
        'project_id',
        'order_id',
        'original_invoice_id',
        'invoice_status_id',
        'created_by',
        'invoice_date',
        'due_date',
        'payment_terms_days',
        'reminder_days',
        'reminder_date',
        'sent_at',
        'paid_at',
        'terms_conditions',
        'internal_notes',
        'customer_name',
        'customer_address',
        'customer_postal_code',
        'customer_city',
        'customer_country',
        'our_reference',
        'customer_reference',
        'subtotal',
        'discount_total',
        'vat_total',
        'total',
        'paid_amount',
        'balance',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'reminder_date' => 'date',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime',
        'payment_terms_days' => 'integer',
        'reminder_days' => 'integer',
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'vat_total' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = static::generateInvoiceNumber($invoice->invoice_type);
            }
            if (empty($invoice->invoice_date)) {
                $invoice->invoice_date = now();
            }
            if (empty($invoice->due_date)) {
                $paymentTerms = $invoice->payment_terms_days ?? 14;
                $invoice->due_date = $invoice->invoice_date->copy()->addDays($paymentTerms);
            }
            if (empty($invoice->reminder_date) && $invoice->due_date && $invoice->reminder_days) {
                $invoice->reminder_date = $invoice->due_date->copy()->addDays($invoice->reminder_days);
            }
            if ($invoice->balance === null) {
                $invoice->balance = $invoice->total ?? 0;
            }
        });
    }

    public static function generateInvoiceNumber(string $type = 'invoice'): string
    {
        $prefix = $type === 'credit_note' ? 'K' : 'F';
        $year = date('Y');

        $lastInvoice = static::withTrashed()
            ->where('invoice_type', $type)
            ->whereYear('created_at', $year)
            ->orderByDesc('id')
            ->first();

        $pattern = '/'.$prefix.'-'.$year.'-(\d+)/';
        if ($lastInvoice && preg_match($pattern, $lastInvoice->invoice_number, $matches)) {
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

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function originalInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'original_invoice_id');
    }

    public function creditNotes(): HasMany
    {
        return $this->hasMany(Invoice::class, 'original_invoice_id');
    }

    public function invoiceStatus(): BelongsTo
    {
        return $this->belongsTo(InvoiceStatus::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class)->orderBy('sort_order');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class)->orderBy('payment_date');
    }

    public function recalculateTotals(): void
    {
        $subtotal = 0;
        $discountTotal = 0;
        $vatTotal = 0;

        foreach ($this->lines as $line) {
            $lineSubtotal = $line->quantity * $line->unit_price;
            $lineDiscount = $lineSubtotal * ($line->discount_percent / 100);
            $lineNet = $lineSubtotal - $lineDiscount;
            $lineVat = $lineNet * ($line->vat_percent / 100);

            $subtotal += $lineSubtotal;
            $discountTotal += $lineDiscount;
            $vatTotal += $lineVat;
        }

        $total = $subtotal - $discountTotal + $vatTotal;

        $this->update([
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'vat_total' => $vatTotal,
            'total' => $total,
            'balance' => $total - $this->paid_amount,
        ]);
    }

    public function updatePaidAmount(): void
    {
        $paidAmount = $this->payments()->sum('amount');

        $this->update([
            'paid_amount' => $paidAmount,
            'balance' => $this->total - $paidAmount,
            'paid_at' => $paidAmount >= $this->total ? now() : null,
        ]);

        // Update status based on payment
        $this->updatePaymentStatus();
    }

    protected function updatePaymentStatus(): void
    {
        if ($this->balance <= 0) {
            $paidStatus = InvoiceStatus::where('code', 'paid')->first();
            if ($paidStatus) {
                $this->update(['invoice_status_id' => $paidStatus->id]);
            }
        } elseif ($this->paid_amount > 0) {
            $partialStatus = InvoiceStatus::where('code', 'partially_paid')->first();
            if ($partialStatus) {
                $this->update(['invoice_status_id' => $partialStatus->id]);
            }
        }
    }

    public function createCreditNote(): Invoice
    {
        $creditNote = Invoice::create([
            'invoice_type' => 'credit_note',
            'title' => 'Kreditnota for '.$this->invoice_number,
            'description' => $this->description,
            'contact_id' => $this->contact_id,
            'project_id' => $this->project_id,
            'original_invoice_id' => $this->id,
            'created_by' => auth()->id(),
            'invoice_date' => now(),
            'due_date' => now(),
            'payment_terms_days' => 0,
            'customer_name' => $this->customer_name,
            'customer_address' => $this->customer_address,
            'customer_postal_code' => $this->customer_postal_code,
            'customer_city' => $this->customer_city,
            'customer_country' => $this->customer_country,
            'customer_reference' => $this->customer_reference,
            'subtotal' => -$this->subtotal,
            'discount_total' => -$this->discount_total,
            'vat_total' => -$this->vat_total,
            'total' => -$this->total,
            'paid_amount' => -$this->total,
            'balance' => 0,
        ]);

        foreach ($this->lines as $line) {
            InvoiceLine::create([
                'invoice_id' => $creditNote->id,
                'product_id' => $line->product_id,
                'description' => $line->description,
                'quantity' => -$line->quantity,
                'unit' => $line->unit,
                'unit_price' => $line->unit_price,
                'discount_percent' => $line->discount_percent,
                'vat_rate_id' => $line->vat_rate_id,
                'vat_percent' => $line->vat_percent,
                'sort_order' => $line->sort_order,
            ]);
        }

        // Update original invoice status to credited
        $creditedStatus = InvoiceStatus::where('code', 'credited')->first();
        if ($creditedStatus) {
            $this->update(['invoice_status_id' => $creditedStatus->id]);
        }

        return $creditNote;
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->balance <= 0;
    }

    public function getIsOverdueAttribute(): bool
    {
        if ($this->is_paid || ! $this->due_date) {
            return false;
        }

        return $this->due_date->isPast();
    }

    public function getNeedsReminderAttribute(): bool
    {
        if ($this->is_paid || ! $this->reminder_date) {
            return false;
        }

        return $this->reminder_date->isPast() && ! $this->is_paid;
    }

    public function getIsCreditNoteAttribute(): bool
    {
        return $this->invoice_type === 'credit_note';
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->invoiceStatus?->name ?? 'Ukjent';
    }

    public function getPaymentProgressAttribute(): float
    {
        if ($this->total <= 0) {
            return 100;
        }

        return min(100, ($this->paid_amount / $this->total) * 100);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderByDesc('created_at');
    }

    public function scopeInvoices($query)
    {
        return $query->where('invoice_type', 'invoice');
    }

    public function scopeCreditNotes($query)
    {
        return $query->where('invoice_type', 'credit_note');
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

    public function scopeNeedsReminder($query)
    {
        return $query->where('balance', '>', 0)
            ->whereNotNull('reminder_date')
            ->where('reminder_date', '<=', now());
    }
}
