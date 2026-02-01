<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\SupplierInvoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CompanyAnalysisService
{
    /**
     * Gather comprehensive financial data for AI analysis.
     *
     * @return array<string, mixed>
     */
    public function gatherFinancialData(Company $company): array
    {
        $currentYear = Carbon::now()->year;
        $lastYear = $currentYear - 1;

        return [
            'company' => [
                'name' => $company->name,
                'organization_number' => $company->organization_number,
            ],
            'period' => [
                'current_year' => $currentYear,
                'last_year' => $lastYear,
                'analysis_date' => Carbon::now()->format('Y-m-d'),
            ],
            'revenue' => $this->getRevenueData($company, $currentYear, $lastYear),
            'expenses' => $this->getExpenseData($company, $currentYear, $lastYear),
            'receivables' => $this->getReceivablesData($company),
            'payables' => $this->getPayablesData($company),
            'cashflow' => $this->getCashflowIndicators($company, $currentYear),
            'profitability' => $this->getProfitabilityData($company, $currentYear, $lastYear),
            'key_customers' => $this->getKeyCustomers($company, $currentYear),
            'key_suppliers' => $this->getKeySuppliers($company, $currentYear),
        ];
    }

    /**
     * Generate AI analysis of company finances.
     *
     * @return array<string, mixed>
     */
    public function generateAnalysis(Company $company): array
    {
        $financialData = $this->gatherFinancialData($company);

        try {
            $response = $this->callAI($financialData);
            $analysis = $this->parseResponse($response);

            return [
                'success' => true,
                'analysis' => $analysis,
                'financial_data' => $financialData,
                'generated_at' => now()->toIso8601String(),
            ];
        } catch (\Exception $e) {
            Log::error('Feil ved generering av selskapsanalyse', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'financial_data' => $financialData,
            ];
        }
    }

    /**
     * Get revenue data for analysis.
     *
     * @return array<string, mixed>
     */
    protected function getRevenueData(Company $company, int $currentYear, int $lastYear): array
    {
        $currentYearRevenue = Invoice::where('company_id', $company->id)
            ->whereYear('invoice_date', $currentYear)
            ->whereHas('invoiceStatus', fn ($q) => $q->whereIn('code', ['sent', 'paid']))
            ->sum('total');

        $lastYearRevenue = Invoice::where('company_id', $company->id)
            ->whereYear('invoice_date', $lastYear)
            ->whereHas('invoiceStatus', fn ($q) => $q->whereIn('code', ['sent', 'paid']))
            ->sum('total');

        // Database-agnostic monthly grouping using PHP
        $monthlyRevenue = Invoice::where('company_id', $company->id)
            ->whereYear('invoice_date', $currentYear)
            ->whereHas('invoiceStatus', fn ($q) => $q->whereIn('code', ['sent', 'paid']))
            ->get(['invoice_date', 'total'])
            ->groupBy(fn ($invoice) => Carbon::parse($invoice->invoice_date)->month)
            ->map(fn ($invoices) => $invoices->sum('total'))
            ->toArray();

        $growth = $lastYearRevenue > 0
            ? round((($currentYearRevenue - $lastYearRevenue) / $lastYearRevenue) * 100, 1)
            : null;

        return [
            'current_year' => round($currentYearRevenue, 2),
            'last_year' => round($lastYearRevenue, 2),
            'growth_percent' => $growth,
            'monthly_breakdown' => $monthlyRevenue,
            'invoice_count' => Invoice::where('company_id', $company->id)
                ->whereYear('invoice_date', $currentYear)
                ->count(),
        ];
    }

    /**
     * Get expense data for analysis.
     *
     * @return array<string, mixed>
     */
    protected function getExpenseData(Company $company, int $currentYear, int $lastYear): array
    {
        $currentYearExpenses = SupplierInvoice::where('company_id', $company->id)
            ->whereYear('invoice_date', $currentYear)
            ->sum('total');

        $lastYearExpenses = SupplierInvoice::where('company_id', $company->id)
            ->whereYear('invoice_date', $lastYear)
            ->sum('total');

        $growth = $lastYearExpenses > 0
            ? round((($currentYearExpenses - $lastYearExpenses) / $lastYearExpenses) * 100, 1)
            : null;

        return [
            'current_year' => round($currentYearExpenses, 2),
            'last_year' => round($lastYearExpenses, 2),
            'growth_percent' => $growth,
        ];
    }

    /**
     * Get receivables (customer debt) data.
     *
     * @return array<string, mixed>
     */
    protected function getReceivablesData(Company $company): array
    {
        $unpaidInvoices = Invoice::where('company_id', $company->id)
            ->whereHas('invoiceStatus', fn ($q) => $q->whereIn('code', ['sent', 'overdue']))
            ->where('balance', '>', 0)
            ->get();

        $total = $unpaidInvoices->sum('balance');
        $overdue = $unpaidInvoices->filter(fn ($inv) => $inv->due_date < now())->sum('balance');
        $overdueCount = $unpaidInvoices->filter(fn ($inv) => $inv->due_date < now())->count();

        $aging = [
            '0-30' => 0,
            '31-60' => 0,
            '61-90' => 0,
            '90+' => 0,
        ];

        foreach ($unpaidInvoices as $invoice) {
            $daysOverdue = max(0, now()->diffInDays($invoice->due_date, false));
            if ($daysOverdue <= 30) {
                $aging['0-30'] += $invoice->balance;
            } elseif ($daysOverdue <= 60) {
                $aging['31-60'] += $invoice->balance;
            } elseif ($daysOverdue <= 90) {
                $aging['61-90'] += $invoice->balance;
            } else {
                $aging['90+'] += $invoice->balance;
            }
        }

        return [
            'total' => round($total, 2),
            'overdue_amount' => round($overdue, 2),
            'overdue_count' => $overdueCount,
            'aging' => array_map(fn ($v) => round($v, 2), $aging),
            'average_days_outstanding' => $unpaidInvoices->count() > 0
                ? round($unpaidInvoices->avg(fn ($inv) => now()->diffInDays($inv->invoice_date)))
                : 0,
        ];
    }

    /**
     * Get payables (supplier debt) data.
     *
     * @return array<string, mixed>
     */
    protected function getPayablesData(Company $company): array
    {
        $unpaidInvoices = SupplierInvoice::where('company_id', $company->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where('balance', '>', 0)
            ->get();

        $total = $unpaidInvoices->sum('balance');
        $overdue = $unpaidInvoices->filter(fn ($inv) => $inv->due_date < now())->sum('balance');

        return [
            'total' => round($total, 2),
            'overdue_amount' => round($overdue, 2),
            'count' => $unpaidInvoices->count(),
        ];
    }

    /**
     * Get cashflow indicators.
     *
     * @return array<string, mixed>
     */
    protected function getCashflowIndicators(Company $company, int $year): array
    {
        $paidInvoices = Invoice::where('company_id', $company->id)
            ->whereYear('paid_at', $year)
            ->whereNotNull('paid_at')
            ->sum('total');

        $paidSupplierInvoices = SupplierInvoice::where('company_id', $company->id)
            ->where('status', 'paid')
            ->whereYear('updated_at', $year)
            ->sum('total');

        return [
            'cash_in' => round($paidInvoices, 2),
            'cash_out' => round($paidSupplierInvoices, 2),
            'net_cashflow' => round($paidInvoices - $paidSupplierInvoices, 2),
        ];
    }

    /**
     * Get profitability data.
     *
     * @return array<string, mixed>
     */
    protected function getProfitabilityData(Company $company, int $currentYear, int $lastYear): array
    {
        $revenue = Invoice::where('company_id', $company->id)
            ->whereYear('invoice_date', $currentYear)
            ->whereHas('invoiceStatus', fn ($q) => $q->whereIn('code', ['sent', 'paid']))
            ->sum('total');

        $expenses = SupplierInvoice::where('company_id', $company->id)
            ->whereYear('invoice_date', $currentYear)
            ->sum('total');

        $grossProfit = $revenue - $expenses;
        $grossMargin = $revenue > 0 ? round(($grossProfit / $revenue) * 100, 1) : 0;

        return [
            'gross_profit' => round($grossProfit, 2),
            'gross_margin_percent' => $grossMargin,
            'revenue' => round($revenue, 2),
            'expenses' => round($expenses, 2),
        ];
    }

    /**
     * Get key customers by revenue.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getKeyCustomers(Company $company, int $year): array
    {
        return Invoice::where('company_id', $company->id)
            ->whereYear('invoice_date', $year)
            ->whereHas('invoiceStatus', fn ($q) => $q->whereIn('code', ['sent', 'paid']))
            ->with('contact')
            ->selectRaw('contact_id, SUM(total) as total_revenue, COUNT(*) as invoice_count')
            ->groupBy('contact_id')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'name' => $row->contact?->company_name ?? 'Ukjent',
                'revenue' => round($row->total_revenue, 2),
                'invoice_count' => $row->invoice_count,
            ])
            ->toArray();
    }

    /**
     * Get key suppliers by spending.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getKeySuppliers(Company $company, int $year): array
    {
        return SupplierInvoice::where('company_id', $company->id)
            ->whereYear('invoice_date', $year)
            ->with('contact')
            ->selectRaw('contact_id, SUM(total) as total_spending, COUNT(*) as invoice_count')
            ->groupBy('contact_id')
            ->orderByDesc('total_spending')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'name' => $row->contact?->company_name ?? 'Ukjent',
                'spending' => round($row->total_spending, 2),
                'invoice_count' => $row->invoice_count,
            ])
            ->toArray();
    }

    /**
     * Call AI for analysis.
     */
    protected function callAI(array $financialData): string
    {
        $provider = config('voucher.ai.provider', 'openai');
        $model = config('voucher.ai.model', 'gpt-4o');

        $response = prism()
            ->text()
            ->using($provider, $model)
            ->withSystemPrompt($this->getSystemPrompt())
            ->withPrompt($this->buildPrompt($financialData))
            ->asText();

        return $response->text;
    }

    /**
     * Get system prompt for AI analysis.
     */
    protected function getSystemPrompt(): string
    {
        return <<<'PROMPT'
Du er en erfaren norsk regnskapsfører og finansanalytiker som gir profesjonelle selskapsanalyser.
Analyser de økonomiske dataene og gi konkrete, handlingsrettede råd.

Returner BARE gyldig JSON (ingen markdown, ingen kodeblokker) med følgende struktur:

{
  "summary": "Kort oppsummering av selskapets økonomiske situasjon (2-3 setninger)",
  "health_score": 0-100,
  "health_label": "Utmerket|God|Akseptabel|Bekymringsfull|Kritisk",
  "strengths": [
    {"title": "Styrke 1", "description": "Forklaring", "metric": "Relevant tall eller %"}
  ],
  "weaknesses": [
    {"title": "Svakhet 1", "description": "Forklaring", "metric": "Relevant tall eller %"}
  ],
  "opportunities": [
    {"title": "Mulighet 1", "description": "Konkret råd for forbedring"}
  ],
  "risks": [
    {"title": "Risiko 1", "description": "Hva som kan gå galt og hvordan unngå det"}
  ],
  "recommendations": [
    {"priority": "high|medium|low", "title": "Anbefaling", "description": "Detaljert forklaring", "expected_impact": "Forventet effekt"}
  ],
  "key_metrics": {
    "liquidity": {"value": "Verdi", "status": "good|warning|critical", "comment": "Kort kommentar"},
    "profitability": {"value": "Verdi", "status": "good|warning|critical", "comment": "Kort kommentar"},
    "growth": {"value": "Verdi", "status": "good|warning|critical", "comment": "Kort kommentar"},
    "receivables": {"value": "Verdi", "status": "good|warning|critical", "comment": "Kort kommentar"}
  }
}

Viktige regler:
- Vær konkret og spesifikk med tall fra dataene
- Gi praktiske, gjennomførbare anbefalinger
- Bruk norsk språk og norske regnskapstermer
- Prioriter de viktigste funnene
- Health score: 80-100=Utmerket, 60-79=God, 40-59=Akseptabel, 20-39=Bekymringsfull, 0-19=Kritisk
- Returner KUN JSON, ingen forklaringer eller markdown
PROMPT;
    }

    /**
     * Build the analysis prompt with financial data.
     */
    protected function buildPrompt(array $financialData): string
    {
        $json = json_encode($financialData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return <<<PROMPT
Analyser følgende økonomiske data for selskapet og gi en komplett analyse med styrker, svakheter, muligheter, risikoer og anbefalinger.

Økonomiske data:
{$json}

Gi en grundig analyse basert på disse dataene.
PROMPT;
    }

    /**
     * Parse AI response to array.
     *
     * @return array<string, mixed>
     */
    protected function parseResponse(string $response): array
    {
        // Remove potential markdown code blocks
        $response = preg_replace('/^```json?\s*/m', '', $response);
        $response = preg_replace('/```\s*$/m', '', $response);
        $response = trim($response);

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('Kunne ikke parse AI-respons som JSON', [
                'response' => $response,
                'error' => json_last_error_msg(),
            ]);

            throw new \RuntimeException('Kunne ikke tolke AI-respons: '.json_last_error_msg());
        }

        return $data;
    }
}
