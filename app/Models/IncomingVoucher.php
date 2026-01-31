<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use App\Services\AccountingService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class IncomingVoucher extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    // Statuser
    public const STATUS_PENDING = 'pending';

    public const STATUS_PARSING = 'parsing';

    public const STATUS_PARSED = 'parsed';

    public const STATUS_ATTESTED = 'attested';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_POSTED = 'posted';

    public const STATUS_REJECTED = 'rejected';

    // Kilder
    public const SOURCE_UPLOAD = 'upload';

    public const SOURCE_EMAIL = 'email';

    protected $fillable = [
        'reference_number',
        'original_filename',
        'file_path',
        'mime_type',
        'file_size',
        'source',
        'email_from',
        'email_subject',
        'email_received_at',
        'status',
        'parsed_at',
        'parsed_data',
        'suggested_supplier_id',
        'suggested_invoice_number',
        'suggested_invoice_date',
        'suggested_due_date',
        'suggested_total',
        'suggested_vat_total',
        'suggested_account_id',
        'confidence_score',
        'attested_by',
        'attested_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'supplier_invoice_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'parsed_data' => 'array',
            'suggested_invoice_date' => 'date',
            'suggested_due_date' => 'date',
            'suggested_total' => 'decimal:2',
            'suggested_vat_total' => 'decimal:2',
            'confidence_score' => 'decimal:2',
            'email_received_at' => 'datetime',
            'parsed_at' => 'datetime',
            'attested_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (IncomingVoucher $voucher) {
            if (empty($voucher->reference_number)) {
                $voucher->reference_number = static::generateReferenceNumber();
            }
        });
    }

    public static function generateReferenceNumber(): string
    {
        $prefix = 'IB';
        $year = date('Y');

        $lastVoucher = static::withTrashed()
            ->whereYear('created_at', $year)
            ->orderByDesc('id')
            ->first();

        $pattern = '/'.$prefix.'-'.$year.'-(\d+)/';
        if ($lastVoucher && preg_match($pattern, $lastVoucher->reference_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $year, $nextNumber);
    }

    // Relasjoner

    public function suggestedSupplier(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'suggested_supplier_id');
    }

    public function suggestedAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'suggested_account_id');
    }

    public function supplierInvoice(): BelongsTo
    {
        return $this->belongsTo(SupplierInvoice::class);
    }

    public function attestedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attested_by');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Workflow-metoder

    public function attest(User $user): bool
    {
        if ($this->status !== self::STATUS_PARSED) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_ATTESTED,
            'attested_by' => $user->id,
            'attested_at' => now(),
        ]);

        return true;
    }

    public function approve(User $user): bool
    {
        if ($this->status !== self::STATUS_ATTESTED) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return true;
    }

    public function reject(User $user, string $reason): bool
    {
        if (! in_array($this->status, [self::STATUS_PARSED, self::STATUS_ATTESTED])) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_REJECTED,
            'rejected_by' => $user->id,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return true;
    }

    public function createSupplierInvoice(): ?SupplierInvoice
    {
        if ($this->status !== self::STATUS_APPROVED) {
            return null;
        }

        if (! $this->suggested_supplier_id) {
            return null;
        }

        $supplierInvoice = SupplierInvoice::create([
            'invoice_number' => $this->suggested_invoice_number,
            'contact_id' => $this->suggested_supplier_id,
            'invoice_date' => $this->suggested_invoice_date ?? now(),
            'due_date' => $this->suggested_due_date,
            'subtotal' => ($this->suggested_total ?? 0) - ($this->suggested_vat_total ?? 0),
            'vat_total' => $this->suggested_vat_total ?? 0,
            'total' => $this->suggested_total ?? 0,
            'paid_amount' => 0,
            'balance' => $this->suggested_total ?? 0,
            'status' => 'approved',
            'description' => $this->parsed_data['description'] ?? '',
            'attachment' => $this->file_path,
            'created_by' => auth()->id(),
            'approved_by' => $this->approved_by,
            'approved_at' => now(),
        ]);

        // Opprett linje med foreslått konto
        if ($this->suggested_account_id) {
            $supplierInvoice->lines()->create([
                'description' => $this->parsed_data['description'] ?? 'Fra inngående bilag',
                'account_id' => $this->suggested_account_id,
                'quantity' => 1,
                'unit_price' => ($this->suggested_total ?? 0) - ($this->suggested_vat_total ?? 0),
                'vat_percent' => $this->suggested_vat_total && $this->suggested_total
                    ? round(($this->suggested_vat_total / (($this->suggested_total ?? 1) - ($this->suggested_vat_total ?? 0))) * 100, 0)
                    : 25,
                'sort_order' => 1,
            ]);
        }

        // Opprett bilag via AccountingService
        $accountingService = app(AccountingService::class);
        $accountingService->createSupplierInvoiceVoucher($supplierInvoice);

        // Oppdater status
        $this->update([
            'status' => self::STATUS_POSTED,
            'supplier_invoice_id' => $supplierInvoice->id,
        ]);

        return $supplierInvoice;
    }

    // Accessors

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Venter',
            self::STATUS_PARSING => 'Tolkes',
            self::STATUS_PARSED => 'Tolket',
            self::STATUS_ATTESTED => 'Attestert',
            self::STATUS_APPROVED => 'Godkjent',
            self::STATUS_POSTED => 'Bokf&oslash;rt',
            self::STATUS_REJECTED => 'Avvist',
            default => 'Ukjent',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'zinc',
            self::STATUS_PARSING => 'blue',
            self::STATUS_PARSED => 'amber',
            self::STATUS_ATTESTED => 'cyan',
            self::STATUS_APPROVED => 'green',
            self::STATUS_POSTED => 'emerald',
            self::STATUS_REJECTED => 'red',
            default => 'zinc',
        };
    }

    public function getSourceLabelAttribute(): string
    {
        return match ($this->source) {
            self::SOURCE_UPLOAD => 'Opplastet',
            self::SOURCE_EMAIL => 'E-post',
            default => 'Ukjent',
        };
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1).' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return $bytes.' bytes';
    }

    public function getFileUrlAttribute(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        return Storage::disk(config('voucher.storage.disk', 'local'))->url($this->file_path);
    }

    public function getIsPdfAttribute(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    public function getConfidencePercentAttribute(): int
    {
        return (int) round(($this->confidence_score ?? 0) * 100);
    }

    // Scopes

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeParsing($query)
    {
        return $query->where('status', self::STATUS_PARSING);
    }

    public function scopeParsed($query)
    {
        return $query->where('status', self::STATUS_PARSED);
    }

    public function scopeAwaitingAttestation($query)
    {
        return $query->where('status', self::STATUS_PARSED);
    }

    public function scopeAwaitingApproval($query)
    {
        return $query->where('status', self::STATUS_ATTESTED);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePosted($query)
    {
        return $query->where('status', self::STATUS_POSTED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeFromUpload($query)
    {
        return $query->where('source', self::SOURCE_UPLOAD);
    }

    public function scopeFromEmail($query)
    {
        return $query->where('source', self::SOURCE_EMAIL);
    }

    public function scopeOrdered($query)
    {
        return $query->orderByDesc('created_at')->orderByDesc('id');
    }
}
