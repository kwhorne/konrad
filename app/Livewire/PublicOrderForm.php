<?php

namespace App\Livewire;

use Flux\Flux;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class PublicOrderForm extends Component
{
    public string $plan = '';

    public string $companyName = '';

    public string $orgNumber = '';

    public string $address = '';

    public string $postalCode = '';

    public string $city = '';

    public string $contactName = '';

    public string $email = '';

    public string $phone = '';

    public string $comments = '';

    public bool $terms = false;

    public bool $submitted = false;

    public function mount(): void
    {
        $this->plan = request('plan', '');
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'plan' => ['required', 'in:basis,pro,premium'],
            'companyName' => ['required', 'string', 'max:200'],
            'orgNumber' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:200'],
            'postalCode' => ['required', 'string', 'max:10'],
            'city' => ['required', 'string', 'max:100'],
            'contactName' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'comments' => ['nullable', 'string', 'max:2000'],
            'terms' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'plan.required' => 'Velg en plan.',
            'plan.in' => 'Ugyldig plan valgt.',
            'companyName.required' => 'Firmanavn er påkrevd.',
            'orgNumber.required' => 'Organisasjonsnummer er påkrevd.',
            'address.required' => 'Adresse er påkrevd.',
            'postalCode.required' => 'Postnummer er påkrevd.',
            'city.required' => 'Poststed er påkrevd.',
            'contactName.required' => 'Navn er påkrevd.',
            'email.required' => 'E-postadresse er påkrevd.',
            'email.email' => 'Ugyldig e-postadresse.',
            'phone.required' => 'Telefonnummer er påkrevd.',
            'terms.accepted' => 'Du må godta vilkårene.',
        ];
    }

    public function submit(): void
    {
        $this->validate();

        $data = [
            'plan' => $this->planLabel(),
            'companyName' => $this->companyName,
            'orgNumber' => $this->orgNumber,
            'address' => $this->address,
            'postalCode' => $this->postalCode,
            'city' => $this->city,
            'contactName' => $this->contactName,
            'email' => $this->email,
            'phone' => $this->phone,
            'comments' => $this->comments,
        ];

        Mail::send([], [], function ($mail) use ($data) {
            $mail->to(config('mail.contact_address', 'post@konradoffice.no'))
                ->replyTo($data['email'], $data['contactName'])
                ->subject('Ny bestilling: '.$data['plan'].' — '.$data['companyName'])
                ->html($this->buildEmailHtml($data));
        });

        $this->submitted = true;
        Flux::toast(text: 'Bestillingen er sendt! Vi tar kontakt innen én virkedag.', variant: 'success');
    }

    private function planLabel(): string
    {
        return match ($this->plan) {
            'pro' => 'Pro (890 kr/mnd)',
            'premium' => 'Premium (1 890 kr/mnd)',
            default => 'Basis (380 kr/mnd)',
        };
    }

    private function buildEmailHtml(array $data): string
    {
        return '
            <h2>Ny bestilling via bestillingsskjema</h2>
            <h3>Plan</h3>
            <p>'.e($data['plan']).'</p>
            <h3>Firmainformasjon</h3>
            <table>
                <tr><td><strong>Firmanavn:</strong></td><td>'.e($data['companyName']).'</td></tr>
                <tr><td><strong>Org.nr:</strong></td><td>'.e($data['orgNumber']).'</td></tr>
                <tr><td><strong>Adresse:</strong></td><td>'.e($data['address']).'</td></tr>
                <tr><td><strong>Postnummer:</strong></td><td>'.e($data['postalCode']).'</td></tr>
                <tr><td><strong>Poststed:</strong></td><td>'.e($data['city']).'</td></tr>
            </table>
            <h3>Kontaktperson</h3>
            <table>
                <tr><td><strong>Navn:</strong></td><td>'.e($data['contactName']).'</td></tr>
                <tr><td><strong>E-post:</strong></td><td>'.e($data['email']).'</td></tr>
                <tr><td><strong>Telefon:</strong></td><td>'.e($data['phone']).'</td></tr>
            </table>
            '.($data['comments'] ? '<h3>Kommentarer</h3><p>'.nl2br(e($data['comments'])).'</p>' : '').'
        ';
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.public-order-form');
    }
}
