<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompanyUserInvitation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user,
        public Company $company,
        public string $token,
        public string $role,
        public ?User $invitedBy = null,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Du er invitert til {$this->company->name}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.company-user-invitation',
            with: [
                'user' => $this->user,
                'company' => $this->company,
                'token' => $this->token,
                'role' => $this->role,
                'invitedBy' => $this->invitedBy,
                'acceptUrl' => route('invitation.accept', ['token' => $this->token]),
                'roleName' => $this->getRoleName(),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Get the translated role name.
     */
    private function getRoleName(): string
    {
        return match ($this->role) {
            'owner' => 'eier',
            'manager' => 'administrator',
            'member' => 'medlem',
            default => 'bruker',
        };
    }
}
