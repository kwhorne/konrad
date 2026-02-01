<?php

use App\Models\Company;
use App\Models\Contact;
use App\Models\ContactPerson;
use App\Models\User;
use App\Services\ContactService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

// Helper to set up a user with a company and context
function setupCompanyContext(): array
{
    $user = User::factory()->create(['onboarding_completed' => true]);
    $company = Company::factory()->withOwner($user)->create();
    $user->update(['current_company_id' => $company->id]);
    app()->instance('current.company', $company);

    return ['user' => $user->fresh(), 'company' => $company];
}

beforeEach(function () {
    ['user' => $this->user, 'company' => $this->company] = setupCompanyContext();
    $this->actingAs($this->user);
    $this->service = app(ContactService::class);
});

// Search Tests
describe('ContactService Search', function () {
    test('returns paginated contacts', function () {
        Contact::factory()->count(20)->create(['company_id' => $this->company->id]);

        $result = $this->service->search([], 15);

        expect($result)->toBeInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class)
            ->and($result->perPage())->toBe(15)
            ->and($result->total())->toBe(20);
    });

    test('filters by search term on company name', function () {
        Contact::factory()->create(['company_id' => $this->company->id, 'company_name' => 'Acme Corporation']);
        Contact::factory()->create(['company_id' => $this->company->id, 'company_name' => 'Beta Industries']);
        Contact::factory()->create(['company_id' => $this->company->id, 'company_name' => 'Acme Solutions']);

        $result = $this->service->search(['search' => 'Acme']);

        expect($result->total())->toBe(2);
    });

    test('filters by search term on email', function () {
        Contact::factory()->create(['company_id' => $this->company->id, 'email' => 'contact@acme.com']);
        Contact::factory()->create(['company_id' => $this->company->id, 'email' => 'info@beta.com']);

        $result = $this->service->search(['search' => 'acme']);

        expect($result->total())->toBe(1);
    });

    test('filters by search term on organization number', function () {
        Contact::factory()->create(['company_id' => $this->company->id, 'organization_number' => '123456789']);
        Contact::factory()->create(['company_id' => $this->company->id, 'organization_number' => '987654321']);

        $result = $this->service->search(['search' => '123456']);

        expect($result->total())->toBe(1);
    });

    test('filters by contact type', function () {
        Contact::factory()->customer()->count(3)->create(['company_id' => $this->company->id]);
        Contact::factory()->supplier()->count(2)->create(['company_id' => $this->company->id]);

        $result = $this->service->search(['type' => 'customer']);

        expect($result->total())->toBe(3);
    });

    test('filters by status', function () {
        Contact::factory()->count(3)->create(['company_id' => $this->company->id, 'status' => 'active']);
        Contact::factory()->count(2)->create(['company_id' => $this->company->id, 'status' => 'inactive']);

        $result = $this->service->search(['status' => 'inactive']);

        expect($result->total())->toBe(2);
    });

    test('filters by is_active boolean', function () {
        Contact::factory()->count(4)->create(['company_id' => $this->company->id, 'is_active' => true]);
        Contact::factory()->inactive()->count(2)->create(['company_id' => $this->company->id]);

        $result = $this->service->search(['is_active' => 'false']);

        expect($result->total())->toBe(2);
    });

    test('combines multiple filters', function () {
        Contact::factory()->customer()->create(['company_id' => $this->company->id, 'status' => 'active']);
        Contact::factory()->customer()->create(['company_id' => $this->company->id, 'status' => 'inactive']);
        Contact::factory()->supplier()->create(['company_id' => $this->company->id, 'status' => 'active']);

        $result = $this->service->search([
            'type' => 'customer',
            'status' => 'active',
        ]);

        expect($result->total())->toBe(1);
    });

    test('eager loads relationships', function () {
        $contact = Contact::factory()->create(['company_id' => $this->company->id]);
        ContactPerson::factory()->primary()->create(['contact_id' => $contact->id]);

        $result = $this->service->search();
        $loadedContact = $result->first();

        // Check that relationships are loaded (not lazy-loaded)
        expect($loadedContact->relationLoaded('primaryContact'))->toBeTrue()
            ->and($loadedContact->relationLoaded('accountManager'))->toBeTrue();
    });

    test('orders by latest', function () {
        $older = Contact::factory()->create([
            'company_id' => $this->company->id,
            'created_at' => now()->subDays(2),
        ]);
        $newer = Contact::factory()->create([
            'company_id' => $this->company->id,
            'created_at' => now(),
        ]);

        $result = $this->service->search();

        expect($result->first()->id)->toBe($newer->id);
    });
});

