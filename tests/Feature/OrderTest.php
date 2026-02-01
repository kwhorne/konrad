<?php

use App\Models\Company;
use App\Models\Contact;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\OrderStatus;
use App\Models\User;
use App\Models\VatRate;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['onboarding_completed' => true]);
    $this->company = Company::factory()->withOwner($this->user)->create();
    $this->user->update(['current_company_id' => $this->company->id]);
    app()->instance('current.company', $this->company);
    $this->actingAs($this->user);

    // Create default statuses if they don't exist
    OrderStatus::firstOrCreate(
        ['code' => 'draft'],
        ['name' => 'Utkast', 'color' => 'blue', 'is_active' => true, 'sort_order' => 1]
    );
    OrderStatus::firstOrCreate(
        ['code' => 'confirmed'],
        ['name' => 'Bekreftet', 'color' => 'green', 'is_active' => true, 'sort_order' => 2]
    );
    OrderStatus::firstOrCreate(
        ['code' => 'completed'],
        ['name' => 'Fullfort', 'color' => 'green', 'is_active' => true, 'sort_order' => 4]
    );

    // Create default VAT rate if it doesn't exist
    VatRate::firstOrCreate(
        ['code' => 'standard'],
        ['rate' => 25, 'name' => 'Standard MVA', 'is_default' => true, 'is_active' => true]
    );
});

test('order has auto-generated number', function () {
    $contact = Contact::factory()->create();
    $order = Order::create([
        'title' => 'Test Order',
        'contact_id' => $contact->id,
        'created_by' => $this->user->id,
    ]);

    expect($order->order_number)->toMatch('/^O-\d{4}-\d{4}$/');
});

test('order can have lines', function () {
    $contact = Contact::factory()->create();
    $vatRate = VatRate::where('code', 'standard')->first();

    $order = Order::create([
        'title' => 'Test Order',
        'contact_id' => $contact->id,
        'created_by' => $this->user->id,
    ]);

    OrderLine::create([
        'order_id' => $order->id,
        'description' => 'Test Product',
        'quantity' => 3,
        'unit' => 'stk',
        'unit_price' => 200,
        'vat_rate_id' => $vatRate->id,
        'vat_percent' => 25,
        'sort_order' => 0,
    ]);

    $order->refresh();

    expect($order->lines)->toHaveCount(1);
    expect((float) $order->subtotal)->toBe(600.0);
    expect((float) $order->vat_total)->toBe(150.0);
    expect((float) $order->total)->toBe(750.0);
});

test('order can be converted to invoice', function () {
    $contact = Contact::factory()->create();
    $completedStatus = OrderStatus::where('code', 'completed')->first();
    $vatRate = VatRate::where('code', 'standard')->first();

    $order = Order::create([
        'title' => 'Test Order',
        'contact_id' => $contact->id,
        'order_status_id' => $completedStatus->id,
        'created_by' => $this->user->id,
        'customer_name' => 'Test Company',
        'customer_address' => 'Test Address',
    ]);

    OrderLine::create([
        'order_id' => $order->id,
        'description' => 'Test Product',
        'quantity' => 2,
        'unit' => 'stk',
        'unit_price' => 150,
        'vat_rate_id' => $vatRate->id,
        'vat_percent' => 25,
        'sort_order' => 0,
    ]);

    $order->refresh();

    expect($order->can_convert)->toBeTrue();

    $invoice = $order->convertToInvoice();

    expect($invoice)->not->toBeNull();
    expect($invoice->order_id)->toBe($order->id);
    expect($invoice->title)->toBe('Test Order');
    expect($invoice->lines)->toHaveCount(1);
    expect((float) $invoice->total)->toBe(375.0);
});

test('draft order cannot be converted', function () {
    $contact = Contact::factory()->create();
    $draftStatus = OrderStatus::where('code', 'draft')->first();

    $order = Order::create([
        'title' => 'Draft Order',
        'contact_id' => $contact->id,
        'order_status_id' => $draftStatus->id,
        'created_by' => $this->user->id,
    ]);

    expect($order->can_convert)->toBeFalse();
});

test('order has delivery address separate from customer address', function () {
    $contact = Contact::factory()->create();

    $order = Order::create([
        'title' => 'Test Order',
        'contact_id' => $contact->id,
        'created_by' => $this->user->id,
        'customer_name' => 'Customer Name',
        'customer_address' => 'Customer Address',
        'customer_postal_code' => '1000',
        'customer_city' => 'Oslo',
        'delivery_address' => 'Delivery Address',
        'delivery_postal_code' => '2000',
        'delivery_city' => 'Bergen',
    ]);

    expect($order->customer_address)->toBe('Customer Address');
    expect($order->delivery_address)->toBe('Delivery Address');
    expect($order->customer_city)->toBe('Oslo');
    expect($order->delivery_city)->toBe('Bergen');
});
