<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VatReportLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'vat_report_id',
        'vat_code_id',
        'base_amount',
        'vat_rate',
        'vat_amount',
        'note',
        'is_manual_override',
        'sort_order',
    ];

    protected $casts = [
        'base_amount' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'is_manual_override' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function vatReport(): BelongsTo
    {
        return $this->belongsTo(VatReport::class);
    }

    public function vatCode(): BelongsTo
    {
        return $this->belongsTo(VatCode::class);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