// Stats Tests
describe('ContactService Stats', function () {
    test('returns correct statistics', function () {
        Contact::factory()->customer()->count(5)->create(['company_id' => $this->company->id]);
        Contact::factory()->supplier()->count(3)->create(['company_id' => $this->company->id]);
        Contact::factory()->create(['company_id' => $this->company->id, 'type' => 'partner']);

        $stats = $this->service->getStats();

        expect($stats)->toBeArray()
            ->and($stats['total'])->toBe(9)
            ->and($stats['customers'])->toBe(5)
            ->and($stats['suppliers'])->toBe(3)
            ->and($stats['partners'])->toBe(1);
    });

    test('returns zero for empty database', function () {
        $stats = $this->service->getStats();

        expect($stats['total'])->toBe(0)
            ->and($stats['customers'])->toBe(0)
            ->and($stats['suppliers'])->toBe(0)
            ->and($stats['partners'])->toBe(0);
    });
});

// Create Tests
describe('ContactService Create', function () {
    test('creates contact with basic data', function () {
        $data = [
            'company_name' => 'Test Company AS',
            'type' => 'customer',
            'email' => 'test@example.com',
            'status' => 'active',
            'is_active' => true,
            'created_by' => $this->user->id,
        ];

        $contact = $this->service->create($data);

        expect($contact)->toBeInstanceOf(Contact::class)
            ->and($contact->company_name)->toBe('Test Company AS')
            ->and($contact->type)->toBe('customer')
            ->and($contact->email)->toBe('test@example.com')
            ->and($contact->company_id)->toBe($this->company->id);
    });

    test('creates contact with contact persons', function () {
        $data = [
            'company_name' => 'Test Company AS',
            'type' => 'customer',
            'status' => 'active',
            'is_active' => true,
            'created_by' => $this->user->id,
        ];

        $contactPersons = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '12345678',
                'is_primary' => true,
            ],
            [
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'phone' => '87654321',
                'is_primary' => false,
            ],
        ];

        $contact = $this->service->create($data, $contactPersons);

        expect($contact->contactPersons)->toHaveCount(2)
            ->and($contact->primaryContact)->not->toBeNull()
            ->and($contact->primaryContact->name)->toBe('John Doe');
    });

    test('creates contact with attachments', function () {
        Storage::fake('local');
        Storage::fake('public');

        // Create a fake temp file
        $tempPath = 'livewire-tmp/test-file.pdf';
        Storage::disk('local')->put($tempPath, 'test content');

        $data = [
            'company_name' => 'Test Company AS',
            'type' => 'customer',
            'status' => 'active',
            'is_active' => true,
            'created_by' => $this->user->id,
        ];

        $contact = $this->service->create($data, [], [$tempPath]);

        expect($contact->attachments)->toBeArray()
            ->and($contact->attachments)->toHaveCount(1)
            ->and($contact->attachments[0]['name'])->toBe('test-file.pdf');

        // Verify file was moved to public storage
        Storage::disk('public')->assertExists($contact->attachments[0]['path']);

        // Verify temp file was deleted
        Storage::disk('local')->assertMissing($tempPath);
    });

    test('handles empty birthday string in contact person', function () {
        $data = [
            'company_name' => 'Test Company AS',
            'type' => 'customer',
            'status' => 'active',
            'is_active' => true,
            'created_by' => $this->user->id,
        ];

        $contactPersons = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'birthday' => '', // Empty string should become null
            ],
        ];

        $contact = $this->service->create($data, $contactPersons);

        expect($contact->contactPersons->first()->birthday)->toBeNull();
    });
});

