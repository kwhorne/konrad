<?php

use App\Livewire\ContactDocumentsManager;
use App\Mail\DocumentMail;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Quote;
use App\Models\QuoteStatus;
use App\Models\User;
use App\Models\VatRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['onboarding_completed' => true]);
    $this->company = Company::factory()->withOwner($this->user)->create();
    $this->user->update(['current_company_id' => $this->company->id]);
    app()->instance('current.company', $this->company);
    $this->actingAs($this->user);

    QuoteStatus::firstOrCreate(
        ['code' => 'draft'],
        ['name' => 'Utkast', 'color' => 'zinc', 'is_active' => true, 'sort_order' => 1]
    );
    QuoteStatus::firstOrCreate(
        ['code' => 'sent'],
        ['name' => 'Sendt', 'color' => 'blue', 'is_active' => true, 'sort_order' => 2]
    );
    QuoteStatus::firstOrCreate(
        ['code' => 'accepted'],
        ['name' => 'Akseptert', 'color' => 'green', 'is_active' => true, 'sort_order' => 3]
    );

    VatRate::firstOrCreate(
        ['code' => 'standard'],
        ['rate' => 25, 'name' => 'Standard MVA', 'is_default' => true, 'is_active' => true]
    );
});

test('can update quote status from document modal', function () {
    $contact = Contact::factory()->create();
    $draftStatus = QuoteStatus::where('code', 'draft')->first();
    $sentStatus = QuoteStatus::where('code', 'sent')->first();

    $quote = Quote::factory()->create([
        'contact_id' => $contact->id,
        'quote_status_id' => $draftStatus->id,
    ]);

    Livewire::test(ContactDocumentsManager::class, ['contactId' => $contact->id])
        ->call('showDetail', 'quote', $quote->id)
        ->assertSet('selectedStatusId', $draftStatus->id)
        ->set('selectedStatusId', $sentStatus->id)
        ->call('updateQuoteStatus');

    expect($quote->fresh()->quote_status_id)->toBe($sentStatus->id);
});

test('can send quote via email from document modal', function () {
    Mail::fake();

    $contact = Contact::factory()->create(['email' => 'test@example.com']);
    $draftStatus = QuoteStatus::where('code', 'draft')->first();

    $quote = Quote::factory()->create([
        'contact_id' => $contact->id,
        'quote_status_id' => $draftStatus->id,
        'sent_at' => null,
    ]);

    Livewire::test(ContactDocumentsManager::class, ['contactId' => $contact->id])
        ->call('showDetail', 'quote', $quote->id)
        ->call('sendQuoteEmail');

    Mail::assertSent(DocumentMail::class, function ($mail) use ($contact) {
        return $mail->hasTo($contact->email);
    });

    $quote->refresh();
    expect($quote->sent_at)->not->toBeNull();
});

test('sending quote updates status to sent', function () {
    Mail::fake();

    $contact = Contact::factory()->create(['email' => 'test@example.com']);
    $draftStatus = QuoteStatus::where('code', 'draft')->first();
    $sentStatus = QuoteStatus::where('code', 'sent')->first();

    $quote = Quote::factory()->create([
        'contact_id' => $contact->id,
        'quote_status_id' => $draftStatus->id,
    ]);

    Livewire::test(ContactDocumentsManager::class, ['contactId' => $contact->id])
        ->call('showDetail', 'quote', $quote->id)
        ->call('sendQuoteEmail')
        ->assertSet('selectedStatusId', $sentStatus->id);

    expect($quote->fresh()->quote_status_id)->toBe($sentStatus->id);
});

test('cannot send quote without contact email', function () {
    Mail::fake();

    $contact = Contact::factory()->create(['email' => null]);
    $quote = Quote::factory()->create(['contact_id' => $contact->id]);

    Livewire::test(ContactDocumentsManager::class, ['contactId' => $contact->id])
        ->call('showDetail', 'quote', $quote->id)
        ->call('sendQuoteEmail');

    Mail::assertNothingSent();
});
