<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Tag extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'color',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'contact_tag')
            ->withPivot('company_id')
            ->withTimestamps();
    }

    /**
     * Attach contacts to the tag with company_id.
     *
     * @param  array<int>|int  $contactIds
     */
    public function attachContacts(array|int $contactIds): void
    {
        $contactIds = is_array($contactIds) ? $contactIds : [$contactIds];
        $pivotData = array_fill_keys($contactIds, ['company_id' => $this->company_id]);
        $this->contacts()->syncWithoutDetaching($pivotData);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
