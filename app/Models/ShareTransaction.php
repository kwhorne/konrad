<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShareTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'transaction_date',
        'transaction_type',
        'share_class_id',
        'from_shareholder_id',
        'to_shareholder_id',
        'number_of_shares',
        'price_per_share',
        'total_amount',
        'currency',
        'description',
        'document_reference',
        'metadata',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'number_of_shares' => 'integer',
            'price_per_share' => 'decimal:4',
            'total_amount' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->transaction_number)) {
                $transaction->transaction_number = self::generateTransactionNumber();
            }
        });
    }

    public static function generateTransactionNumber(): string
    {
        $prefix = 'AKS';
        $year = date('Y');
        $lastTransaction = self::whereYear('transaction_date', $year)
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastTransaction
            ? (int) substr($lastTransaction->transaction_number, -4) + 1
            : 1;

        return $prefix.'-'.$year.'-'.str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    // Relationships
    public function shareClass(): BelongsTo
    {
        return $this->belongsTo(ShareClass::class);
    }

    public function fromShareholder(): BelongsTo
    {
        return $this->belongsTo(Shareholder::class, 'from_shareholder_id');
    }

    public function toShareholder(): BelongsTo
    {
        return $this->belongsTo(Shareholder::class, 'to_shareholder_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeOfType($query, string $type)
    {
        return $query->where('transaction_type', $type);
    }

    public function scopeInYear($query, int $year)
    {
        return $query->whereYear('transaction_date', $year);
    }

    public function scopeBetween($query, $from, $to)
    {
        return $query->whereBetween('transaction_date', [$from, $to]);
    }

    public function scopeForShareholder($query, int $shareholderId)
    {
        return $query->where(function ($q) use ($shareholderId) {
            $q->where('from_shareholder_id', $shareholderId)
                ->orWhere('to_shareholder_id', $shareholderId);
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('transaction_date', 'desc')->orderBy('id', 'desc');
    }

    // Accessors
    public function getTransactionTypeLabel(): string
    {
        return match ($this->transaction_type) {
            'issue' => 'Emisjon',
            'transfer' => 'Overdragelse',
            'redemption' => 'Innløsning',
            'split' => 'Aksjesplitt',
            'merger' => 'Fusjon',
            'bonus' => 'Fondsemisjon',
            default => $this->transaction_type,
        };
    }

    public function getTransactionTypeBadgeColor(): string
    {
        return match ($this->transaction_type) {
            'issue' => 'success',
            'transfer' => 'info',
            'redemption' => 'warning',
            'split' => 'primary',
            'merger' => 'danger',
            'bonus' => 'success',
            default => 'outline',
        };
    }

    public function getFormattedPricePerShare(): string
    {
        if (! $this->price_per_share) {
            return '-';
        }

        return number_format($this->price_per_share, 4, ',', ' ').' '.$this->currency;
    }

    public function getFormattedTotalAmount(): string
    {
        if (! $this->total_amount) {
            return '-';
        }

        return number_format($this->total_amount, 2, ',', ' ').' '.$this->currency;
    }

    public function getTransactionDescription(): string
    {
        $parts = [];

        switch ($this->transaction_type) {
            case 'issue':
                $parts[] = "Emisjon til {$this->toShareholder?->name}";
                break;
            case 'transfer':
                $parts[] = "Overdragelse fra {$this->fromShareholder?->name} til {$this->toShareholder?->name}";
                break;
            case 'redemption':
                $parts[] = "Innløsning fra {$this->fromShareholder?->name}";
                break;
            case 'split':
                $parts[] = 'Aksjesplitt';
                break;
            case 'merger':
                $parts[] = 'Fusjon';
                break;
            case 'bonus':
                $parts[] = "Fondsemisjon til {$this->toShareholder?->name}";
                break;
        }

        $parts[] = "{$this->number_of_shares} aksjer";
        $parts[] = "({$this->shareClass?->code})";

        return implode(' - ', $parts);
    }

    // Business methods
    public function isIncrease(): bool
    {
        return in_array($this->transaction_type, ['issue', 'bonus', 'split']);
    }

    public function isDecrease(): bool
    {
        return in_array($this->transaction_type, ['redemption', 'merger']);
    }

    public function isTransfer(): bool
    {
        return $this->transaction_type === 'transfer';
    }

    public function affectsShareholder(int $shareholderId): bool
    {
        return $this->from_shareholder_id === $shareholderId
            || $this->to_shareholder_id === $shareholderId;
    }

    public function getShareChangeForShareholder(int $shareholderId): int
    {
        if ($this->to_shareholder_id === $shareholderId) {
            return $this->number_of_shares;
        }

        if ($this->from_shareholder_id === $shareholderId) {
            return -$this->number_of_shares;
        }

        return 0;
    }
}
