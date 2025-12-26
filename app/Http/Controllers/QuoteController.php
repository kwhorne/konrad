<?php

namespace App\Http\Controllers;

use App\Mail\DocumentMail;
use App\Models\Quote;
use App\Services\DocumentPdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

class QuoteController extends Controller
{
    public function index()
    {
        return view('quotes.index');
    }

    public function create()
    {
        // Return view directly to preserve query parameters (contact_id)
        return view('quotes.index');
    }

    public function show()
    {
        return redirect()->route('quotes.index');
    }

    public function edit()
    {
        return redirect()->route('quotes.index');
    }

    public function pdf(Quote $quote, DocumentPdfService $pdfService): Response
    {
        $pdf = $pdfService->generateQuotePdf($quote);
        $filename = 'Tilbud-'.$quote->quote_number.'.pdf';

        return $pdf->download($filename);
    }

    public function preview(Quote $quote, DocumentPdfService $pdfService): Response
    {
        $pdf = $pdfService->generateQuotePdf($quote);

        return $pdf->stream();
    }

    public function send(Quote $quote): RedirectResponse
    {
        $email = $quote->contact?->email;

        if (! $email) {
            return redirect()->route('quotes.index')
                ->with('error', 'Kontakten har ingen e-postadresse.');
        }

        Mail::to($email)->send(new DocumentMail($quote));

        $quote->update(['sent_at' => now()]);

        // Update status to 'sent' if it exists
        $sentStatus = \App\Models\QuoteStatus::where('code', 'sent')->first();
        if ($sentStatus) {
            $quote->update(['quote_status_id' => $sentStatus->id]);
        }

        return redirect()->route('quotes.index')
            ->with('success', 'Tilbudet ble sendt til '.$email);
    }
}
