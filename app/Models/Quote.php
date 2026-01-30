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
        $totalsService = app(\App\Services\DocumentTotalsService::class);
        $this->load('lines');
        $totals = $totalsService->calculate($this->lines);

        $this->update([
            'subtotal' => $totals['subtotal'],
            'discount_total' => $totals['discount_total'],
            'vat_total' => $totals['vat_total'],
            'total' => $totals['total'],
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
        return app(\App\Services\DocumentConversionService::class)->convertQuoteToOrder($this);
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
