<?php

namespace App\Services\Altinn;

use App\Models\AnnualAccount;
use App\Models\CompanySetting;
use DOMDocument;
use DOMElement;

class ArsregnskapXbrlService
{
    // XBRL namespaces for Norwegian GAAP
    private const NS_XBRLI = 'http://www.xbrl.org/2003/instance';

    private const NS_LINK = 'http://www.xbrl.org/2003/linkbase';

    private const NS_XLINK = 'http://www.w3.org/1999/xlink';

    private const NS_ISO4217 = 'http://www.xbrl.org/2003/iso4217';

    private const NS_IFRS = 'http://xbrl.ifrs.org/taxonomy/2023-03-23/ifrs-full';

    private const NS_NGAAP = 'http://www.regnskapsstiftelsen.no/xbrl/taxonomy/2023';

    private DOMDocument $doc;

    private DOMElement $root;

    private string $contextId;

    private string $unitId = 'NOK';

    /**
     * Generate XBRL instance document for annual account.
     */
    public function generate(AnnualAccount $annualAccount): string
    {
        $this->doc = new DOMDocument('1.0', 'UTF-8');
        $this->doc->formatOutput = true;

        $this->createRoot();
        $this->addSchemaReferences();
        $this->addContexts($annualAccount);
        $this->addUnits();
        $this->addCompanyInformation($annualAccount);
        $this->addIncomeStatement($annualAccount);
        $this->addBalanceSheet($annualAccount);

        if ($annualAccount->cashFlowStatement) {
            $this->addCashFlowStatement($annualAccount);
        }

        $this->addNotes($annualAccount);

        return $this->doc->saveXML();
    }

    /**
     * Create root xbrl element.
     */
    private function createRoot(): void
    {
        $this->root = $this->doc->createElementNS(self::NS_XBRLI, 'xbrli:xbrl');

        $this->root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:link', self::NS_LINK);
        $this->root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xlink', self::NS_XLINK);
        $this->root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:iso4217', self::NS_ISO4217);
        $this->root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ngaap', self::NS_NGAAP);

        $this->doc->appendChild($this->root);
    }

    /**
     * Add schema references.
     */
    private function addSchemaReferences(): void
    {
        $schemaRef = $this->doc->createElementNS(self::NS_LINK, 'link:schemaRef');
        $schemaRef->setAttributeNS(self::NS_XLINK, 'xlink:type', 'simple');
        $schemaRef->setAttributeNS(self::NS_XLINK, 'xlink:href', 'http://www.regnskapsstiftelsen.no/xbrl/taxonomy/2023/ngaap-entry.xsd');
        $this->root->appendChild($schemaRef);
    }

    /**
     * Add XBRL contexts.
     */
    private function addContexts(AnnualAccount $annualAccount): void
    {
        $company = CompanySetting::first();
        $orgNumber = $company?->organization_number ?? '000000000';

        // Instant context (balance sheet date)
        $this->contextId = 'ctx_instant';
        $contextInstant = $this->doc->createElementNS(self::NS_XBRLI, 'xbrli:context');
        $contextInstant->setAttribute('id', $this->contextId);

        $entity = $this->doc->createElementNS(self::NS_XBRLI, 'xbrli:entity');
        $identifier = $this->doc->createElementNS(self::NS_XBRLI, 'xbrli:identifier', $orgNumber);
        $identifier->setAttribute('scheme', 'http://www.brreg.no');
        $entity->appendChild($identifier);

        $period = $this->doc->createElementNS(self::NS_XBRLI, 'xbrli:period');
        $instant = $this->doc->createElementNS(self::NS_XBRLI, 'xbrli:instant', $annualAccount->period_end->format('Y-m-d'));
        $period->appendChild($instant);

        $contextInstant->appendChild($entity);
        $contextInstant->appendChild($period);
        $this->root->appendChild($contextInstant);

        // Duration context (income statement period)
        $contextDuration = $this->doc->createElementNS(self::NS_XBRLI, 'xbrli:context');
        $contextDuration->setAttribute('id', 'ctx_duration');

        $entity2 = $this->doc->createElementNS(self::NS_XBRLI, 'xbrli:entity');
        $identifier2 = $this->doc->createElementNS(self::NS_XBRLI, 'xbrli:identifier', $orgNumber);
        $identifier2->setAttribute('scheme', 'http://www.brreg.no');
        $entity2->appendChild($identifier2);

        $period2 = $this->doc->createElementNS(self::NS_XBRLI, 'xbrli:period');
        $startDate = $this->doc->createElementNS(self::NS_XBRLI, 'xbrli:startDate', $annualAccount->period_start->format('Y-m-d'));
        $endDate = $this->doc->createElementNS(self::NS_XBRLI, 'xbrli:endDate', $annualAccount->period_end->format('Y-m-d'));
        $period2->appendChild($startDate);
        $period2->appendChild($endDate);

        $contextDuration->appendChild($entity2);
        $contextDuration->appendChild($period2);
        $this->root->appendChild($contextDuration);
    }