// Update Tests
describe('ContactService Update', function () {
    test('updates contact fields', function () {
        $contact = Contact::factory()->create([
            'company_id' => $this->company->id,
            'company_name' => 'Old Name',
        ]);

        $updatedContact = $this->service->update($contact, [
            'company_name' => 'New Name',
            'email' => 'new@example.com',
        ]);

        expect($updatedContact->company_name)->toBe('New Name')
            ->and($updatedContact->email)->toBe('new@example.com');
    });

    test('syncs contact persons on update', function () {
        $contact = Contact::factory()->create(['company_id' => $this->company->id]);
        ContactPerson::factory()->count(2)->create(['contact_id' => $contact->id]);

        $newContactPersons = [
            ['name' => 'New Person 1', 'email' => 'new1@example.com', 'is_primary' => true],
            ['name' => 'New Person 2', 'email' => 'new2@example.com', 'is_primary' => false],
            ['name' => 'New Person 3', 'email' => 'new3@example.com', 'is_primary' => false],
        ];

        $this->service->update($contact, [], $newContactPersons);

        expect($contact->fresh()->contactPersons)->toHaveCount(3)
            ->and($contact->contactPersons->pluck('name')->toArray())
            ->toContain('New Person 1', 'New Person 2', 'New Person 3');
    });

    test('appends new attachments to existing ones', function () {
        Storage::fake('local');
        Storage::fake('public');

        $existingAttachment = [
            'name' => 'existing.pdf',
            'path' => 'contacts/existing.pdf',
            'size' => 1000,
            'mime_type' => 'application/pdf',
        ];

        $contact = Contact::factory()->create([
            'company_id' => $this->company->id,
            'attachments' => [$existingAttachment],
        ]);

        // Create a new temp file
        $tempPath = 'livewire-tmp/new-file.pdf';
        Storage::disk('local')->put($tempPath, 'new content');

        $this->service->update($contact, [], [], [$tempPath]);

        $updatedContact = $contact->fresh();

        expect($updatedContact->attachments)->toHaveCount(2)
            ->and($updatedContact->attachments[0]['name'])->toBe('existing.pdf')
            ->and($updatedContact->attachments[1]['name'])->toBe('new-file.pdf');
    });

    test('returns fresh model after update', function () {
        $contact = Contact::factory()->create(['company_id' => $this->company->id]);

        $updatedContact = $this->service->update($contact, ['company_name' => 'Updated']);

        expect($updatedContact)->not->toBe($contact)
            ->and($updatedContact->company_name)->toBe('Updated');
    });
});

// Delete Tests
describe('ContactService Delete', function () {
    test('deletes contact', function () {
        $contact = Contact::factory()->create(['company_id' => $this->company->id]);

        $result = $this->service->delete($contact);

        expect($result)->toBeTrue()
            ->and(Contact::find($contact->id))->toBeNull();
    });

    test('deletes attachments from storage', function () {
        Storage::fake('public');

        $attachmentPath = 'contacts/test-file.pdf';
        Storage::disk('public')->put($attachmentPath, 'content');

        $contact = Contact::factory()->create([
            'company_id' => $this->company->id,
            'attachments' => [
                ['name' => 'test-file.pdf', 'path' => $attachmentPath],
            ],
        ]);

        $this->service->delete($contact);

        Storage::disk('public')->assertMissing($attachmentPath);
    });

    test('handles contact without attachments', function () {
        $contact = Contact::factory()->create([
            'company_id' => $this->company->id,
            'attachments' => null,
        ]);

        $result = $this->service->delete($contact);

        expect($result)->toBeTrue();
    });
});

// Delete Attachment Tests
describe('ContactService Delete Attachment', function () {
    test('deletes specific attachment', function () {
        Storage::fake('public');

        $attachmentPath = 'contacts/to-delete.pdf';
        Storage::disk('public')->put($attachmentPath, 'content');

        $contact = Contact::factory()->create([
            'company_id' => $this->company->id,
            'attachments' => [
                ['name' => 'to-delete.pdf', 'path' => $attachmentPath],
                ['name' => 'keep.pdf', 'path' => 'contacts/keep.pdf'],
            ],
        ]);

        $result = $this->service->deleteAttachment($contact, $attachmentPath);

        expect($result)->toBeTrue()
            ->and($contact->fresh()->attachments)->toHaveCount(1)
            ->and($contact->fresh()->attachments[0]['name'])->toBe('keep.pdf');

        Storage::disk('public')->assertMissing($attachmentPath);
    });

    test('returns false for non-existent attachment', function () {
        $contact = Contact::factory()->create([
            'company_id' => $this->company->id,
            'attachments' => [
                ['name' => 'file.pdf', 'path' => 'contacts/file.pdf'],
            ],
        ]);

        $result = $this->service->deleteAttachment($contact, 'contacts/non-existent.pdf');

        expect($result)->toBeFalse()
            ->and($contact->fresh()->attachments)->toHaveCount(1);
    });

    test('handles contact without attachments', function () {
        $contact = Contact::factory()->create([
            'company_id' => $this->company->id,
            'attachments' => null,
        ]);

        $result = $this->service->deleteAttachment($contact, 'contacts/any.pdf');

        expect($result)->toBeFalse();
    });
});
