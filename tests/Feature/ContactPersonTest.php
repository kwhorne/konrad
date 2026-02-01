<?php

use App\Models\Company;
use App\Models\Contact;
use App\Models\ContactPerson;
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
test('contact person can be created', function () {
    $contact = Contact::factory()->create();

    $person = ContactPerson::factory()->create([
        'contact_id' => $contact->id,
        'name' => 'Ola Nordmann',
        'email' => 'ola@example.com',
    ]);

    expect($person)->toBeInstanceOf(ContactPerson::class);
    expect($person->name)->toBe('Ola Nordmann');
    expect($person->contact_id)->toBe($contact->id);
});

// Relationship
test('contact person belongs to contact', function () {
    $contact = Contact::factory()->create();
    $person = ContactPerson::factory()->create(['contact_id' => $contact->id]);

    expect($person->contact->id)->toBe($contact->id);
});

// Initials
test('contact person initials returns two letters for single name', function () {
    $person = ContactPerson::factory()->create(['name' => 'Ola']);

    expect($person->getInitials())->toBe('OL');
});

test('contact person initials returns first and last letter for full name', function () {
    $person = ContactPerson::factory()->create(['name' => 'Ola Nordmann']);

    expect($person->getInitials())->toBe('ON');
});

test('contact person initials handles multiple names', function () {
    $person = ContactPerson::factory()->create(['name' => 'Ola Erik Nordmann']);

    expect($person->getInitials())->toBe('ON');
});

test('contact person initials are uppercase', function () {
    $person = ContactPerson::factory()->create(['name' => 'ola nordmann']);

    expect($person->getInitials())->toBe('ON');
});

// Boolean Casts
test('contact person is_primary is boolean', function () {
    $person = ContactPerson::factory()->primary()->create();

    expect($person->is_primary)->toBeTrue();
    expect($person->is_primary)->toBeBool();
});

test('contact person is_active is boolean', function () {
    $person = ContactPerson::factory()->create(['is_active' => true]);

    expect($person->is_active)->toBeTrue();
    expect($person->is_active)->toBeBool();
});

// Soft Deletes
test('contact person can be soft deleted', function () {
    $person = ContactPerson::factory()->create();

    $person->delete();

    expect($person->trashed())->toBeTrue();
    expect(ContactPerson::count())->toBe(0);
    expect(ContactPerson::withTrashed()->count())->toBe(1);
});

test('soft deleted contact person can be restored', function () {
    $person = ContactPerson::factory()->create();
    $person->delete();

    $person->restore();

    expect($person->trashed())->toBeFalse();
    expect(ContactPerson::count())->toBe(1);
});

// Factory States
test('contact person factory primary state works', function () {
    $person = ContactPerson::factory()->primary()->create();

    expect($person->is_primary)->toBeTrue();
});

test('contact person factory inactive state works', function () {
    $person = ContactPerson::factory()->inactive()->create();

    expect($person->is_active)->toBeFalse();
});

// Date Cast
test('contact person birthday is cast to date', function () {
    $person = ContactPerson::factory()->create(['birthday' => '1990-05-15']);

    expect($person->birthday)->toBeInstanceOf(Carbon\Carbon::class);
    expect($person->birthday->format('Y-m-d'))->toBe('1990-05-15');
});
