<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Quote;
use App\Services\DocumentPdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $documentType;

    public string $documentNumber;

    public string $customerName;

    public string $companyName;

    /**
     * @var array<string, mixed>
     */
    public array $company;

    private Quote|Order|Invoice $document;

    /**
     * Create a new message instance.
     */
    public function __construct(Quote|Order|Invoice $document)
    {
        $this->document = $document;
        $this->company = config('company');
        $this->companyName = $this->company['name'];

        if ($document instanceof Quote) {
            $this->documentType = 'tilbud';
            $this->documentNumber = $document->quote_number;
        } elseif ($document instanceof Order) {
            $this->documentType = 'ordre';
            $this->documentNumber = $document->order_number;
        } else {
            $this->documentType = $document->is_credit_note ? 'kreditnota' : 'faktura';
            $this->documentNumber = $document->invoice_number;
        }

        $this->customerName = $document->customer_name ?? 'Kunde';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = ucfirst($this->documentType).' '.$this->documentNumber.' fra '.$this->companyName;

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.document',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $pdfService = app(DocumentPdfService::class);

        if ($this->document instanceof Quote) {
            $pdf = $pdfService->generateQuotePdf($this->document);
            $filename = 'Tilbud-'.$this->document->quote_number.'.pdf';
        } elseif ($this->document instanceof Order) {
            $pdf = $pdfService->generateOrderPdf($this->document);
            $filename = 'Ordre-'.$this->document->order_number.'.pdf';
        } else {
            $pdf = $pdfService->generateInvoicePdf($this->document);
            $prefix = $this->document->is_credit_note ? 'Kreditnota' : 'Faktura';
            $filename = $prefix.'-'.$this->document->invoice_number.'.pdf';
        }

        return [
            Attachment::fromData(fn () => $pdf->output(), $filename)
                ->withMime('application/pdf'),
        ];
    }
}
