<?php

use App\Models\Company;
use App\Models\Contact;
use App\Models\Tag;
use App\Models\User;
use App\Services\TagService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function setupTagContext(): array
{
    $user = User::factory()->create(['onboarding_completed' => true]);
    $company = Company::factory()->withOwner($user)->create();
    $user->update(['current_company_id' => $company->id]);
    app()->instance('current.company', $company);

    return ['user' => $user->fresh(), 'company' => $company];
}

beforeEach(function () {
    ['user' => $this->user, 'company' => $this->company] = setupTagContext();
    $this->actingAs($this->user);
    $this->service = app(TagService::class);
});

describe('Tag CRUD Operations', function () {
    it('gets all tags for the current company', function () {
        Tag::factory()->count(3)->create(['company_id' => $this->company->id]);
        Tag::factory()->count(2)->create(); // Different company

        $tags = $this->service->all();

        expect($tags)->toHaveCount(3);
    });

    it('gets only active tags', function () {
        Tag::factory()->count(2)->create(['company_id' => $this->company->id, 'is_active' => true]);
        Tag::factory()->create(['company_id' => $this->company->id, 'is_active' => false]);

        $tags = $this->service->active();

        expect($tags)->toHaveCount(2);
    });

    it('creates a new tag', function () {
        $tag = $this->service->create([
            'name' => 'Important',
            'color' => 'red',
            'description' => 'High priority contacts',
        ]);

        expect($tag)->toBeInstanceOf(Tag::class);
        expect($tag->name)->toBe('Important');
        expect($tag->slug)->toBe('important');
        expect($tag->color)->toBe('red');
        expect($tag->company_id)->toBe($this->company->id);
    });

    it('generates unique slug automatically', function () {
        $tag1 = $this->service->create(['name' => 'Test Tag']);
        $tag2 = $this->service->create(['name' => 'Test Tag']);

        expect($tag1->slug)->toBe('test-tag');
        expect($tag2->slug)->toBe('test-tag-1');
    });

    it('updates a tag', function () {
        $tag = Tag::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Old Name',
        ]);

        $updated = $this->service->update($tag, [
            'name' => 'New Name',
            'color' => 'blue',
        ]);

        expect($updated->name)->toBe('New Name');
        expect($updated->slug)->toBe('new-name');
        expect($updated->color)->toBe('blue');
    });

    it('deletes a tag', function () {
        $tag = Tag::factory()->create(['company_id' => $this->company->id]);
        $id = $tag->id;

        $result = $this->service->delete($tag);

        expect($result)->toBeTrue();
        expect(Tag::find($id))->toBeNull();
    });

    it('finds or creates a tag by name', function () {
        $tag1 = $this->service->findOrCreate('VIP', 'gold');

        expect($tag1->name)->toBe('VIP');
        expect($tag1->color)->toBe('gold');

        // Should return the same tag
        $tag2 = $this->service->findOrCreate('VIP');

        expect($tag2->id)->toBe($tag1->id);
    });
});

describe('Tag-Contact Relationships', function () {
    it('attaches tags to a contact', function () {
        $contact = Contact::factory()->create(['created_by' => $this->user->id]);
        $tags = Tag::factory()->count(2)->create(['company_id' => $this->company->id]);

        $this->service->attachToContact($contact, $tags->pluck('id')->toArray());

        expect($contact->tags)->toHaveCount(2);
    });

    it('does not duplicate tags when attaching', function () {
        $contact = Contact::factory()->create(['created_by' => $this->user->id]);
        $tag = Tag::factory()->create(['company_id' => $this->company->id]);

        $this->service->attachToContact($contact, [$tag->id]);
        $this->service->attachToContact($contact, [$tag->id]);

        $contact->load('tags');
        expect($contact->tags)->toHaveCount(1);
    });

    it('detaches tags from a contact', function () {
        $contact = Contact::factory()->create(['created_by' => $this->user->id]);
        $tags = Tag::factory()->count(3)->create(['company_id' => $this->company->id]);
        $contact->attachTags($tags->pluck('id')->toArray());

        $this->service->detachFromContact($contact, [$tags[0]->id, $tags[1]->id]);

        $contact->load('tags');
        expect($contact->tags)->toHaveCount(1);
        expect($contact->tags->first()->id)->toBe($tags[2]->id);
    });

    it('syncs tags for a contact', function () {
        $contact = Contact::factory()->create(['created_by' => $this->user->id]);
        $originalTags = Tag::factory()->count(2)->create(['company_id' => $this->company->id]);
        $newTags = Tag::factory()->count(3)->create(['company_id' => $this->company->id]);

        $contact->attachTags($originalTags->pluck('id')->toArray());
        expect($contact->tags)->toHaveCount(2);

        $this->service->syncContactTags($contact, $newTags->pluck('id')->toArray());

        $contact->load('tags');
        expect($contact->tags)->toHaveCount(3);
        expect($contact->tags->pluck('id')->toArray())
            ->toBe($newTags->pluck('id')->toArray());
    });

    it('gets contacts with a specific tag', function () {
        $tag = Tag::factory()->create(['company_id' => $this->company->id]);
        $contactsWithTag = Contact::factory()->count(2)->create(['created_by' => $this->user->id]);
        $contactWithoutTag = Contact::factory()->create(['created_by' => $this->user->id]);

        foreach ($contactsWithTag as $contact) {
            $contact->attachTags([$tag->id]);
        }

        $contacts = $this->service->getContactsWithTag($tag);

        expect($contacts)->toHaveCount(2);
        expect($contacts->pluck('id')->toArray())
            ->toBe($contactsWithTag->pluck('id')->toArray());
    });

    it('gets tags with contact count', function () {
        $tag1 = Tag::factory()->create(['company_id' => $this->company->id, 'name' => 'Popular']);
        $tag2 = Tag::factory()->create(['company_id' => $this->company->id, 'name' => 'Unused']);

        $contacts = Contact::factory()->count(5)->create(['created_by' => $this->user->id]);
        foreach ($contacts as $contact) {
            $contact->attachTags([$tag1->id]);
        }

        $tagsWithCount = $this->service->getTagsWithContactCount();

        $popularTag = $tagsWithCount->firstWhere('id', $tag1->id);
        $unusedTag = $tagsWithCount->firstWhere('id', $tag2->id);

        expect($popularTag->contacts_count)->toBe(5);
        expect($unusedTag->contacts_count)->toBe(0);
    });
});

