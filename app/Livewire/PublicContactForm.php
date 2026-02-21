<?php

namespace App\Livewire;

use Flux\Flux;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class PublicContactForm extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $company = '';

    public string $topic = 'general';

    public string $message = '';

    public bool $submitted = false;

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'company' => ['nullable', 'string', 'max:150'],
            'topic' => ['required', 'in:general,demo,support,pricing,other'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'name.required' => 'Navn er påkrevd.',
            'email.required' => 'E-postadresse er påkrevd.',
            'email.email' => 'Ugyldig e-postadresse.',
            'message.required' => 'Melding er påkrevd.',
            'message.min' => 'Meldingen må være minst 10 tegn.',
        ];
    }

    public function submit(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'company' => $this->company,
            'topic' => $this->topicLabel(),
            'message' => $this->message,
        ];

        Mail::send([], [], function ($mail) use ($data) {
            $mail->to(config('mail.contact_address', 'post@konradoffice.no'))
                ->replyTo($data['email'], $data['name'])
                ->subject('Kontaktskjema: '.$data['topic'].' fra '.$data['name'])
                ->html($this->buildEmailHtml($data));
        });

        $this->submitted = true;
        $this->reset(['name', 'email', 'phone', 'company', 'topic', 'message']);
        Flux::toast(text: 'Meldingen ble sendt! Vi svarer innen en virkedag.', variant: 'success');
    }

    private function topicLabel(): string
    {
        return match ($this->topic) {
            'demo' => 'Demo-forespørsel',
            'support' => 'Support',
            'pricing' => 'Priser og abonnement',
            'other' => 'Annet',
            default => 'Generell henvendelse',
        };
    }

    private function buildEmailHtml(array $data): string
    {
        return '
            <h2>Ny henvendelse via kontaktskjema</h2>
            <table>
                <tr><td><strong>Navn:</strong></td><td>'.e($data['name']).'</td></tr>
                <tr><td><strong>E-post:</strong></td><td>'.e($data['email']).'</td></tr>
                <tr><td><strong>Telefon:</strong></td><td>'.e($data['phone'] ?: '–').'</td></tr>
                <tr><td><strong>Bedrift:</strong></td><td>'.e($data['company'] ?: '–').'</td></tr>
                <tr><td><strong>Emne:</strong></td><td>'.e($data['topic']).'</td></tr>
            </table>
            <hr>
            <h3>Melding:</h3>
            <p>'.nl2br(e($data['message'])).'</p>
        ';
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.public-contact-form');
    }
}
