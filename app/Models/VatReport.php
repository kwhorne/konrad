<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VatReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'report_type',
        'period_type',
        'year',
        'period',
        'period_from',
        'period_to',
        'total_base',
        'total_output_vat',
        'total_input_vat',
        'vat_payable',
        'note',
        'status',
        'calculated_at',
        'submitted_at',
        'altinn_reference',
        'created_by',
        'submitted_by',
    ];

    protected $casts = [
        'period_from' => 'date',
        'period_to' => 'date',
        'total_base' => 'decimal:2',
        'total_output_vat' => 'decimal:2',
        'total_input_vat' => 'decimal:2',
        'vat_payable' => 'decimal:2',
        'calculated_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(VatReportLine::class)->orderBy('sort_order');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(VatReportAttachment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function getPeriodNameAttribute(): string
    {
        if ($this->period_type === 'bimonthly') {
            $periodNames = [
                1 => 'Januar - Februar',
                2 => 'Mars - April',
                3 => 'Mai - Juni',
                4 => 'Juli - August',
                5 => 'September - Oktober',
                6 => 'November - Desember',
            ];

            return ($periodNames[$this->period] ?? 'Ukjent').' '.$this->year;
        }

        if ($this->period_type === 'monthly') {
            $monthNames = [
                1 => 'Januar', 2 => 'Februar', 3 => 'Mars', 4 => 'April',
                5 => 'Mai', 6 => 'Juni', 7 => 'Juli', 8 => 'August',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
            ];

            return ($monthNames[$this->period] ?? 'Ukjent').' '.$this->year;
        }

        return $this->year;
    }

    public function getReportTypeNameAttribute(): string
    {
        return match ($this->report_type) {
            'alminnelig' => 'Alminnelig næring',
            'primaer' => 'Primærnæring',
            default => 'Ukjent',
        };
    }

    public function getPeriodTypeNameAttribute(): string
    {
        return match ($this->period_type) {
            'bimonthly' => 'Tomånedlig',
            'monthly' => 'Månedlig',
            'annual' => 'Årlig',
            default => 'Ukjent',
        };
    }

    public function getStatusNameAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Utkast',
            'calculated' => 'Beregnet',
            'submitted' => 'Sendt',
            'accepted' => 'Godkjent',
            'rejected' => 'Avvist',
            default => 'Ukjent',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'zinc',
            'calculated' => 'yellow',
            'submitted' => 'blue',
            'accepted' => 'green',
            'rejected' => 'red',
            default => 'zinc',
        };
    }

    public function recalculateTotals(): void
    {
        $outputVat = $this->lines()
            ->whereHas('vatCode', fn ($q) => $q->where('direction', 'output'))
            ->sum('vat_amount');

        $inputVat = $this->lines()
            ->whereHas('vatCode', fn ($q) => $q->where('direction', 'input'))
            ->sum('vat_amount');

        $totalBase = $this->lines()->sum('base_amount');

        $this->update([
            'total_base' => $totalBase,
            'total_output_vat' => $outputVat,
            'total_input_vat' => abs($inputVat),
            'vat_payable' => $outputVat + $inputVat, // input is already negative
            'calculated_at' => now(),
            'status' => 'calculated',
        ]);
    }

    public function scopeByYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOrdered($query)
    {
        return $query->orderByDesc('year')->orderByDesc('period');
    }

    public static function getBimonthlyPeriodDates(int $year, int $period): array
    {
        $periodMonths = [
            1 => [1, 2],
            2 => [3, 4],
            3 => [5, 6],
            4 => [7, 8],
            5 => [9, 10],
            6 => [11, 12],
        ];

        $months = $periodMonths[$period] ?? [1, 2];

        return [
            'from' => \Carbon\Carbon::create($year, $months[0], 1)->startOfMonth(),
            'to' => \Carbon\Carbon::create($year, $months[1], 1)->endOfMonth(),
        ];
    }
}
