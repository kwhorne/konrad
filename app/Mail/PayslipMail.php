<?php

namespace App\Mail;

use App\Models\PayrollEntry;
use App\Services\Payroll\PayslipPdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PayslipMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $employeeName;

    public string $periodLabel;

    public string $companyName;

    public string $nettolonn;

    public string $paymentDate;

    public bool $hasPassword;

    /**
     * Create a new message instance.
     */
    public function __construct(
        private PayrollEntry $entry,
        private ?string $password = null
    ) {
        $this->employeeName = $entry->user->name;
        $this->periodLabel = $entry->payrollRun->period_label;
        $this->companyName = config('company.name', config('app.name'));
        $this->nettolonn = 'kr '.number_format($entry->nettolonn, 2, ',', ' ');
        $this->paymentDate = $entry->payrollRun->utbetalingsdato->format('d.m.Y');
        $this->hasPassword = ! empty($password);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Lønnsslipp {$this->periodLabel} - {$this->companyName}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.payslip',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $pdfService = app(PayslipPdfService::class);

        $pdfContent = $pdfService->generatePayslip($this->entry, $this->password);
        $filename = $pdfService->getFilename($this->entry);

        return [
            Attachment::fromData(fn () => $pdfContent, $filename)
                ->withMime('application/pdf'),
        ];
    }
}
