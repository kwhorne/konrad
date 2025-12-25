<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AltinnDeadlineReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private array $deadline,
        private int $daysUntil
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $urgencyLevel = $this->getUrgencyLevel();
        $subject = $this->getSubject();

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting("Hei {$notifiable->name}!");

        if ($this->daysUntil <= 1) {
            $message->line("**KRITISK:** Fristen for {$this->deadline['name']} er i morgen!")
                ->line("Du må sende inn {$this->deadline['code']} til {$this->deadline['recipient']} innen {$this->deadline['deadline']->format('d.m.Y')}.");
        } elseif ($this->daysUntil <= 7) {
            $message->line("**Viktig påminnelse:** Fristen for {$this->deadline['name']} nærmer seg raskt.")
                ->line("Du har {$this->daysUntil} dager igjen til å sende inn {$this->deadline['code']} til {$this->deadline['recipient']}.");
        } else {
            $message->line("Påminnelse om kommende frist for {$this->deadline['name']}.")
                ->line("Fristen er {$this->deadline['deadline']->format('d.m.Y')} ({$this->daysUntil} dager igjen).");
        }

        $message->line('**Detaljer:**')
            ->line("- Innsending: {$this->deadline['name']}")
            ->line("- Skjema: {$this->deadline['code']}")
            ->line("- Regnskapsår: {$this->deadline['fiscal_year']}")
            ->line("- Mottaker: {$this->deadline['recipient']}")
            ->line("- Frist: {$this->deadline['deadline']->format('d.m.Y')}");

        $statusLabel = $this->getStatusLabel();
        $message->line("- Status: {$statusLabel}");

        $message->action('Gå til Altinn-oversikt', url('/altinn'))
            ->line('Sørg for at alle data er korrekte før innsending.');

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'altinn_deadline_reminder',
            'deadline_type' => $this->deadline['type'],
            'deadline_name' => $this->deadline['name'],
            'deadline_code' => $this->deadline['code'],
            'deadline_date' => $this->deadline['deadline']->toDateString(),
            'days_until' => $this->daysUntil,
            'fiscal_year' => $this->deadline['fiscal_year'],
            'recipient' => $this->deadline['recipient'],
            'status' => $this->deadline['status'],
        ];
    }

    private function getUrgencyLevel(): string
    {
        if ($this->daysUntil <= 1) {
            return 'critical';
        }
        if ($this->daysUntil <= 7) {
            return 'high';
        }
        if ($this->daysUntil <= 14) {
            return 'medium';
        }

        return 'low';
    }

    private function getSubject(): string
    {
        if ($this->daysUntil <= 1) {
            return "⚠️ KRITISK: {$this->deadline['name']} forfaller i morgen!";
        }
        if ($this->daysUntil <= 7) {
            return "⏰ HASTER: {$this->deadline['name']} - {$this->daysUntil} dager igjen";
        }

        return "📋 Påminnelse: {$this->deadline['name']} - frist {$this->deadline['deadline']->format('d.m.Y')}";
    }

    private function getStatusLabel(): string
    {
        return match ($this->deadline['status']) {
            'not_started' => 'Ikke startet',
            'draft' => 'Under arbeid',
            'ready', 'approved' => 'Klar for innsending',
            'submitted' => 'Sendt inn',
            'accepted' => 'Akseptert',
            'rejected' => 'Avvist',
            default => ucfirst($this->deadline['status']),
        };
    }
}
