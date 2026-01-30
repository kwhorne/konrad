<?php

use App\Models\Contact;
use App\Models\ContactPerson;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

// Contact Number Generation
test('contact has auto-generated number', function () {
    $contact = Contact::factory()->create();

    $year = date('Y');
    expect($contact->contact_number)->toMatch("/^CON{$year}\d{4}$/");
});

test('contact numbers increment correctly', function () {
    $contact1 = Contact::factory()->create();
    $contact2 = Contact::factory()->create();

    $number1 = (int) substr($contact1->contact_number, -4);
    $number2 = (int) substr($contact2->contact_number, -4);

    expect($number2)->toBe($number1 + 1);
});

test('contact number is not overwritten if provided', function () {
    $contact = Contact::factory()->create(['contact_number' => 'CUSTOM001']);

    expect($contact->contact_number)->toBe('CUSTOM001');
});

// Type Labels and Badge Colors
test('contact type label returns correct norwegian translation', function (string $type, string $expectedLabel) {
    $contact = Contact::factory()->create(['type' => $type]);

    expect($contact->getTypeLabel())->toBe($expectedLabel);
})->with([
    ['customer', 'Kunde'],
    ['supplier', 'Leverandør'],
    ['partner', 'Partner'],
    ['prospect', 'Prospekt'],
    ['competitor', 'Konkurrent'],
    ['other', 'Annet'],
]);

test('contact type badge color returns correct color', function (string $type, string $expectedColor) {
    $contact = Contact::factory()->create(['type' => $type]);

    expect($contact->getTypeBadgeColor())->toBe($expectedColor);
})->with([
    ['customer', 'success'],
    ['supplier', 'info'],
    ['partner', 'primary'],
    ['prospect', 'warning'],
    ['competitor', 'danger'],
    ['other', 'outline'],
]);

// Status Labels and Badge Colors
test('contact status label returns correct norwegian translation', function (string $status, string $expectedLabel) {
    $contact = Contact::factory()->create(['status' => $status]);

    expect($contact->getStatusLabel())->toBe($expectedLabel);
})->with([
    ['active', 'Aktiv'],
    ['inactive', 'Inaktiv'],
    ['prospect', 'Prospekt'],
    ['archived', 'Arkivert'],
]);

test('contact status badge color returns correct color', function (string $status, string $expectedColor) {
    $contact = Contact::factory()->create(['status' => $status]);

    expect($contact->getStatusBadgeColor())->toBe($expectedColor);
})->with([
    ['active', 'success'],
    ['inactive', 'outline'],
    ['prospect', 'warning'],
    ['archived', 'danger'],
]);

// Customer Category
test('contact category label returns correct label', function (?string $category, ?string $expectedLabel) {
    $contact = Contact::factory()->create(['customer_category' => $category]);

    expect($contact->getCategoryLabel())->toBe($expectedLabel);
})->with([
    ['a', 'A-kunde'],
    ['b', 'B-kunde'],
    ['c', 'C-kunde'],
    [null, null],
]);

// Address Formatting
test('contact full address formats correctly', function () {
    $contact = Contact::factory()->create([
        'address' => 'Storgata 1',
        'postal_code' => '0001',
        'city' => 'Oslo',
        'country' => 'Norge',
    ]);

    expect($contact->getFullAddress())->toBe('Storgata 1, 0001 Oslo, Norge');
});

test('contact full address handles missing parts', function () {
    $contact = Contact::factory()->create([
        'address' => null,
        'postal_code' => null,
        'city' => 'Oslo',
        'country' => '',
    ]);

    expect($contact->getFullAddress())->toBe('Oslo');
});

test('contact full address returns dash when empty', function () {
    $contact = Contact::factory()->create([
        'address' => null,
        'postal_code' => null,
        'city' => null,
        'country' => '',
    ]);

    expect($contact->getFullAddress())->toBe('-');
});

test('contact billing address formats correctly', function () {
    $contact = Contact::factory()->create([
        'billing_address' => 'Fakturagate 5',
        'billing_postal_code' => '0002',
        'billing_city' => 'Bergen',
        'billing_country' => 'Norge',
    ]);

    expect($contact->getFullBillingAddress())->toBe('Fakturagate 5, 0002 Bergen, Norge');
});

