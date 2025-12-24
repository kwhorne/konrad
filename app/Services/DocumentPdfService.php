<?php

namespace App\Services;

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
        return [
            'name' => config('company.name'),
            'address' => config('company.address'),
            'postal_code' => config('company.postal_code'),
            'city' => config('company.city'),
            'country' => config('company.country'),
            'org_number' => config('company.org_number'),
            'bank_account' => config('company.bank_account'),
            'email' => config('company.email'),
            'phone' => config('company.phone'),
            'website' => config('company.website'),
            'logo_path' => config('company.logo_path'),
        ];
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
