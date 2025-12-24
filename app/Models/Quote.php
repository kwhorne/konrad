<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'quote_number',
        'title',
        'description',
        'contact_id',
        'project_id',
        'quote_status_id',
        'created_by',
        'quote_date',
        'valid_until',
        'payment_terms_days',
        'terms_conditions',
        'internal_notes',
        'customer_name',
        'customer_address',
        'customer_postal_code',
        'customer_city',
        'customer_country',
        'subtotal',
        'discount_total',
        'vat_total',
        'total',
        'is_active',
        'sent_at',
        'sort_order',
    ];

    protected $casts = [
        'quote_date' => 'date',
        'valid_until' => 'date',
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
        static::creating(function (Quote $quote) {
            if (empty($quote->quote_number)) {
                $quote->quote_number = static::generateQuoteNumber();
            }
            if (empty($quote->quote_date)) {
                $quote->quote_date = now();
            }
        });
    }

    public static function generateQuoteNumber(): string
    {
        $year = date('Y');
        $lastQuote = static::withTrashed()
            ->whereYear('created_at', $year)
            ->orderByDesc('id')
            ->first();

        if ($lastQuote && preg_match('/T-'.$year.'-(\d+)/', $lastQuote->quote_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('T-%s-%04d', $year, $nextNumber);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function quoteStatus(): BelongsTo
    {
        return $this->belongsTo(QuoteStatus::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(QuoteLine::class)->orderBy('sort_order');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
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

    public function copyCustomerFromContact(): void
    {
        if ($this->contact) {
            $this->update([
                'customer_name' => $this->contact->company_name ?? $this->contact->name,
                'customer_address' => $this->contact->address,
                'customer_postal_code' => $this->contact->postal_code,
                'customer_city' => $this->contact->city,
                'customer_country' => $this->contact->country,
            ]);
        }
    }

    public function convertToOrder(): Order
    {
        $order = Order::create([
            'title' => $this->title,
            'description' => $this->description,
            'contact_id' => $this->contact_id,
            'project_id' => $this->project_id,
            'quote_id' => $this->id,
            'created_by' => auth()->id(),
            'order_date' => now(),
            'payment_terms_days' => $this->payment_terms_days,
            'terms_conditions' => $this->terms_conditions,
            'customer_name' => $this->customer_name,
            'customer_address' => $this->customer_address,
            'customer_postal_code' => $this->customer_postal_code,
            'customer_city' => $this->customer_city,
            'customer_country' => $this->customer_country,
            'subtotal' => $this->subtotal,
            'discount_total' => $this->discount_total,
            'vat_total' => $this->vat_total,
            'total' => $this->total,
        ]);

        foreach ($this->lines as $line) {
            OrderLine::create([
                'order_id' => $order->id,
                'quote_line_id' => $line->id,
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

        // Update quote status to converted
        $convertedStatus = QuoteStatus::where('code', 'converted')->first();
        if ($convertedStatus) {
            $this->update(['quote_status_id' => $convertedStatus->id]);
        }

        return $order;
    }

    public function getIsExpiredAttribute(): bool
    {
        if (! $this->valid_until) {
            return false;
        }

        return $this->valid_until->isPast();
    }

    public function getCanConvertAttribute(): bool
    {
        $status = $this->quoteStatus?->code;

        return in_array($status, ['sent', 'accepted']) && ! $this->is_expired;
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->quoteStatus?->name ?? 'Ukjent';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderByDesc('created_at');
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('valid_until')
            ->where('valid_until', '<', now());
    }

    public function scopeValid($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('valid_until')
                ->orWhere('valid_until', '>=', now());
        });
    }
}