test('contact billing address returns null when not set', function () {
    $contact = Contact::factory()->create([
        'billing_address' => null,
    ]);

    expect($contact->getFullBillingAddress())->toBeNull();
});

// Credit Limit Formatting
test('contact credit limit formats correctly', function () {
    $contact = Contact::factory()->create(['credit_limit' => 50000]);

    expect($contact->getFormattedCreditLimit())->toBe('50 000,00 NOK');
});

test('contact credit limit returns dash when not set', function () {
    $contact = Contact::factory()->create(['credit_limit' => null]);

    expect($contact->getFormattedCreditLimit())->toBe('-');
});

// Relationships
test('contact can have contact persons', function () {
    $contact = Contact::factory()->create();
    ContactPerson::factory()->count(3)->create(['contact_id' => $contact->id]);

    expect($contact->contactPersons)->toHaveCount(3);
});

test('contact can have primary contact', function () {
    $contact = Contact::factory()->create();
    $primaryPerson = ContactPerson::factory()->primary()->create(['contact_id' => $contact->id]);
    ContactPerson::factory()->create(['contact_id' => $contact->id]);

    expect($contact->primaryContact)->not->toBeNull();
    expect($contact->primaryContact->id)->toBe($primaryPerson->id);
});

test('contact belongs to creator', function () {
    $creator = User::factory()->create();
    $contact = Contact::factory()->create(['created_by' => $creator->id]);

    expect($contact->creator->id)->toBe($creator->id);
});

test('contact belongs to account manager', function () {
    $accountManager = User::factory()->create();
    $contact = Contact::factory()->create(['account_manager_id' => $accountManager->id]);

    expect($contact->accountManager->id)->toBe($accountManager->id);
});

test('contact can have quotes', function () {
    $contact = Contact::factory()->create();
    Quote::factory()->count(2)->create(['contact_id' => $contact->id]);

    expect($contact->quotes)->toHaveCount(2);
});

test('contact can have orders', function () {
    $contact = Contact::factory()->create();
    Order::factory()->count(2)->create(['contact_id' => $contact->id]);

    expect($contact->orders)->toHaveCount(2);
});

test('contact can have invoices', function () {
    $contact = Contact::factory()->create();
    Invoice::factory()->count(2)->create(['contact_id' => $contact->id]);

    expect($contact->invoices)->toHaveCount(2);
});

// Scopes
test('active scope filters correctly', function () {
    Contact::factory()->count(3)->create(['is_active' => true]);
    Contact::factory()->count(2)->inactive()->create();

    expect(Contact::active()->count())->toBe(3);
});

test('ordered scope sorts by company name', function () {
    Contact::factory()->create(['company_name' => 'Zebra AS']);
    Contact::factory()->create(['company_name' => 'Alpha AS']);
    Contact::factory()->create(['company_name' => 'Beta AS']);

    $contacts = Contact::ordered()->pluck('company_name')->toArray();

    expect($contacts)->toBe(['Alpha AS', 'Beta AS', 'Zebra AS']);
});

// Soft Deletes
test('contact can be soft deleted', function () {
    $contact = Contact::factory()->create();

    $contact->delete();

    expect($contact->trashed())->toBeTrue();
    expect(Contact::count())->toBe(0);
    expect(Contact::withTrashed()->count())->toBe(1);
});

test('soft deleted contact can be restored', function () {
    $contact = Contact::factory()->create();
    $contact->delete();

    $contact->restore();

    expect($contact->trashed())->toBeFalse();
    expect(Contact::count())->toBe(1);
});

// Factory States
test('contact factory customer state works', function () {
    $contact = Contact::factory()->customer()->create();

    expect($contact->type)->toBe('customer');
});

test('contact factory supplier state works', function () {
    $contact = Contact::factory()->supplier()->create();

    expect($contact->type)->toBe('supplier');
});

test('contact factory inactive state works', function () {
    $contact = Contact::factory()->inactive()->create();

    expect($contact->is_active)->toBeFalse();
    expect($contact->status)->toBe('inactive');
});
