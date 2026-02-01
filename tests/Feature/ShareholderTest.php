<?php

use App\Models\Company;
use App\Models\Contact;
use App\Models\Shareholder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['onboarding_completed' => true]);
    $this->company = Company::factory()->withOwner($this->user)->create();
    $this->user->update(['current_company_id' => $this->company->id]);
    app()->instance('current.company', $this->company);
    $this->actingAs($this->user);
});

// Basic Creation
test('shareholder can be created', function () {
    $shareholder = Shareholder::factory()->create([
        'name' => 'Ola Nordmann',
        'shareholder_type' => 'person',
    ]);

    expect($shareholder)->toBeInstanceOf(Shareholder::class);
    expect($shareholder->name)->toBe('Ola Nordmann');
    expect($shareholder->shareholder_type)->toBe('person');
});

// Type Labels
test('shareholder type label returns correct norwegian translation', function (string $type, string $expectedLabel) {
    $shareholder = Shareholder::factory()->create(['shareholder_type' => $type]);

    expect($shareholder->getTypeLabel())->toBe($expectedLabel);
})->with([
    ['person', 'Person'],
    ['company', 'Selskap'],
]);

// Type Badge Colors
test('shareholder type badge color returns correct color', function (string $type, string $expectedColor) {
    $shareholder = Shareholder::factory()->create(['shareholder_type' => $type]);

    expect($shareholder->getTypeBadgeColor())->toBe($expectedColor);
})->with([
    ['person', 'info'],
    ['company', 'primary'],
]);

// National ID Encryption
test('shareholder national_id is encrypted', function () {
    $shareholder = Shareholder::factory()->person()->create([
        'national_id' => '12345678901',
    ]);

    // The raw value in the database should be different (encrypted)
    $rawValue = $shareholder->getRawOriginal('national_id');
    expect($rawValue)->not->toBe('12345678901');

    // But when accessed through the model, it should be decrypted
    expect($shareholder->national_id)->toBe('12345678901');
});

// Masked National ID
test('shareholder masked national id shows first 6 digits', function () {
    $shareholder = Shareholder::factory()->person()->create([
        'national_id' => '12345678901',
    ]);

    expect($shareholder->getMaskedNationalId())->toBe('123456*****');
});

test('shareholder masked national id returns null for short values', function () {
    $shareholder = Shareholder::factory()->person()->create([
        'national_id' => '12345',
    ]);

    expect($shareholder->getMaskedNationalId())->toBeNull();
});

test('shareholder masked national id returns null when not set', function () {
    $shareholder = Shareholder::factory()->company()->create([
        'national_id' => null,
    ]);

    expect($shareholder->getMaskedNationalId())->toBeNull();
});

// Full Address
test('shareholder full address formats correctly', function () {
    $shareholder = Shareholder::factory()->create([
        'address' => 'Storgata 1',
        'postal_code' => '0001',
        'city' => 'Oslo',
        'country_code' => 'NO',
    ]);

    expect($shareholder->getFullAddress())->toBe('Storgata 1, 0001 Oslo');
});

test('shareholder full address includes country for foreign shareholders', function () {
    $shareholder = Shareholder::factory()->foreign()->create([
        'address' => 'Kungsgatan 5',
        'postal_code' => '11143',
        'city' => 'Stockholm',
        'country_code' => 'SE',
    ]);

    expect($shareholder->getFullAddress())->toBe('Kungsgatan 5, 11143 Stockholm, SE');
});

test('shareholder full address returns dash when empty', function () {
    $shareholder = Shareholder::factory()->create([
        'address' => null,
        'postal_code' => null,
        'city' => null,
    ]);

    expect($shareholder->getFullAddress())->toBe('-');
});

// Identifier
test('shareholder identifier returns org number for companies', function () {
    $shareholder = Shareholder::factory()->company()->create([
        'organization_number' => '123456789',
    ]);

    expect($shareholder->getIdentifier())->toBe('123456789');
});

test('shareholder identifier returns masked national id for persons', function () {
    $shareholder = Shareholder::factory()->person()->create([
        'national_id' => '12345678901',
    ]);

    expect($shareholder->getIdentifier())->toBe('123456*****');
});

// Is Norwegian
test('shareholder isNorwegian returns true for NO country code', function () {
    $shareholder = Shareholder::factory()->create(['country_code' => 'NO']);

    expect($shareholder->isNorwegian())->toBeTrue();
});

test('shareholder isNorwegian returns false for foreign country code', function () {
    $shareholder = Shareholder::factory()->foreign()->create();

    expect($shareholder->isNorwegian())->toBeFalse();
});

// Relationships
test('shareholder can belong to contact', function () {
    $contact = Contact::factory()->create();
    $shareholder = Shareholder::factory()->create(['contact_id' => $contact->id]);

    expect($shareholder->contact->id)->toBe($contact->id);
});

// Scopes
test('active scope filters correctly', function () {
    Shareholder::factory()->count(3)->create(['is_active' => true]);
    Shareholder::factory()->count(2)->inactive()->create();

    expect(Shareholder::active()->count())->toBe(3);
});

test('persons scope filters correctly', function () {
    Shareholder::factory()->count(2)->person()->create();
    Shareholder::factory()->count(3)->company()->create();

    expect(Shareholder::persons()->count())->toBe(2);
});

test('companies scope filters correctly', function () {
    Shareholder::factory()->count(2)->person()->create();
    Shareholder::factory()->count(3)->company()->create();

    expect(Shareholder::companies()->count())->toBe(3);
});

test('ordered scope sorts by name', function () {
    Shareholder::factory()->create(['name' => 'Zebra AS']);
    Shareholder::factory()->create(['name' => 'Alpha AS']);
    Shareholder::factory()->create(['name' => 'Beta AS']);

    $names = Shareholder::ordered()->pluck('name')->toArray();

    expect($names)->toBe(['Alpha AS', 'Beta AS', 'Zebra AS']);
});

// Soft Deletes
test('shareholder can be soft deleted', function () {
    $shareholder = Shareholder::factory()->create();

    $shareholder->delete();

    expect($shareholder->trashed())->toBeTrue();
    expect(Shareholder::count())->toBe(0);
    expect(Shareholder::withTrashed()->count())->toBe(1);
});

test('soft deleted shareholder can be restored', function () {
    $shareholder = Shareholder::factory()->create();
    $shareholder->delete();

    $shareholder->restore();

    expect($shareholder->trashed())->toBeFalse();
    expect(Shareholder::count())->toBe(1);
});

// Factory States
test('shareholder factory person state works', function () {
    $shareholder = Shareholder::factory()->person()->create();

    expect($shareholder->shareholder_type)->toBe('person');
    expect($shareholder->national_id)->not->toBeNull();
});

test('shareholder factory company state works', function () {
    $shareholder = Shareholder::factory()->company()->create();

    expect($shareholder->shareholder_type)->toBe('company');
    expect($shareholder->organization_number)->not->toBeNull();
});

test('shareholder factory foreign state works', function () {
    $shareholder = Shareholder::factory()->foreign()->create();

    expect($shareholder->country_code)->not->toBe('NO');
});

test('shareholder factory inactive state works', function () {
    $shareholder = Shareholder::factory()->inactive()->create();

    expect($shareholder->is_active)->toBeFalse();
});
