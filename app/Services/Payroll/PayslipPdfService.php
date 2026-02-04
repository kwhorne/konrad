<?php

namespace App\Services\Payroll;

use App\Models\CompanySetting;
use App\Models\EmployeePayrollSettings;
use App\Models\PayrollEntry;
use TCPDF;

class PayslipPdfService
{
    /**
     * Generate a password-protected PDF payslip.
     *
     * @return string The PDF content
     */
    public function generatePayslip(PayrollEntry $entry, ?string $password = null): string
    {
        $entry->load(['user', 'payrollRun', 'lines.payType']);

        // Get the password from personnummer if not provided
        if ($password === null) {
            $password = $this->getPasswordForEmployee($entry->user_id);
        }

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('Konrad Office');
        $pdf->SetAuthor($this->getCompanyName());
        $pdf->SetTitle('Lønnsslipp '.$entry->payrollRun->period_label);
        $pdf->SetSubject('Lønnsslipp');

        // Set protection with password
        if ($password) {
            $pdf->SetProtection(
                ['print', 'copy'],
                $password,  // User password (to open)
                null,       // Owner password (for full access)
                0,          // Encryption strength (0 = RC4 40bit, 1 = RC4 128bit, 2 = AES 128bit, 3 = AES 256bit)
                null        // Public key certificate
            );
        }

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);

        // Add a page
        $pdf->AddPage();

        // Generate content
        $this->renderPayslipContent($pdf, $entry);

