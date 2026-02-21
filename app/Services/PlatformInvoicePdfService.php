<?php

namespace App\Services;

use App\Models\CompanyModule;
use App\Models\PlatformInvoice;
use Barryvdh\DomPDF\Facade\Pdf;

class PlatformInvoicePdfService
{
    public function generate(PlatformInvoice $invoice): \Barryvdh\DomPDF\PDF
    {
        $invoice->loadMissing('company');

        $lines = CompanyModule::where('company_id', $invoice->company_id)
            ->with('module')
            ->get()
            ->filter(fn ($cm) => $cm->isActive() && $cm->module?->price_monthly > 0)
            ->map(fn ($cm) => [
                'name' => $cm->module->name,
                'amount' => $cm->module->price_monthly,
            ])
            ->values();

        return Pdf::loadView('pdf.platform-invoice', [
            'invoice' => $invoice,
            'company' => $invoice->company,
            'lines' => $lines,
            'sender' => $this->getSenderInfo(),
        ])
            ->setPaper('a4')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);
    }

    public function getFilename(PlatformInvoice $invoice): string
    {
        return 'faktura-'.$invoice->invoice_number.'.pdf';
    }

    /**
     * @return array<string, string|null>
     */
    private function getSenderInfo(): array
    {
        return [
            'name' => config('company.name', 'Konrad Office AS'),
            'address' => config('company.address'),
            'postal_code' => config('company.postal_code'),
            'city' => config('company.city'),
            'org_number' => config('company.org_number'),
            'bank_account' => config('company.bank_account'),
            'email' => config('company.email'),
        ];
    }
}
