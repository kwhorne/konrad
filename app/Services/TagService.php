<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class TagService
{
    /**
     * Get all tags for the current company.
     */
    public function all(): Collection
    {
        return Tag::query()
            ->ordered()
            ->get();
    }

    /**
     * Get only active tags for the current company.
     */
    public function active(): Collection
    {
        return Tag::query()
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * Create a new tag.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Tag
    {
        if (empty($data['slug']) && ! empty($data['name'])) {
            $data['slug'] = $this->generateUniqueSlug($data['name']);
        }

        return Tag::create($data);
    }

    /**
     * Update an existing tag.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Tag $tag, array $data): Tag
    {
        if (isset($data['name']) && $data['name'] !== $tag->name) {
            if (empty($data['slug'])) {
                $data['slug'] = $this->generateUniqueSlug($data['name'], $tag->id);
            }
        }

        $tag->update($data);

        return $tag->fresh();
    }

    /**
     * Delete a tag.
     */
    public function delete(Tag $tag): bool
    {
        return $tag->delete();
    }

    /**
     * Find or create a tag by name.
     */
    public function findOrCreate(string $name, ?string $color = null): Tag
    {
        $slug = Str::slug($name);

        $tag = Tag::where('slug', $slug)->first();

        if ($tag) {
            return $tag;
        }

        return $this->create([
            'name' => $name,
            'slug' => $slug,
            'color' => $color ?? 'zinc',
        ]);
    }

    /**
     * Attach tags to a contact.
     *
     * @param  array<int>  $tagIds
     */
    public function attachToContact(Contact $contact, array $tagIds): void
    {
        $contact->attachTags($tagIds);
    }

    /**
     * Detach tags from a contact.
     *
     * @param  array<int>  $tagIds
     */
    public function detachFromContact(Contact $contact, array $tagIds): void
    {
        $contact->tags()->detach($tagIds);
    }

    /**
     * Sync tags for a contact (replaces all existing).
     *
     * @param  array<int>  $tagIds
     */
    public function syncContactTags(Contact $contact, array $tagIds): void
    {
        $contact->syncTags($tagIds);
    }

    /**
     * Get contacts with a specific tag.
     */
    public function getContactsWithTag(Tag $tag): Collection
    {
        return $tag->contacts()
            ->with(['primaryContact', 'accountManager'])
            ->get();
    }

    /**
     * Get the number of contacts using each tag.
     *
     * @return Collection<int, Tag>
     */
    public function getTagsWithContactCount(): Collection
    {
        return Tag::query()
            ->withCount('contacts')
            ->ordered()
            ->get();
    }

    /**
     * Reorder tags.
     *
     * @param  array<int, int>  $order  Array of tag ID => sort_order
     */
    public function reorder(array $order): void
    {
        foreach ($order as $tagId => $sortOrder) {
            Tag::where('id', $tagId)->update(['sort_order' => $sortOrder]);
        }
    }

    /**
     * Generate a unique slug for a tag.
     */
    protected function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        $query = Tag::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;

            $query = Tag::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }
}
