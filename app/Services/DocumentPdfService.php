<?php

namespace App\Services;

use App\Models\CompanySetting;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Quote;
use Barryvdh\DomPDF\Facade\Pdf;

class DocumentPdfService
{
    public function generateQuotePdf(Quote $quote): \Barryvdh\DomPDF\PDF
    {
        $quote->load(['contact', 'lines.product', 'lines.vatRate', 'quoteStatus', 'creator', 'project']);

        return Pdf::loadView('pdf.quote', [
            'quote' => $quote,
            'company' => $this->getCompanyInfo(),
        ])
            ->setPaper('a4')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);
    }

    public function generateOrderPdf(Order $order): \Barryvdh\DomPDF\PDF
    {
        $order->load(['contact', 'lines.product', 'lines.vatRate', 'orderStatus', 'creator', 'project']);

        return Pdf::loadView('pdf.order', [
            'order' => $order,
            'company' => $this->getCompanyInfo(),
        ])
            ->setPaper('a4')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);
    }

    public function generateInvoicePdf(Invoice $invoice): \Barryvdh\DomPDF\PDF
    {
        $invoice->load(['contact', 'lines.product', 'lines.vatRate', 'invoiceStatus', 'creator', 'project', 'payments']);

        return Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'company' => $this->getCompanyInfo(),
        ])
            ->setPaper('a4')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);
    }

    /**
     * @return array<string, mixed>
     */
    private function getCompanyInfo(): array
    {
        $settings = CompanySetting::current();
        $defaultLogoUrl = $this->getDefaultLogoUrl();

        if (! $settings) {
            // Fallback to config if no settings in database
            return [
                'name' => config('company.name', config('app.name')),
                'address' => config('company.address'),
                'postal_code' => config('company.postal_code'),
                'city' => config('company.city'),
                'country' => config('company.country', 'Norge'),
                'org_number' => config('company.org_number'),
                'bank_account' => config('company.bank_account'),
                'email' => config('company.email'),
                'phone' => config('company.phone'),
                'website' => config('company.website'),
                'logo_path' => config('company.logo_path'),
                'logo_url' => $defaultLogoUrl,
            ];
        }

        // Get the logo URL - use company logo if available, otherwise fallback to default
        $logoUrl = $settings->hasLogo() ? $this->getAbsoluteLogoUrl($settings) : $defaultLogoUrl;

        return [
            'name' => $settings->company_name,
            'address' => $settings->address,
            'postal_code' => $settings->postal_code,
            'city' => $settings->city,
            'country' => $settings->country ?? 'Norge',
            'org_number' => $settings->formatted_org_number ?? $settings->organization_number,
            'vat_number' => $settings->vat_number,
            'bank_name' => $settings->bank_name,
            'bank_account' => $settings->formatted_bank_account ?? $settings->bank_account,
            'iban' => $settings->iban,
            'swift' => $settings->swift,
            'email' => $settings->email,
            'phone' => $settings->phone,
            'website' => $settings->website,
            'logo_path' => $settings->logo_path,
            'logo_url' => $logoUrl,
            'invoice_terms' => $settings->invoice_terms,
            'quote_terms' => $settings->quote_terms,
            'order_terms' => $settings->order_terms,
            'document_footer' => $settings->document_footer,
        ];
    }

    /**
     * Get the default Konrad Office logo URL for PDFs.
     */
    private function getDefaultLogoUrl(): string
    {
        $logoPath = public_path('images/logo/logo-light.png');

        if (file_exists($logoPath)) {
            return $logoPath;
        }

        return '';
    }

    /**
     * Get the absolute logo URL for a company setting (for PDF rendering).
     */
    private function getAbsoluteLogoUrl(CompanySetting $settings): string
    {
        if (! $settings->logo_path) {
            return '';
        }

        // For local storage, return the absolute file path for DomPDF
        $storagePath = storage_path('app/public/'.$settings->logo_path);

        if (file_exists($storagePath)) {
            return $storagePath;
        }

        // Fallback to storage URL
        return $settings->logo_url ?? '';
    }

    public function formatCurrency(float $amount): string
    {
        return 'kr '.number_format($amount, 2, ',', ' ');
    }

    public function formatDate(?\DateTimeInterface $date): string
    {
        return $date ? $date->format('d.m.Y') : '';
    }
}