describe('Tag Ordering', function () {
    it('returns tags in sort order', function () {
        Tag::factory()->create(['company_id' => $this->company->id, 'name' => 'C', 'sort_order' => 3]);
        Tag::factory()->create(['company_id' => $this->company->id, 'name' => 'A', 'sort_order' => 1]);
        Tag::factory()->create(['company_id' => $this->company->id, 'name' => 'B', 'sort_order' => 2]);

        $tags = $this->service->all();

        expect($tags[0]->name)->toBe('A');
        expect($tags[1]->name)->toBe('B');
        expect($tags[2]->name)->toBe('C');
    });

    it('reorders tags', function () {
        $tagA = Tag::factory()->create(['company_id' => $this->company->id, 'name' => 'A', 'sort_order' => 1]);
        $tagB = Tag::factory()->create(['company_id' => $this->company->id, 'name' => 'B', 'sort_order' => 2]);
        $tagC = Tag::factory()->create(['company_id' => $this->company->id, 'name' => 'C', 'sort_order' => 3]);

        $this->service->reorder([
            $tagC->id => 1,
            $tagA->id => 2,
            $tagB->id => 3,
        ]);

        $tags = $this->service->all();

        expect($tags[0]->name)->toBe('C');
        expect($tags[1]->name)->toBe('A');
        expect($tags[2]->name)->toBe('B');
    });
});

describe('Tag Model', function () {
    it('auto-generates slug from name on create', function () {
        $tag = Tag::create([
            'company_id' => $this->company->id,
            'name' => 'Norwegian Characters: ÆØÅ',
        ]);

        // Str::slug transliterates ÆØÅ to their ASCII equivalents
        expect($tag->slug)->toStartWith('norwegian-characters-');
    });

    it('belongs to a company', function () {
        $tag = Tag::factory()->create(['company_id' => $this->company->id]);

        expect($tag->company)->toBeInstanceOf(Company::class);
        expect($tag->company->id)->toBe($this->company->id);
    });
});

describe('Contact-Tag Integration', function () {
    it('contact can access its tags', function () {
        $contact = Contact::factory()->create(['created_by' => $this->user->id]);
        $tags = Tag::factory()->count(2)->create(['company_id' => $this->company->id]);

        $contact->attachTags($tags->pluck('id')->toArray());

        expect($contact->tags)->toHaveCount(2);
        expect($contact->tags->first())->toBeInstanceOf(Tag::class);
    });

    it('tag can access its contacts', function () {
        $tag = Tag::factory()->create(['company_id' => $this->company->id]);
        $contacts = Contact::factory()->count(3)->create(['created_by' => $this->user->id]);

        $tag->attachContacts($contacts->pluck('id')->toArray());

        expect($tag->contacts)->toHaveCount(3);
        expect($tag->contacts->first())->toBeInstanceOf(Contact::class);
    });

    it('pivot table has timestamps', function () {
        $contact = Contact::factory()->create(['created_by' => $this->user->id]);
        $tag = Tag::factory()->create(['company_id' => $this->company->id]);

        $contact->attachTags([$tag->id]);

        $pivot = $contact->tags()->first()->pivot;

        expect($pivot->created_at)->not->toBeNull();
        expect($pivot->updated_at)->not->toBeNull();
    });
});