        return $pdf->Output('', 'S');
    }

    /**
     * Get the filename for a payslip.
     */
    public function getFilename(PayrollEntry $entry): string
    {
        $period = $entry->payrollRun->year.'-'.str_pad($entry->payrollRun->month, 2, '0', STR_PAD_LEFT);

        return "Lønnsslipp-{$period}-{$entry->user->name}.pdf";
    }

    /**
     * Get the password for an employee (last 5 digits of personnummer).
     */
    public function getPasswordForEmployee(int $userId): ?string
    {
        $settings = EmployeePayrollSettings::where('user_id', $userId)->first();

        if (! $settings || ! $settings->personnummer) {
            return null;
        }

        return substr($settings->personnummer, -5);
    }

    /**
     * Get the email address to send payslip to.
     */
    public function getEmailForEmployee(int $userId): ?string
    {
        $settings = EmployeePayrollSettings::where('user_id', $userId)->first();

        // Use personal email if set, otherwise use user email
        if ($settings && $settings->personal_email) {
            return $settings->personal_email;
        }

        $user = \App\Models\User::find($userId);

        return $user?->email;
    }

    /**
     * Render the payslip content to the PDF.
     */
    protected function renderPayslipContent(TCPDF $pdf, PayrollEntry $entry): void
    {
        $company = $this->getCompanyInfo();
        $run = $entry->payrollRun;

        // Company header
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, $company['name'], 0, 1, 'L');

        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(0, 5, $company['address'] ?? '', 0, 1, 'L');
        $pdf->Cell(0, 5, trim(($company['postal_code'] ?? '').' '.($company['city'] ?? '')), 0, 1, 'L');
        if ($company['org_number'] ?? null) {
            $pdf->Cell(0, 5, 'Org.nr: '.$company['org_number'], 0, 1, 'L');
        }

        $pdf->Ln(5);

        // Title
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'LØNNSSLIPP', 0, 1, 'C');

        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, $run->period_label, 0, 1, 'C');
        $pdf->Cell(0, 6, 'Utbetalt: '.$run->utbetalingsdato->format('d.m.Y'), 0, 1, 'C');

        $pdf->Ln(5);

        // Employee info
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 8, 'Ansatt', 0, 1, 'L');

        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(40, 6, 'Navn:', 0, 0, 'L');
        $pdf->Cell(0, 6, $entry->user->name, 0, 1, 'L');

        $employeeSettings = EmployeePayrollSettings::where('user_id', $entry->user_id)->first();
        if ($employeeSettings) {
            if ($employeeSettings->ansattnummer) {
                $pdf->Cell(40, 6, 'Ansattnr:', 0, 0, 'L');
                $pdf->Cell(0, 6, $employeeSettings->ansattnummer, 0, 1, 'L');
            }
            if ($employeeSettings->stilling) {
                $pdf->Cell(40, 6, 'Stilling:', 0, 0, 'L');
                $pdf->Cell(0, 6, $employeeSettings->stilling, 0, 1, 'L');
            }
            if ($employeeSettings->kontonummer) {
                $pdf->Cell(40, 6, 'Kontonummer:', 0, 0, 'L');
                $pdf->Cell(0, 6, $this->formatBankAccount($employeeSettings->kontonummer), 0, 1, 'L');
            }
        }

        $pdf->Ln(5);

        // Income section
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 8, 'Inntekter', 0, 1, 'L');

        $pdf->SetFont('helvetica', '', 10);

        // Table header
        $pdf->SetFillColor(240, 240, 240);
        $pdf->Cell(100, 7, 'Beskrivelse', 1, 0, 'L', true);
        $pdf->Cell(35, 7, 'Antall/Sats', 1, 0, 'R', true);
        $pdf->Cell(45, 7, 'Beløp', 1, 1, 'R', true);

        // Income rows
        if ($entry->grunnlonn > 0) {
            $pdf->Cell(100, 6, 'Grunnlønn', 1, 0, 'L');
            $pdf->Cell(35, 6, '', 1, 0, 'R');
            $pdf->Cell(45, 6, $this->formatAmount($entry->grunnlonn), 1, 1, 'R');
        }

        if ($entry->overtid_belop > 0) {
            $pdf->Cell(100, 6, 'Overtid', 1, 0, 'L');
            $hours = $entry->timer_overtid_50 + $entry->timer_overtid_100;
            $pdf->Cell(35, 6, $hours > 0 ? number_format($hours, 1, ',', '').' t' : '', 1, 0, 'R');
            $pdf->Cell(45, 6, $this->formatAmount($entry->overtid_belop), 1, 1, 'R');
        }

        if ($entry->bonus > 0) {
            $pdf->Cell(100, 6, 'Bonus', 1, 0, 'L');
            $pdf->Cell(35, 6, '', 1, 0, 'R');
            $pdf->Cell(45, 6, $this->formatAmount($entry->bonus), 1, 1, 'R');
        }

        if ($entry->tillegg > 0) {
            $pdf->Cell(100, 6, 'Tillegg', 1, 0, 'L');
            $pdf->Cell(35, 6, '', 1, 0, 'R');
            $pdf->Cell(45, 6, $this->formatAmount($entry->tillegg), 1, 1, 'R');
        }

        // Brutto total
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(135, 7, 'Brutto lønn', 1, 0, 'R');
        $pdf->Cell(45, 7, $this->formatAmount($entry->bruttolonn), 1, 1, 'R');

        $pdf->Ln(5);

        // Deductions section
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 8, 'Trekk', 0, 1, 'L');

        $pdf->SetFont('helvetica', '', 10);

        // Table header
        $pdf->SetFillColor(240, 240, 240);
        $pdf->Cell(100, 7, 'Beskrivelse', 1, 0, 'L', true);
        $pdf->Cell(35, 7, 'Prosent', 1, 0, 'R', true);
        $pdf->Cell(45, 7, 'Beløp', 1, 1, 'R', true);

        // Tax
        if ($entry->forskuddstrekk > 0) {
            $taxLabel = 'Forskuddstrekk';
            if ($entry->skatt_type_brukt === 'tabelltrekk') {
                $taxLabel .= ' (tabell)';
            } elseif ($entry->skatteprosent_brukt) {
                $taxLabel .= ' ('.number_format($entry->skatteprosent_brukt, 1, ',', '').'%)';
            }
            $pdf->Cell(100, 6, $taxLabel, 1, 0, 'L');
            $pct = $entry->bruttolonn > 0 ? ($entry->forskuddstrekk / $entry->bruttolonn) * 100 : 0;
            $pdf->Cell(35, 6, number_format($pct, 1, ',', '').'%', 1, 0, 'R');
            $pdf->Cell(45, 6, '-'.$this->formatAmount($entry->forskuddstrekk), 1, 1, 'R');
        }

        if ($entry->fagforening > 0) {
            $pdf->Cell(100, 6, 'Fagforeningskontingent', 1, 0, 'L');
            $pdf->Cell(35, 6, '', 1, 0, 'R');
            $pdf->Cell(45, 6, '-'.$this->formatAmount($entry->fagforening), 1, 1, 'R');
        }

        if ($entry->andre_trekk > 0) {
            $pdf->Cell(100, 6, 'Andre trekk', 1, 0, 'L');
            $pdf->Cell(35, 6, '', 1, 0, 'R');
            $pdf->Cell(45, 6, '-'.$this->formatAmount($entry->andre_trekk), 1, 1, 'R');
        }

        // Total deductions
        $totalTrekk = $entry->forskuddstrekk + $entry->fagforening + $entry->andre_trekk;
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(135, 7, 'Sum trekk', 1, 0, 'R');
        $pdf->Cell(45, 7, '-'.$this->formatAmount($totalTrekk), 1, 1, 'R');

        $pdf->Ln(5);

        // Net salary
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetFillColor(200, 230, 200);
        $pdf->Cell(135, 10, 'Netto til utbetaling', 1, 0, 'R', true);
        $pdf->Cell(45, 10, $this->formatAmount($entry->nettolonn), 1, 1, 'R', true);

        $pdf->Ln(10);

        // Additional info
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(100, 100, 100);

        if ($entry->feriepenger_avsetning > 0) {
            $pdf->Cell(0, 5, 'Feriepenger avsatt denne periode: '.$this->formatAmount($entry->feriepenger_avsetning), 0, 1, 'L');
        }

        if ($entry->otp_belop > 0) {
            $pdf->Cell(0, 5, 'OTP (obligatorisk tjenestepensjon): '.$this->formatAmount($entry->otp_belop), 0, 1, 'L');
        }

        // Footer
        $pdf->Ln(10);
        $pdf->SetTextColor(150, 150, 150);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 5, 'Denne lønnsslippen er generert av Konrad Office', 0, 1, 'C');
        $pdf->Cell(0, 5, 'Eventuelle spørsmål rettes til lønnsavdelingen', 0, 1, 'C');
    }

    /**
     * Format amount as Norwegian currency.
     */
    protected function formatAmount(float $amount): string
    {
        return 'kr '.number_format($amount, 2, ',', ' ');
    }

    /**
     * Format bank account number.
     */
    protected function formatBankAccount(string $account): string
    {
        $account = preg_replace('/[^0-9]/', '', $account);

        if (strlen($account) === 11) {
            return substr($account, 0, 4).'.'.substr($account, 4, 2).'.'.substr($account, 6);
        }

        return $account;
    }

    /**
     * Get company information.
     *
     * @return array<string, mixed>
     */
    protected function getCompanyInfo(): array
    {
        $settings = CompanySetting::current();

        if (! $settings) {
            return [
                'name' => config('company.name', config('app.name')),
                'address' => config('company.address'),
                'postal_code' => config('company.postal_code'),
                'city' => config('company.city'),
                'org_number' => config('company.org_number'),
            ];
        }

        return [
            'name' => $settings->company_name,
            'address' => $settings->address,
            'postal_code' => $settings->postal_code,
            'city' => $settings->city,
            'org_number' => $settings->formatted_org_number ?? $settings->organization_number,
        ];
    }

    /**
     * Get company name.
     */
    protected function getCompanyName(): string
    {
        return $this->getCompanyInfo()['name'] ?? config('app.name');
    }
}