    /**
     * Add units (NOK).
     */
    private function addUnits(): void
    {
        $unit = $this->doc->createElementNS(self::NS_XBRLI, 'xbrli:unit');
        $unit->setAttribute('id', $this->unitId);

        $measure = $this->doc->createElementNS(self::NS_XBRLI, 'xbrli:measure', 'iso4217:NOK');
        $unit->appendChild($measure);

        $this->root->appendChild($unit);
    }

    /**
     * Add company information.
     */
    private function addCompanyInformation(AnnualAccount $annualAccount): void
    {
        $company = CompanySetting::first();

        $this->addFact('ngaap:EntityName', $company?->company_name ?? '', 'ctx_instant');
        $this->addFact('ngaap:EntityOrganisationNumber', $company?->organization_number ?? '', 'ctx_instant');
        $this->addFact('ngaap:FiscalYear', (string) $annualAccount->fiscal_year, 'ctx_instant');
        $this->addFact('ngaap:CompanySize', $annualAccount->company_size, 'ctx_instant');
        $this->addFact('ngaap:AverageNumberOfEmployees', (string) $annualAccount->average_employees, 'ctx_duration');

        if ($annualAccount->auditor_name) {
            $this->addFact('ngaap:AuditorName', $annualAccount->auditor_name, 'ctx_instant');
            $this->addFact('ngaap:AuditOpinion', $annualAccount->audit_opinion ?? '', 'ctx_instant');
        }
    }

    /**
     * Add income statement facts.
     */
    private function addIncomeStatement(AnnualAccount $annualAccount): void
    {
        $this->addMonetaryFact('ngaap:Revenue', $annualAccount->revenue, 'ctx_duration');
        $this->addMonetaryFact('ngaap:OperatingProfit', $annualAccount->operating_profit, 'ctx_duration');
        $this->addMonetaryFact('ngaap:ProfitBeforeTax', $annualAccount->profit_before_tax, 'ctx_duration');
        $this->addMonetaryFact('ngaap:NetProfit', $annualAccount->net_profit, 'ctx_duration');
    }

    /**
     * Add balance sheet facts.
     */
    private function addBalanceSheet(AnnualAccount $annualAccount): void
    {
        $this->addMonetaryFact('ngaap:TotalAssets', $annualAccount->total_assets, 'ctx_instant');
        $this->addMonetaryFact('ngaap:TotalEquity', $annualAccount->total_equity, 'ctx_instant');
        $this->addMonetaryFact('ngaap:TotalLiabilities', $annualAccount->total_liabilities, 'ctx_instant');
    }

