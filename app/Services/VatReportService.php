<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\VatCode;
use App\Models\VatReport;
use App\Models\VatReportAttachment;
use App\Models\VatReportLine;
use App\Models\VoucherLine;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class VatReportService
{
    /**
     * Create a new VAT report for a bi-monthly period.
     */
    public function createReport(int $year, int $period, string $reportType = 'alminnelig'): VatReport
    {
        $dates = VatReport::getBimonthlyPeriodDates($year, $period);

        return VatReport::create([
            'report_type' => $reportType,
            'period_type' => 'bimonthly',
            'year' => $year,
            'period' => $period,
            'period_from' => $dates['from'],
            'period_to' => $dates['to'],
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Calculate VAT amounts for a report based on posted vouchers.
     */
    public function calculateReport(VatReport $report): VatReport
    {
        return DB::transaction(function () use ($report) {
            // Remove existing lines
            $report->lines()->delete();

            // Get all active VAT codes
            $vatCodes = VatCode::active()->ordered()->get();

            $sortOrder = 0;

            foreach ($vatCodes as $vatCode) {
                $amounts = $this->calculateVatCodeAmounts($vatCode, $report->period_from, $report->period_to);

                if ($amounts['base'] != 0 || $amounts['vat'] != 0) {
                    VatReportLine::create([
                        'vat_report_id' => $report->id,
                        'vat_code_id' => $vatCode->id,
                        'base_amount' => $amounts['base'],
                        'vat_rate' => $vatCode->rate,
                        'vat_amount' => $amounts['vat'],
                        'sort_order' => $sortOrder++,
                    ]);
                }
            }

            $report->recalculateTotals();

            return $report->fresh(['lines.vatCode']);
        });
    }

    /**
     * Calculate amounts for a specific VAT code in a period.
     */
    protected function calculateVatCodeAmounts(VatCode $vatCode, Carbon $from, Carbon $to): array
    {
        $base = 0;
        $vat = 0;

        // Calculate based on the VAT code category and direction
        switch ($vatCode->category) {
            case 'salg_norge':
                // Sales in Norway - get from outgoing invoices
                $amounts = $this->calculateSalesVat($vatCode, $from, $to);
                $base = $amounts['base'];
                $vat = $amounts['vat'];
                break;

            case 'kjop_norge':
                // Purchases in Norway - get from supplier invoices
                $amounts = $this->calculatePurchaseVat($vatCode, $from, $to);
                $base = $amounts['base'];
                $vat = $amounts['vat'];
                break;

            case 'import':
            case 'export':
            case 'other':
                // These often need manual entry or special calculation
                // For now, get from voucher lines with VAT amounts
                $amounts = $this->calculateFromVouchers($vatCode, $from, $to);
                $base = $amounts['base'];
                $vat = $amounts['vat'];
                break;
        }

        // Apply sign from VAT code (1 for positive, -1 for negative/deduction)
        return [
            'base' => $base * $vatCode->sign,
            'vat' => $vat * $vatCode->sign,
        ];
    }

    /**
     * Calculate sales VAT from outgoing invoices.
     */
    protected function calculateSalesVat(VatCode $vatCode, Carbon $from, Carbon $to): array
    {
        // Get invoices within the period
        $query = Invoice::where('invoice_type', 'invoice')
            ->whereBetween('invoice_date', [$from, $to])
            ->whereNotNull('sent_at'); // Only include sent invoices

        // Match by VAT rate
        if ($vatCode->rate !== null) {
            // Sum line items with matching VAT rate
            $result = DB::table('invoices')
                ->join('invoice_lines', 'invoices.id', '=', 'invoice_lines.invoice_id')
                ->where('invoices.invoice_type', 'invoice')
                ->whereBetween('invoices.invoice_date', [$from, $to])
                ->whereNotNull('invoices.sent_at')
                ->whereNull('invoices.deleted_at')
                ->where('invoice_lines.vat_percent', $vatCode->rate)
                ->selectRaw('
                    SUM(invoice_lines.quantity * invoice_lines.unit_price * (1 - invoice_lines.discount_percent / 100)) as base,
                    SUM(invoice_lines.quantity * invoice_lines.unit_price * (1 - invoice_lines.discount_percent / 100) * invoice_lines.vat_percent / 100) as vat
                ')
                ->first();

            return [
                'base' => (float) ($result->base ?? 0),
                'vat' => (float) ($result->vat ?? 0),
            ];
        }

        // For null rate (exempt), get lines with 0% VAT
        if ($vatCode->code === '5' || $vatCode->code === '6') {
            $result = DB::table('invoices')
                ->join('invoice_lines', 'invoices.id', '=', 'invoice_lines.invoice_id')
                ->where('invoices.invoice_type', 'invoice')
                ->whereBetween('invoices.invoice_date', [$from, $to])
                ->whereNotNull('invoices.sent_at')
                ->whereNull('invoices.deleted_at')
                ->where('invoice_lines.vat_percent', 0)
                ->selectRaw('
                    SUM(invoice_lines.quantity * invoice_lines.unit_price * (1 - invoice_lines.discount_percent / 100)) as base
                ')
                ->first();

            return [
                'base' => (float) ($result->base ?? 0),
                'vat' => 0,
            ];
        }

        return ['base' => 0, 'vat' => 0];
    }

    /**
     * Calculate purchase VAT from supplier invoices.
     */
    protected function calculatePurchaseVat(VatCode $vatCode, Carbon $from, Carbon $to): array
    {
        if ($vatCode->rate === null) {
            return ['base' => 0, 'vat' => 0];
        }

        $result = DB::table('supplier_invoices')
            ->join('supplier_invoice_lines', 'supplier_invoices.id', '=', 'supplier_invoice_lines.supplier_invoice_id')
            ->whereBetween('supplier_invoices.invoice_date', [$from, $to])
            ->whereIn('supplier_invoices.status', ['approved', 'paid', 'partially_paid'])
            ->whereNull('supplier_invoices.deleted_at')
            ->where('supplier_invoice_lines.vat_percent', $vatCode->rate)
            ->selectRaw('
                SUM(supplier_invoice_lines.quantity * supplier_invoice_lines.unit_price) as base,
                SUM(supplier_invoice_lines.quantity * supplier_invoice_lines.unit_price * supplier_invoice_lines.vat_percent / 100) as vat
            ')
            ->first();

        return [
            'base' => (float) ($result->base ?? 0),
            'vat' => (float) ($result->vat ?? 0),
        ];
    }

    /**
     * Calculate from voucher lines for special cases.
     */
    protected function calculateFromVouchers(VatCode $vatCode, Carbon $from, Carbon $to): array
    {
        // Get VAT account based on direction
        $accountNumber = $vatCode->direction === 'output' ? '2700' : '2710';
        $vatAccount = Account::where('account_number', $accountNumber)->first();

        if (! $vatAccount) {
            return ['base' => 0, 'vat' => 0];
        }

        // Sum VAT amounts from voucher lines posted to VAT accounts
        $result = VoucherLine::where('account_id', $vatAccount->id)
            ->whereNotNull('vat_amount')
            ->whereHas('voucher', function ($q) use ($from, $to) {
                $q->where('is_posted', true)
                    ->whereBetween('voucher_date', [$from, $to]);
            })
            ->selectRaw('SUM(ABS(vat_amount)) as vat')
            ->first();

        $vat = (float) ($result->vat ?? 0);

        // Calculate base from VAT amount if rate is known
        $base = 0;
        if ($vatCode->rate && $vatCode->rate > 0) {
            $base = $vat / ($vatCode->rate / 100);
        }

        return [
            'base' => $base,
            'vat' => $vat,
        ];
    }

    /**
     * Update a specific line with manual override.
     */
    public function updateLine(VatReportLine $line, float $baseAmount, float $vatAmount, ?string $note = null): VatReportLine
    {
        $line->update([
            'base_amount' => $baseAmount,
            'vat_amount' => $vatAmount,
            'note' => $note,
            'is_manual_override' => true,
        ]);

        $line->vatReport->recalculateTotals();

        return $line->fresh();
    }

    /**
     * Add a note to the report.
     */
    public function updateNote(VatReport $report, ?string $note): VatReport
    {
        $report->update(['note' => $note]);

        return $report->fresh();
    }

    /**
     * Add an attachment to the report.
     */
    public function addAttachment(VatReport $report, UploadedFile $file): VatReportAttachment
    {
        $path = $file->store('vat-report-attachments/'.$report->id, 'local');

        return VatReportAttachment::create([
            'vat_report_id' => $report->id,
            'filename' => basename($path),
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'path' => $path,
            'uploaded_by' => auth()->id(),
        ]);
    }

    /**
     * Remove an attachment.
     */
    public function removeAttachment(VatReportAttachment $attachment): void
    {
        $attachment->delete();
    }

    /**
     * Mark report as submitted.
     */
    public function submitReport(VatReport $report, ?string $altinnReference = null): VatReport
    {
        $report->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'submitted_by' => auth()->id(),
            'altinn_reference' => $altinnReference,
        ]);

        return $report->fresh();
    }

    /**
     * Mark report as accepted.
     */
    public function acceptReport(VatReport $report): VatReport
    {
        $report->update(['status' => 'accepted']);

        return $report->fresh();
    }

    /**
     * Mark report as rejected.
     */
    public function rejectReport(VatReport $report): VatReport
    {
        $report->update(['status' => 'rejected']);

        return $report->fresh();
    }

    /**
     * Get available periods for creating new reports.
     */
    public function getAvailablePeriods(int $year): Collection
    {
        $existingPeriods = VatReport::where('year', $year)
            ->pluck('period')
            ->toArray();

        $periods = collect();
        $periodNames = [
            1 => 'Januar - Februar',
            2 => 'Mars - April',
            3 => 'Mai - Juni',
            4 => 'Juli - August',
            5 => 'September - Oktober',
            6 => 'November - Desember',
        ];

        foreach ($periodNames as $period => $name) {
            if (! in_array($period, $existingPeriods)) {
                $periods->push([
                    'period' => $period,
                    'name' => $name,
                    'year' => $year,
                ]);
            }
        }

        return $periods;
    }

    /**
     * Get summary data for a report grouped by category.
     *
     * @return array<string, array{name: string, lines: Collection, base_total: float, vat_total: float}>
     */
    public function getReportSummary(VatReport $report): array
    {
        $lines = $report->lines()->with('vatCode')->get();

        $categories = [
            'salg_norge' => [
                'name' => 'Salg av varer og tjenester i Norge',
                'lines' => collect(),
                'base_total' => 0,
                'vat_total' => 0,
            ],
            'kjop_norge' => [
                'name' => 'Kjøp av varer og tjenester i Norge',
                'lines' => collect(),
                'base_total' => 0,
                'vat_total' => 0,
            ],
            'import' => [
                'name' => 'Kjøp av tjenester fra utlandet (import)',
                'lines' => collect(),
                'base_total' => 0,
                'vat_total' => 0,
            ],
            'export' => [
                'name' => 'Utførsel av varer og tjenester',
                'lines' => collect(),
                'base_total' => 0,
                'vat_total' => 0,
            ],
            'other' => [
                'name' => 'Andre forhold',
                'lines' => collect(),
                'base_total' => 0,
                'vat_total' => 0,
            ],
        ];

        foreach ($lines as $line) {
            $category = $line->vatCode->category ?? 'other';
            if (isset($categories[$category])) {
                $categories[$category]['lines']->push($line);
                $categories[$category]['base_total'] += (float) $line->base_amount;
                $categories[$category]['vat_total'] += (float) $line->vat_amount;
            }
        }

        // Remove empty categories
        return array_filter($categories, fn ($cat) => $cat['lines']->isNotEmpty());
    }

    /**
     * Get current bi-monthly period.
     *
     * @return array{year: int, period: int}
     */
    public function getCurrentPeriod(): array
    {
        $now = now();
        $month = $now->month;
        $year = $now->year;

        // Calculate bi-monthly period (1-6)
        $period = (int) ceil($month / 2);

        return [
            'year' => $year,
            'period' => $period,
        ];
    }

    /**
     * Check if a report exists for a specific period.
     */
    public function reportExists(int $year, int $period): bool
    {
        return VatReport::where('year', $year)
            ->where('period', $period)
            ->exists();
    }

    /**
     * Delete a VAT report (only if in draft status).
     */
    public function deleteReport(VatReport $report): bool
    {
        if ($report->status !== 'draft') {
            return false;
        }

        $report->delete();

        return true;
    }

    /**
     * Get available years for filtering and selection.
     *
     * @return array<int>
     */
    public function getAvailableYears(int $yearsBack = 5): array
    {
        $years = [];
        $currentYear = now()->year;

        for ($i = $currentYear; $i >= $currentYear - $yearsBack; $i--) {
            $years[] = $i;
        }

        return $years;
    }

    /**
     * Check if a report can be deleted.
     */
    public function canDelete(VatReport $report): bool
    {
        return $report->status === 'draft';
    }

    /**
     * Check if a report can be submitted.
     */
    public function canSubmit(VatReport $report): bool
    {
        return $report->status === 'draft';
    }

    /**
     * Check if a report can be accepted/rejected.
     */
    public function canChangeStatus(VatReport $report): bool
    {
        return $report->status === 'submitted';
    }

    /**
     * Get or create a draft report for a period.
     */
    public function getOrCreateDraftReport(int $year, int $period): VatReport
    {
        $report = VatReport::where('year', $year)
            ->where('period', $period)
            ->first();

        if (! $report) {
            $report = $this->createReport($year, $period);
        }

        return $report;
    }
}
