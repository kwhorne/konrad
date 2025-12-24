<?php

use App\Models\Contact;
use App\Models\Quote;
use App\Models\QuoteLine;
use App\Models\QuoteStatus;
use App\Models\User;
use App\Models\VatRate;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    // Create default statuses if they don't exist
    QuoteStatus::firstOrCreate(
        ['code' => 'draft'],
        ['name' => 'Utkast', 'color' => 'blue', 'is_active' => true, 'sort_order' => 1]
    );
    QuoteStatus::firstOrCreate(
        ['code' => 'sent'],
        ['name' => 'Sendt', 'color' => 'yellow', 'is_active' => true, 'sort_order' => 2]
    );
    QuoteStatus::firstOrCreate(
        ['code' => 'accepted'],
        ['name' => 'Akseptert', 'color' => 'green', 'is_active' => true, 'sort_order' => 3]
    );

    // Create default VAT rate if it doesn't exist
    VatRate::firstOrCreate(
        ['code' => 'standard'],
        ['rate' => 25, 'name' => 'Standard MVA', 'is_default' => true, 'is_active' => true]
    );
});

test('quote has auto-generated number', function () {
    $contact = Contact::factory()->create();
    $quote = Quote::create([
        'title' => 'Test Quote',
        'contact_id' => $contact->id,
        'created_by' => $this->user->id,
    ]);

    expect($quote->quote_number)->toMatch('/^T-\d{4}-\d{4}$/');
});

test('quote can have lines', function () {
    $contact = Contact::factory()->create();
    $vatRate = VatRate::where('code', 'standard')->first();

    $quote = Quote::create([
        'title' => 'Test Quote',
        'contact_id' => $contact->id,
        'created_by' => $this->user->id,
    ]);

    QuoteLine::create([
        'quote_id' => $quote->id,
        'description' => 'Test Product',
        'quantity' => 2,
        'unit' => 'stk',
        'unit_price' => 100,
        'vat_rate_id' => $vatRate->id,
        'vat_percent' => 25,
        'sort_order' => 0,
    ]);

    $quote->refresh();

    expect($quote->lines)->toHaveCount(1);
    expect((float) $quote->subtotal)->toBe(200.0);
    expect((float) $quote->vat_total)->toBe(50.0);
    expect((float) $quote->total)->toBe(250.0);
});

test('quote can be converted to order', function () {
    $contact = Contact::factory()->create();
    $acceptedStatus = QuoteStatus::where('code', 'accepted')->first();
    $vatRate = VatRate::where('code', 'standard')->first();

    $quote = Quote::create([
        'title' => 'Test Quote',
        'contact_id' => $contact->id,
        'quote_status_id' => $acceptedStatus->id,
        'created_by' => $this->user->id,
        'customer_name' => 'Test Company',
        'customer_address' => 'Test Address',
    ]);

    QuoteLine::create([
        'quote_id' => $quote->id,
        'description' => 'Test Product',
        'quantity' => 2,
        'unit' => 'stk',
        'unit_price' => 100,
        'vat_rate_id' => $vatRate->id,
        'vat_percent' => 25,
        'sort_order' => 0,
    ]);

    $quote->refresh();

    expect($quote->can_convert)->toBeTrue();

    $order = $quote->convertToOrder();

    expect($order)->not->toBeNull();
    expect($order->quote_id)->toBe($quote->id);
    expect($order->title)->toBe('Test Quote');
    expect($order->lines)->toHaveCount(1);
    expect((float) $order->total)->toBe(250.0);
});

test('draft quote cannot be converted', function () {
    $contact = Contact::factory()->create();
    $draftStatus = QuoteStatus::where('code', 'draft')->first();

    $quote = Quote::create([
        'title' => 'Draft Quote',
        'contact_id' => $contact->id,
        'quote_status_id' => $draftStatus->id,
        'created_by' => $this->user->id,
    ]);

    expect($quote->can_convert)->toBeFalse();
});

test('quote line calculates totals correctly', function () {
    $contact = Contact::factory()->create();
    $vatRate = VatRate::where('code', 'standard')->first();

    $quote = Quote::create([
        'title' => 'Test Quote',
        'contact_id' => $contact->id,
        'created_by' => $this->user->id,
    ]);

    $line = QuoteLine::create([
        'quote_id' => $quote->id,
        'description' => 'Product with discount',
        'quantity' => 10,
        'unit' => 'stk',
        'unit_price' => 100,
        'discount_percent' => 10,
        'vat_rate_id' => $vatRate->id,
        'vat_percent' => 25,
        'sort_order' => 0,
    ]);

    // 10 * 100 = 1000, minus 10% discount = 900
    expect((float) $line->line_total)->toBe(900.0);

    $quote->refresh();
    expect((float) $quote->subtotal)->toBe(1000.0); // subtotal is before discount
    expect((float) $quote->vat_total)->toBe(225.0);
    expect((float) $quote->total)->toBe(1125.0);
});
