<?php

namespace App\Http\Controllers;

use App\Mail\DocumentMail;
use App\Models\Invoice;
use App\Services\DocumentPdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    public function index()
    {
        return view('invoices.index');
    }

    public function create()
    {
        // Return view directly to preserve query parameters (contact_id)
        return view('invoices.index');
    }

    public function show()
    {
        return redirect()->route('invoices.index');
    }

    public function edit()
    {
        return redirect()->route('invoices.index');
    }

    public function pdf(Invoice $invoice, DocumentPdfService $pdfService): Response
    {
        $pdf = $pdfService->generateInvoicePdf($invoice);
        $prefix = $invoice->is_credit_note ? 'Kreditnota' : 'Faktura';
        $filename = $prefix.'-'.$invoice->invoice_number.'.pdf';

        return $pdf->download($filename);
    }

    public function preview(Invoice $invoice, DocumentPdfService $pdfService): Response
    {
        $pdf = $pdfService->generateInvoicePdf($invoice);

        return $pdf->stream();
    }

    public function send(Invoice $invoice): RedirectResponse
    {
        $email = $invoice->contact?->email;

        if (! $email) {
            return redirect()->route('invoices.index')
                ->with('error', 'Kontakten har ingen e-postadresse.');
        }

        Mail::to($email)->send(new DocumentMail($invoice));

        $invoice->update(['sent_at' => now()]);

        // Update status to 'sent' if currently draft
        $sentStatus = \App\Models\InvoiceStatus::where('code', 'sent')->first();
        if ($sentStatus && $invoice->invoiceStatus?->code === 'draft') {
            $invoice->update(['invoice_status_id' => $sentStatus->id]);
        }

        $documentType = $invoice->is_credit_note ? 'Kreditnotaen' : 'Fakturaen';

        return redirect()->route('invoices.index')
            ->with('success', $documentType.' ble sendt til '.$email);
    }
}
