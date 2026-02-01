<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\ContactPerson;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContactService
{
    /**
     * Search contacts with optimized query.
     *
     * Uses FULLTEXT search if available, falls back to LIKE for compatibility.
     *
     * @param  array<string, mixed>  $filters
     */
    public function search(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Contact::with(['primaryContact', 'accountManager']);

        // Apply search filter
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                // Try FULLTEXT search first (if index exists)
                if ($this->hasFullTextIndex()) {
                    $q->whereRaw(
                        'MATCH(company_name, email, organization_number) AGAINST(? IN BOOLEAN MODE)',
                        [$this->prepareFullTextQuery($search)]
                    );
                } else {
                    // Fallback to optimized LIKE search
                    // Only use prefix matching where possible for index usage
                    $q->where('company_name', 'like', "%{$search}%")
                        ->orWhere('contact_number', 'like', "{$search}%") // Prefix match - uses index
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('organization_number', 'like', "{$search}%"); // Prefix match
                }
            });
        }

        // Apply type filter
        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Apply status filter
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Apply is_active filter
        if (isset($filters['is_active'])) {
            $isActive = filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN);
            $query->where('is_active', $isActive);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get contact statistics.
     *
     * @return array<string, int>
     */
    public function getStats(): array
    {
        return [
            'total' => Contact::count(),
            'customers' => Contact::where('type', 'customer')->count(),
            'suppliers' => Contact::where('type', 'supplier')->count(),
            'partners' => Contact::where('type', 'partner')->count(),
        ];
    }

    /**
     * Create a new contact with related data.
     *
     * @param  array<string, mixed>  $data
     * @param  array<array<string, mixed>>  $contactPersons
     * @param  array<string>  $attachmentPaths  Temporary file paths from Livewire uploads
     */
    public function create(array $data, array $contactPersons = [], array $attachmentPaths = []): Contact
    {
        return DB::transaction(function () use ($data, $contactPersons, $attachmentPaths) {
            // Handle attachments
            if (! empty($attachmentPaths)) {
                $data['attachments'] = $this->processAttachments($attachmentPaths);
            }

            // Create the contact
            $contact = Contact::create($data);

            // Create contact persons
            foreach ($contactPersons as $personData) {
                $this->createContactPerson($contact, $personData);
            }

            return $contact;
        });
    }

    /**
     * Update an existing contact with related data.
     *
     * @param  array<string, mixed>  $data
     * @param  array<array<string, mixed>>  $contactPersons
     * @param  array<string>  $newAttachmentPaths
     */
    public function update(Contact $contact, array $data, array $contactPersons = [], array $newAttachmentPaths = []): Contact
    {
        return DB::transaction(function () use ($contact, $data, $contactPersons, $newAttachmentPaths) {
            // Handle new attachments
            if (! empty($newAttachmentPaths)) {
                $existingAttachments = $contact->attachments ?? [];
                $newAttachments = $this->processAttachments($newAttachmentPaths);
                $data['attachments'] = array_merge($existingAttachments, $newAttachments);
            }

            // Update the contact
            $contact->update($data);

            // Sync contact persons
            if (! empty($contactPersons)) {
                $this->syncContactPersons($contact, $contactPersons);
            }

            return $contact->fresh();
        });
    }

    /**
     * Delete a contact and its related data.
     */
    public function delete(Contact $contact): bool
    {
        return DB::transaction(function () use ($contact) {
            // Delete attachments from storage
            if ($contact->attachments) {
                foreach ($contact->attachments as $attachment) {
                    if (isset($attachment['path'])) {
                        Storage::disk('public')->delete($attachment['path']);
                    }
                }
            }

            return $contact->delete();
        });
    }

    /**
     * Process uploaded attachments from temporary storage.
     *
     * @param  array<string>  $tempPaths
     * @return array<array<string, mixed>>
     */
    protected function processAttachments(array $tempPaths): array
    {
        $attachments = [];

        foreach ($tempPaths as $path) {
            if (! Storage::disk('local')->exists($path)) {
                continue;
            }

            $file = Storage::disk('local')->get($path);
            $filename = basename($path);
            $uniqueFilename = Str::uuid().'_'.$filename;
            $storagePath = 'contacts/'.$uniqueFilename;

            Storage::disk('public')->put($storagePath, $file);

            $attachments[] = [
                'name' => $filename,
                'path' => $storagePath,
                'size' => Storage::disk('local')->size($path),
                'mime_type' => Storage::disk('local')->mimeType($path) ?? 'application/octet-stream',
            ];

            // Clean up temporary file
            Storage::disk('local')->delete($path);
        }

        return $attachments;
    }

    /**
     * Create a contact person for a contact.
     *
     * @param  array<string, mixed>  $data
     */
    protected function createContactPerson(Contact $contact, array $data): ContactPerson
    {
        // Convert empty strings to null for date fields
        if (isset($data['birthday']) && $data['birthday'] === '') {
            $data['birthday'] = null;
        }

        return $contact->contactPersons()->create($data);
    }

    /**
     * Sync contact persons - delete existing and recreate.
     *
     * @param  array<array<string, mixed>>  $contactPersons
     */
    protected function syncContactPersons(Contact $contact, array $contactPersons): void
    {
        // Delete existing persons
        $contact->contactPersons()->delete();

        // Recreate with new data
        foreach ($contactPersons as $personData) {
            $this->createContactPerson($contact, $personData);
        }
    }

    /**
     * Delete a specific attachment from a contact.
     */
    public function deleteAttachment(Contact $contact, string $attachmentPath): bool
    {
        $attachments = $contact->attachments ?? [];

        // Find and remove the attachment
        $newAttachments = array_filter($attachments, function ($attachment) use ($attachmentPath) {
            return ($attachment['path'] ?? '') !== $attachmentPath;
        });

        if (count($newAttachments) === count($attachments)) {
            return false; // Attachment not found
        }

        // Delete from storage
        Storage::disk('public')->delete($attachmentPath);

        // Update contact
        $contact->update(['attachments' => array_values($newAttachments)]);

        return true;
    }

    /**
     * Check if FULLTEXT index exists on contacts table.
     */
    protected function hasFullTextIndex(): bool
    {
        static $hasIndex = null;

        if ($hasIndex === null) {
            try {
                $indexes = DB::select("SHOW INDEX FROM contacts WHERE Index_type = 'FULLTEXT'");
                $hasIndex = ! empty($indexes);
            } catch (\Exception $e) {
                $hasIndex = false;
            }
        }

        return $hasIndex;
    }

    /**
     * Prepare search query for FULLTEXT search.
     */
    protected function prepareFullTextQuery(string $search): string
    {
        // Clean and prepare for boolean mode
        $terms = preg_split('/\s+/', trim($search));
        $prepared = array_map(function ($term) {
            // Add wildcard for partial matching
            $term = preg_replace('/[^\p{L}\p{N}]/u', '', $term);

            return $term ? "+{$term}*" : '';
        }, $terms);

        return implode(' ', array_filter($prepared));
    }
}
