<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use App\Services\AccountingService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankReconciliationDraft extends Model
{
    use BelongsToCompany;

    public const VOUCHER_TYPE_PAYMENT = 'payment';

    public const VOUCHER_TYPE_SUPPLIER_PAYMENT = 'supplier_payment';

    public const VOUCHER_TYPE_MANUAL = 'manual';

    protected $fillable = [
        'bank_transaction_id',
        'voucher_type',
        'voucher_data',
        'contact_id',
        'account_id',
        'description',
        'amount',
        'is_processed',
        'voucher_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'voucher_data' => 'array',
            'amount' => 'decimal:2',
            'is_processed' => 'boolean',
        ];
    }

    public function bankTransaction(): BelongsTo
    {
        return $this->belongsTo(BankTransaction::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Process the draft and create a voucher.
     */
    public function processToVoucher(): ?Voucher
    {
        if ($this->is_processed) {
            return $this->voucher;
        }

        $accountingService = app(AccountingService::class);
        $bankTransaction = $this->bankTransaction;
        $bankAccount = $bankTransaction->bankStatement->bankAccount;

        $lines = $this->voucher_data['lines'] ?? [];

        if (empty($lines)) {
            $lines = [
                [
                    'account_id' => $this->account_id,
                    'description' => $this->description,
                    'amount' => abs($this->amount),
                ],
            ];
        }

        $voucher = Voucher::create([
            'voucher_date' => $bankTransaction->transaction_date,
            'description' => $this->description,
            'voucher_type' => $this->voucher_type === self::VOUCHER_TYPE_PAYMENT ? 'payment' : 'supplier_payment',
            'created_by' => $this->created_by,
        ]);

        $sortOrder = 1;

        if ($bankTransaction->isCredit) {
            $voucher->lines()->create([
                'account_id' => $bankAccount?->id,
                'description' => $this->description,
                'debit' => abs($this->amount),
                'credit' => 0,
                'sort_order' => $sortOrder++,
            ]);

            foreach ($lines as $line) {
                $voucher->lines()->create([
                    'account_id' => $line['account_id'],
                    'description' => $line['description'] ?? $this->description,
                    'debit' => 0,
                    'credit' => abs($line['amount'] ?? $this->amount),
                    'sort_order' => $sortOrder++,
                ]);
            }
        } else {
            foreach ($lines as $line) {
                $voucher->lines()->create([
                    'account_id' => $line['account_id'],
                    'description' => $line['description'] ?? $this->description,
                    'debit' => abs($line['amount'] ?? $this->amount),
                    'credit' => 0,
                    'sort_order' => $sortOrder++,
                ]);
            }

            $voucher->lines()->create([
                'account_id' => $bankAccount?->id,
                'description' => $this->description,
                'debit' => 0,
                'credit' => abs($this->amount),
                'sort_order' => $sortOrder++,
            ]);
        }

        $voucher->recalculateTotals();

        $this->update([
            'is_processed' => true,
            'voucher_id' => $voucher->id,
        ]);

        $bankTransaction->update([
            'match_status' => BankTransaction::MATCH_STATUS_MANUAL_MATCHED,
        ]);

        $bankTransaction->bankStatement->recalculateCounts();

        return $voucher;
    }

    public function getVoucherTypeLabelAttribute(): string
    {
        return match ($this->voucher_type) {
            self::VOUCHER_TYPE_PAYMENT => 'Innbetaling',
            self::VOUCHER_TYPE_SUPPLIER_PAYMENT => 'Utbetaling',
            self::VOUCHER_TYPE_MANUAL => 'Manuelt bilag',
            default => 'Ukjent',
        };
    }

    public function scopeUnprocessed($query)
    {
        return $query->where('is_processed', false);
    }

    public function scopeProcessed($query)
    {
        return $query->where('is_processed', true);
    }
}