    /**
     * Add cash flow statement facts.
     */
    private function addCashFlowStatement(AnnualAccount $annualAccount): void
    {
        $cf = $annualAccount->cashFlowStatement;

        $this->addMonetaryFact('ngaap:NetCashFromOperatingActivities', $cf->net_operating_cash_flow, 'ctx_duration');
        $this->addMonetaryFact('ngaap:NetCashFromInvestingActivities', $cf->net_investing_cash_flow, 'ctx_duration');
        $this->addMonetaryFact('ngaap:NetCashFromFinancingActivities', $cf->net_financing_cash_flow, 'ctx_duration');
        $this->addMonetaryFact('ngaap:NetChangeInCash', $cf->net_change_in_cash, 'ctx_duration');
        $this->addMonetaryFact('ngaap:OpeningCashBalance', $cf->opening_cash_balance, 'ctx_instant');
        $this->addMonetaryFact('ngaap:ClosingCashBalance', $cf->closing_cash_balance, 'ctx_instant');
    }

    /**
     * Add notes as text blocks.
     */
    private function addNotes(AnnualAccount $annualAccount): void
    {
        foreach ($annualAccount->notes()->visible()->ordered()->get() as $note) {
            $conceptName = $this->getNoteConceptName($note->note_type);
            $this->addFact($conceptName, $note->content, 'ctx_duration');
        }
    }

    /**
     * Map note type to XBRL concept name.
     */
    private function getNoteConceptName(string $noteType): string
    {
        return match ($noteType) {
            'accounting_principles' => 'ngaap:AccountingPoliciesTextBlock',
            'employees' => 'ngaap:EmployeesAndRemunerationTextBlock',
            'fixed_assets' => 'ngaap:FixedAssetsTextBlock',
            'share_capital' => 'ngaap:ShareCapitalTextBlock',
            'equity' => 'ngaap:EquityTextBlock',
            'debt' => 'ngaap:DebtTextBlock',
            'tax' => 'ngaap:TaxTextBlock',
            'related_parties' => 'ngaap:RelatedPartiesTextBlock',
            'subsequent_events' => 'ngaap:SubsequentEventsTextBlock',
            default => 'ngaap:OtherNotesTextBlock',
        };
    }

    /**
     * Add a simple fact element.
     */
    private function addFact(string $concept, string $value, string $contextRef): void
    {
        if (empty($value)) {
            return;
        }

        $fact = $this->doc->createElement($concept, htmlspecialchars($value));
        $fact->setAttribute('contextRef', $contextRef);
        $this->root->appendChild($fact);
    }

    /**
     * Add a monetary fact element.
     */
    private function addMonetaryFact(string $concept, float $value, string $contextRef): void
    {
        $fact = $this->doc->createElement($concept, number_format($value, 2, '.', ''));
        $fact->setAttribute('contextRef', $contextRef);
        $fact->setAttribute('unitRef', $this->unitId);
        $fact->setAttribute('decimals', '2');
        $this->root->appendChild($fact);
    }

    /**
     * Validate XBRL document structure.
     */
    public function validate(string $xbrlContent): array
    {
        $errors = [];
        $warnings = [];

        $doc = new DOMDocument;
        $doc->loadXML($xbrlContent);

        // Check for required contexts
        $contexts = $doc->getElementsByTagNameNS(self::NS_XBRLI, 'context');
        if ($contexts->length < 2) {
            $errors[] = 'Mangler påkrevde kontekster (instant og duration).';
        }

        // Check for required facts
        $requiredFacts = [
            'ngaap:EntityOrganisationNumber',
            'ngaap:Revenue',
            'ngaap:TotalAssets',
            'ngaap:TotalEquity',
        ];

        foreach ($requiredFacts as $factName) {
            $facts = $doc->getElementsByTagName(explode(':', $factName)[1]);
            if ($facts->length === 0) {
                $errors[] = "Mangler påkrevd element: {$factName}";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Get file name for XBRL document.
     */
    public function getFileName(AnnualAccount $annualAccount): string
    {
        $company = CompanySetting::first();
        $orgNumber = $company?->organization_number ?? '000000000';

        return sprintf(
            'arsregnskap_%s_%d.xbrl',
            $orgNumber,
            $annualAccount->fiscal_year
        );
    }
}
