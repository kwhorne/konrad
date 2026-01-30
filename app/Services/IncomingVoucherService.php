<?php

namespace App\Services;

use App\Jobs\ParseVoucherJob;
use App\Models\Account;
use App\Models\IncomingVoucher;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class IncomingVoucherService
{
    public function __construct(
        private AccountSuggestionService $accountSuggestionService
    ) {}

    /**
     * Upload and create incoming vouchers from files.
     *
     * @param  array<UploadedFile>  $files
     * @return array<IncomingVoucher>
     */
    public function uploadFiles(array $files, ?int $createdBy = null): array
    {
        $disk = config('voucher.storage.disk', 'local');
        $path = config('voucher.storage.path', 'incoming-vouchers');

        $vouchers = [];

        foreach ($files as $file) {
            $storedPath = $file->store($path, $disk);

            $voucher = IncomingVoucher::create([
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $storedPath,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'source' => IncomingVoucher::SOURCE_UPLOAD,
                'status' => IncomingVoucher::STATUS_PENDING,
                'created_by' => $createdBy ?? auth()->id(),
            ]);

            // Dispatch parsing job
            dispatch(new ParseVoucherJob($voucher));

            $vouchers[] = $voucher;
        }

        return $vouchers;
    }

    /**
     * Update suggested values on an incoming voucher.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateSuggestions(IncomingVoucher $voucher, array $data): IncomingVoucher
    {
        $voucher->update([
            'suggested_supplier_id' => $data['supplier_id'] ?? null,
            'suggested_invoice_number' => $data['invoice_number'] ?? null,
            'suggested_invoice_date' => $data['invoice_date'] ?? null,
            'suggested_due_date' => $data['due_date'] ?? null,
            'suggested_total' => isset($data['total']) ? (float) $data['total'] : null,
            'suggested_vat_total' => isset($data['vat_total']) ? (float) $data['vat_total'] : null,
            'suggested_account_id' => $data['account_id'] ?? null,
        ]);

        return $voucher->fresh();
    }

    /**
     * Attest an incoming voucher.
     *
     * @param  array<string, mixed>  $data  Updated suggestion data
     */
    public function attest(IncomingVoucher $voucher, array $data): bool
    {
        $this->updateSuggestions($voucher, $data);

        return $voucher->attest(auth()->user());
    }

    /**
     * Approve an incoming voucher and create supplier invoice.
     *
     * @param  array<string, mixed>  $data  Updated suggestion data
     * @return array{success: bool, supplier_invoice: \App\Models\SupplierInvoice|null, error: string|null}
     */
    public function approve(IncomingVoucher $voucher, array $data): array
    {
        $this->updateSuggestions($voucher, $data);

        if (! $voucher->approve(auth()->user())) {
            return [
                'success' => false,
                'supplier_invoice' => null,
                'error' => 'Kunne ikke godkjenne bilaget.',
            ];
        }

        // Create supplier invoice and post voucher
        $supplierInvoice = $voucher->createSupplierInvoice();

        if ($supplierInvoice) {
            // Record account usage for learning
            $supplier = $supplierInvoice->contact;
            $account = Account::find($data['account_id'] ?? null);

            if ($supplier && $account) {
                $this->accountSuggestionService->recordUsage(
                    $supplier,
                    $voucher->parsed_data['description'] ?? '',
                    $account
                );
            }

            return [
                'success' => true,
                'supplier_invoice' => $supplierInvoice,
                'error' => null,
            ];
        }

        return [
            'success' => true,
            'supplier_invoice' => null,
            'error' => 'Bilaget ble godkjent, men kunne ikke opprette leverandørfaktura.',
        ];
    }

    /**
     * Reject an incoming voucher.
     */
    public function reject(IncomingVoucher $voucher, string $reason): bool
    {
        return $voucher->reject(auth()->user(), $reason);
    }

    /**
     * Re-parse an incoming voucher.
     */
    public function reParse(IncomingVoucher $voucher): bool
    {
        if ($voucher->status === IncomingVoucher::STATUS_PARSING) {
            return false;
        }

        $voucher->update(['status' => IncomingVoucher::STATUS_PENDING]);
        dispatch(new ParseVoucherJob($voucher));

        return true;
    }

    /**
     * Delete an incoming voucher (if allowed).
     */
    public function delete(IncomingVoucher $voucher): bool
    {
        if (in_array($voucher->status, [IncomingVoucher::STATUS_APPROVED, IncomingVoucher::STATUS_POSTED])) {
            return false;
        }

        // Delete file from storage
        if ($voucher->file_path) {
            $disk = config('voucher.storage.disk', 'local');
            Storage::disk($disk)->delete($voucher->file_path);
        }

        $voucher->delete();

        return true;
    }

    /**
     * Get status counts for dashboard/overview.
     *
     * @return array<string, int>
     */
    public function getStatusCounts(): array
    {
        return [
            'pending' => IncomingVoucher::where('status', IncomingVoucher::STATUS_PENDING)->count(),
            'parsing' => IncomingVoucher::where('status', IncomingVoucher::STATUS_PARSING)->count(),
            'parsed' => IncomingVoucher::where('status', IncomingVoucher::STATUS_PARSED)->count(),
            'attested' => IncomingVoucher::where('status', IncomingVoucher::STATUS_ATTESTED)->count(),
            'approved' => IncomingVoucher::where('status', IncomingVoucher::STATUS_APPROVED)->count(),
            'posted' => IncomingVoucher::where('status', IncomingVoucher::STATUS_POSTED)->count(),
            'rejected' => IncomingVoucher::where('status', IncomingVoucher::STATUS_REJECTED)->count(),
        ];
    }

    /**
     * Check if voucher can be attested.
     */
    public function canAttest(IncomingVoucher $voucher): bool
    {
        return $voucher->status === IncomingVoucher::STATUS_PARSED;
    }

    /**
     * Check if voucher can be approved.
     */
    public function canApprove(IncomingVoucher $voucher): bool
    {
        return $voucher->status === IncomingVoucher::STATUS_ATTESTED;
    }

    /**
     * Check if voucher can be rejected.
     */
    public function canReject(IncomingVoucher $voucher): bool
    {
        return in_array($voucher->status, [
            IncomingVoucher::STATUS_PARSED,
            IncomingVoucher::STATUS_ATTESTED,
        ]);
    }

    /**
     * Check if voucher can be deleted.
     */
    public function canDelete(IncomingVoucher $voucher): bool
    {
        return ! in_array($voucher->status, [
            IncomingVoucher::STATUS_APPROVED,
            IncomingVoucher::STATUS_POSTED,
        ]);
    }

    /**
     * Check if voucher can be re-parsed.
     */
    public function canReParse(IncomingVoucher $voucher): bool
    {
        return $voucher->status !== IncomingVoucher::STATUS_PARSING;
    }
}
