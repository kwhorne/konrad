<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

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
            if (empty($invoice->due_date) || empty($invoice->reminder_date)) {
                $invoiceService = app(\App\Services\InvoiceService::class);
                $dates = $invoiceService->calculateInvoiceDates(
                    $invoice->invoice_date,
                    $invoice->payment_terms_days,
                    $invoice->reminder_days
                );
                if (empty($invoice->due_date)) {
                    $invoice->due_date = $dates['due_date'];
                }
                if (empty($invoice->reminder_date) && $dates['reminder_date']) {
                    $invoice->reminder_date = $dates['reminder_date'];
                }
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
        app(\App\Services\InvoiceService::class)->recalculateTotals($this);
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
        return app(\App\Services\InvoiceService::class)->createCreditNote($this);
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
