<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PostCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'sort_order',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(PostCategory::class, 'parent_id')->ordered();
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }
}
