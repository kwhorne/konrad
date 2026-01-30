<?php

use App\Models\Contact;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\OrderStatus;
use App\Models\Quote;
use App\Models\QuoteLine;
use App\Models\QuoteStatus;
use App\Models\User;
use App\Services\DocumentConversionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(DocumentConversionService::class);
});

it('converts quote to order', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $contact = Contact::factory()->create();
    QuoteStatus::factory()->create(['code' => 'sent', 'name' => 'Sendt']);
    QuoteStatus::factory()->create(['code' => 'converted', 'name' => 'Konvertert']);

    $quote = Quote::factory()->create([
        'contact_id' => $contact->id,
        'quote_status_id' => QuoteStatus::where('code', 'sent')->first()->id,
        'title' => 'Test Quote',
        'subtotal' => 1000,
        'discount_total' => 100,
        'vat_total' => 225,
        'total' => 1125,
        'valid_until' => now()->addDays(30),
    ]);

    QuoteLine::factory()->create([
        'quote_id' => $quote->id,
        'description' => 'Line 1',
        'quantity' => 10,
        'unit_price' => 100,
        'discount_percent' => 0,
        'vat_percent' => 15,
    ]);

    $order = $this->service->convertQuoteToOrder($quote);

    expect($order)->toBeInstanceOf(Order::class);
    expect($order->quote_id)->toBe($quote->id);
    expect($order->title)->toBe('Test Quote');
    expect($order->contact_id)->toBe($contact->id);
    expect($order->total)->toBe('1125.00');
    expect($order->lines)->toHaveCount(1);
    expect($order->lines->first()->description)->toBe('Line 1');

    $quote->refresh();
    expect($quote->quoteStatus->code)->toBe('converted');
});

it('throws exception when quote cannot be converted', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $draftStatus = QuoteStatus::factory()->create(['code' => 'draft', 'name' => 'Utkast']);

    $quote = Quote::factory()->create([
        'quote_status_id' => $draftStatus->id,
    ]);

    $this->service->convertQuoteToOrder($quote);
})->throws(\InvalidArgumentException::class, 'Tilbudet kan ikke konverteres til ordre.');

it('throws exception when quote is expired', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $sentStatus = QuoteStatus::factory()->create(['code' => 'sent', 'name' => 'Sendt']);

    $quote = Quote::factory()->create([
        'quote_status_id' => $sentStatus->id,
        'valid_until' => now()->subDays(1),
    ]);

    $this->service->convertQuoteToOrder($quote);
})->throws(\InvalidArgumentException::class, 'Tilbudet kan ikke konverteres til ordre.');

it('converts order to invoice', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $contact = Contact::factory()->create();
    OrderStatus::factory()->create(['code' => 'confirmed', 'name' => 'Bekreftet']);
    OrderStatus::factory()->create(['code' => 'invoiced', 'name' => 'Fakturert']);

    $order = Order::factory()->create([
        'contact_id' => $contact->id,
        'order_status_id' => OrderStatus::where('code', 'confirmed')->first()->id,
        'title' => 'Test Order',
        'payment_terms_days' => 14,
        'subtotal' => 2000,
        'discount_total' => 200,
        'vat_total' => 450,
        'total' => 2250,
    ]);

    OrderLine::factory()->create([
        'order_id' => $order->id,
        'description' => 'Order Line 1',
        'quantity' => 5,
        'unit_price' => 400,
        'discount_percent' => 0,
        'vat_percent' => 15,
    ]);

    $invoice = $this->service->convertOrderToInvoice($order);

    expect($invoice)->toBeInstanceOf(Invoice::class);
    expect($invoice->order_id)->toBe($order->id);
    expect($invoice->title)->toBe('Test Order');
    expect($invoice->contact_id)->toBe($contact->id);
    expect($invoice->total)->toBe('2250.00');
    expect($invoice->balance)->toBe('2250.00');
    expect($invoice->payment_terms_days)->toBe(14);
    expect($invoice->lines)->toHaveCount(1);
    expect($invoice->lines->first()->description)->toBe('Order Line 1');
    expect($invoice->due_date)->not->toBeNull();
    expect($invoice->reminder_date)->not->toBeNull();

    $order->refresh();
    expect($order->orderStatus->code)->toBe('invoiced');
});

it('throws exception when order cannot be converted', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $draftStatus = OrderStatus::factory()->create(['code' => 'draft', 'name' => 'Utkast']);

    $order = Order::factory()->create([
        'order_status_id' => $draftStatus->id,
    ]);

    $this->service->convertOrderToInvoice($order);
})->throws(\InvalidArgumentException::class, 'Ordren kan ikke konverteres til faktura.');

it('can check if quote can be converted', function () {
    $sentStatus = QuoteStatus::factory()->create(['code' => 'sent', 'name' => 'Sendt']);

    $convertableQuote = Quote::factory()->create([
        'quote_status_id' => $sentStatus->id,
        'valid_until' => now()->addDays(30),
    ]);

    $draftStatus = QuoteStatus::factory()->create(['code' => 'draft', 'name' => 'Utkast']);
    $nonConvertableQuote = Quote::factory()->create([
        'quote_status_id' => $draftStatus->id,
    ]);

    expect($this->service->canConvertQuote($convertableQuote))->toBeTrue();
    expect($this->service->canConvertQuote($nonConvertableQuote))->toBeFalse();
});

it('can check if order can be converted', function () {
    $confirmedStatus = OrderStatus::factory()->create(['code' => 'confirmed', 'name' => 'Bekreftet']);

    $convertableOrder = Order::factory()->create([
        'order_status_id' => $confirmedStatus->id,
    ]);

    $draftStatus = OrderStatus::factory()->create(['code' => 'draft', 'name' => 'Utkast']);
    $nonConvertableOrder = Order::factory()->create([
        'order_status_id' => $draftStatus->id,
    ]);

    expect($this->service->canConvertOrder($convertableOrder))->toBeTrue();
    expect($this->service->canConvertOrder($nonConvertableOrder))->toBeFalse();
});
