<?php

namespace App\Mail;

use App\Models\PlatformInvoice;
use App\Services\PlatformInvoicePdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PlatformInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $companyName;

    public string $invoiceNumber;

    public string $amountFormatted;

    public string $dueDateFormatted;

    public function __construct(public PlatformInvoice $invoice)
    {
        $this->companyName = $invoice->company->name;
        $this->invoiceNumber = $invoice->invoice_number;
        $this->amountFormatted = $invoice->amount_formatted;
        $this->dueDateFormatted = $invoice->due_date->format('d.m.Y');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Faktura {$this->invoiceNumber} fra Konrad Office AS",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.platform-invoice',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $pdfService = app(PlatformInvoicePdfService::class);
        $pdf = $pdfService->generate($this->invoice);
        $filename = $pdfService->getFilename($this->invoice);

        return [
            Attachment::fromData(fn () => $pdf->output(), $filename)
                ->withMime('application/pdf'),
        ];
    }
}
