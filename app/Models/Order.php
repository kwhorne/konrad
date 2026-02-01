<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'title',
        'description',
        'contact_id',
        'project_id',
        'department_id',
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

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
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

    public function convertToInvoice(): Invoice
    {
        return app(\App\Services\DocumentConversionService::class)->convertOrderToInvoice($this);
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
