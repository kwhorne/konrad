<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'title',
        'description',
        'contact_id',
        'project_id',
        'quote_id',
        'order_status_id',
        'created_by',
        'order_date',
        'delivery_date',
        'customer_reference',
        'payment_terms_days',
        'terms_conditions',
        'internal_notes',
        'customer_name',
        'customer_address',
        'customer_postal_code',
        'customer_city',
        'customer_country',
        'delivery_address',
        'delivery_postal_code',
        'delivery_city',
        'delivery_country',
        'subtotal',
        'discount_total',
        'vat_total',
        'total',
        'is_active',
        'sent_at',
        'sort_order',
    ];

    protected $casts = [
        'order_date' => 'date',
        'delivery_date' => 'date',
        'sent_at' => 'datetime',
        'payment_terms_days' => 'integer',
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'vat_total' => 'decimal:2',
        'total' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }
            if (empty($order->order_date)) {
                $order->order_date = now();
            }
        });
    }

    public static function generateOrderNumber(): string
    {
        $year = date('Y');
        $lastOrder = static::withTrashed()
            ->whereYear('created_at', $year)
            ->orderByDesc('id')
            ->first();

        if ($lastOrder && preg_match('/O-'.$year.'-(\d+)/', $lastOrder->order_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('O-%s-%04d', $year, $nextNumber);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function orderStatus(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(OrderLine::class)->orderBy('sort_order');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
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

        $this->update([
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'vat_total' => $vatTotal,
            'total' => $subtotal - $discountTotal + $vatTotal,
        ]);
    }

    public function convertToInvoice(): Invoice
    {
        $dueDate = now()->addDays($this->payment_terms_days);
        $reminderDays = 14;

        $invoice = Invoice::create([
            'invoice_type' => 'invoice',
            'title' => $this->title,
            'description' => $this->description,
            'contact_id' => $this->contact_id,
            'project_id' => $this->project_id,
            'order_id' => $this->id,
            'created_by' => auth()->id(),
            'invoice_date' => now(),
            'due_date' => $dueDate,
            'payment_terms_days' => $this->payment_terms_days,
            'reminder_days' => $reminderDays,
            'reminder_date' => $dueDate->copy()->addDays($reminderDays),
            'terms_conditions' => $this->terms_conditions,
            'customer_name' => $this->customer_name,
            'customer_address' => $this->customer_address,
            'customer_postal_code' => $this->customer_postal_code,
            'customer_city' => $this->customer_city,
            'customer_country' => $this->customer_country,
            'customer_reference' => $this->customer_reference,
            'subtotal' => $this->subtotal,
            'discount_total' => $this->discount_total,
            'vat_total' => $this->vat_total,
            'total' => $this->total,
            'balance' => $this->total,
        ]);

        foreach ($this->lines as $line) {
            InvoiceLine::create([
                'invoice_id' => $invoice->id,
                'order_line_id' => $line->id,
                'product_id' => $line->product_id,
                'description' => $line->description,
                'quantity' => $line->quantity,
                'unit' => $line->unit,
                'unit_price' => $line->unit_price,
                'discount_percent' => $line->discount_percent,
                'vat_rate_id' => $line->vat_rate_id,
                'vat_percent' => $line->vat_percent,
                'sort_order' => $line->sort_order,
            ]);
        }

        // Update order status to invoiced
        $invoicedStatus = OrderStatus::where('code', 'invoiced')->first();
        if ($invoicedStatus) {
            $this->update(['order_status_id' => $invoicedStatus->id]);
        }

        return $invoice;
    }

    public function getCanConvertAttribute(): bool
    {
        $status = $this->orderStatus?->code;

        return in_array($status, ['confirmed', 'in_progress', 'completed']);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->orderStatus?->name ?? 'Ukjent';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderByDesc('created_at');
    }
}
