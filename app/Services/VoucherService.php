<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Voucher;
use App\Models\VoucherLine;

class VoucherService
{
    /**
     * Calculate total debit from working lines.
     *
     * @param  array<int, array<string, mixed>>  $lines
     */
    public function calculateTotalDebit(array $lines): float
    {
        return collect($lines)->sum('debit');
    }

    /**
     * Calculate total credit from working lines.
     *
     * @param  array<int, array<string, mixed>>  $lines
     */
    public function calculateTotalCredit(array $lines): float
    {
        return collect($lines)->sum('credit');
    }

    /**
     * Calculate the difference between debit and credit.
     *
     * @param  array<int, array<string, mixed>>  $lines
     */
    public function calculateDifference(array $lines): float
    {
        return abs($this->calculateTotalDebit($lines) - $this->calculateTotalCredit($lines));
    }

    /**
     * Check if working lines are balanced.
     *
     * @param  array<int, array<string, mixed>>  $lines
     */
    public function isBalanced(array $lines): bool
    {
        return $this->calculateDifference($lines) < 0.01 && count($lines) > 0;
    }

    /**
     * Validate a single line's debit/credit values.
     *
     * @return array<string, string>|null Returns error messages or null if valid
     */
    public function validateLineAmounts(float $debit, float $credit): ?array
    {
        if ($debit == 0 && $credit == 0) {
            return ['debit' => 'Du må fylle inn debet eller kredit'];
        }

        if ($debit > 0 && $credit > 0) {
            return ['debit' => 'Du kan ikke fylle inn både debet og kredit på samme linje'];
        }

        return null;
    }

    /**
     * Build line data array for working lines.
     *
     * @return array<string, mixed>
     */
    public function buildLineData(
        int $accountId,
        string $description,
        float $debit,
        float $credit,
        ?int $contactId = null,
        ?int $existingId = null
    ): array {
        $account = Account::find($accountId);
        $contact = $contactId ? Contact::find($contactId) : null;

        return [
            'id' => $existingId,
            'account_id' => $accountId,
            'account_number' => $account?->account_number,
            'account_name' => $account?->name,
            'description' => $description,
            'debit' => $debit,
            'credit' => $credit,
            'contact_id' => $contactId,
            'contact_name' => $contact?->company_name,
        ];
    }

    /**
     * Convert voucher lines to working lines array format.
     *
     * @return array<int, array<string, mixed>>
     */
    public function voucherLinesToWorkingLines(Voucher $voucher): array
    {
        return $voucher->lines->map(function ($line) {
            return [
                'id' => $line->id,
                'account_id' => $line->account_id,
                'account_number' => $line->account->account_number,
                'account_name' => $line->account->name,
                'description' => $line->description ?? '',
                'debit' => (float) $line->debit,
                'credit' => (float) $line->credit,
                'contact_id' => $line->contact_id,
                'contact_name' => $line->contact?->company_name,
            ];
        })->toArray();
    }

    /**
     * Create a new voucher with lines.
     *
     * @param  array<string, mixed>  $voucherData
     * @param  array<int, array<string, mixed>>  $lines
     */
    public function createVoucher(array $voucherData, array $lines): Voucher
    {
        $voucher = Voucher::create([
            'voucher_date' => $voucherData['voucher_date'],
            'description' => $voucherData['description'],
            'voucher_type' => $voucherData['voucher_type'] ?? 'manual',
            'created_by' => $voucherData['created_by'] ?? auth()->id(),
        ]);

        $this->syncLines($voucher, $lines);

        return $voucher->fresh(['lines.account', 'lines.contact']);
    }

    /**
     * Update an existing voucher with lines.
     *
     * @param  array<string, mixed>  $voucherData
     * @param  array<int, array<string, mixed>>  $lines
     */
    public function updateVoucher(Voucher $voucher, array $voucherData, array $lines): Voucher
    {
        if ($voucher->is_posted) {
            throw new \InvalidArgumentException('Kan ikke redigere et bokført bilag');
        }

        $voucher->update([
            'voucher_date' => $voucherData['voucher_date'],
            'description' => $voucherData['description'],
        ]);

        $this->syncLines($voucher, $lines);

        return $voucher->fresh(['lines.account', 'lines.contact']);
    }

    /**
     * Sync voucher lines from working lines array.
     *
     * @param  array<int, array<string, mixed>>  $lines
     */
    protected function syncLines(Voucher $voucher, array $lines): void
    {
        // Get existing line IDs from working lines
        $existingIds = collect($lines)->pluck('id')->filter()->toArray();

        // Delete removed lines
        $voucher->lines()->whereNotIn('id', $existingIds)->delete();

        // Update or create lines
        foreach ($lines as $index => $line) {
            $lineData = [
                'account_id' => $line['account_id'],
                'description' => $line['description'],
                'debit' => $line['debit'],
                'credit' => $line['credit'],
                'contact_id' => $line['contact_id'],
                'sort_order' => $index,
            ];

            if (! empty($line['id'])) {
                VoucherLine::find($line['id'])?->update($lineData);
            } else {
                $voucher->lines()->create($lineData);
            }
        }

        $voucher->recalculateTotals();
    }

    /**
     * Post a voucher.
     */
    public function postVoucher(Voucher $voucher): bool
    {
        return $voucher->post();
    }

    /**
     * Delete a voucher (only if not posted).
     */
    public function deleteVoucher(Voucher $voucher): bool
    {
        if ($voucher->is_posted) {
            return false;
        }

        $voucher->delete();

        return true;
    }

    /**
     * Validate that voucher can be saved.
     *
     * @param  array<int, array<string, mixed>>  $lines
     * @return array<string, string>|null Returns error messages or null if valid
     */
    public function validateVoucher(array $lines): ?array
    {
        if (count($lines) < 2) {
            return ['lines' => 'Et bilag må ha minst 2 linjer'];
        }

        if (! $this->isBalanced($lines)) {
            return ['lines' => 'Debet og kredit må være i balanse'];
        }

        return null;
    }

    /**
     * Get voucher summary.
     *
     * @return array{total_debit: float, total_credit: float, difference: float, is_balanced: bool, line_count: int}
     */
    public function getVoucherSummary(Voucher $voucher): array
    {
        $voucher->loadMissing('lines');

        return [
            'total_debit' => (float) $voucher->total_debit,
            'total_credit' => (float) $voucher->total_credit,
            'difference' => abs((float) $voucher->total_debit - (float) $voucher->total_credit),
            'is_balanced' => $voucher->is_balanced,
            'line_count' => $voucher->lines->count(),
        ];
    }
}
