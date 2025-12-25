<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'body',
        'featured_image',
        'post_category_id',
        'author_id',
        'is_published',
        'published_at',
        'meta_title',
        'meta_description',
        'views',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'views' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function scopeOrdered($query)
    {
        return $query->orderByDesc('published_at')->orderByDesc('created_at');
    }

    public function isPublished(): bool
    {
        return $this->is_published && ($this->published_at === null || $this->published_at->isPast());
    }

    public function incrementViews(): void
    {
        $this->increment('views');
    }

    public function getReadingTimeAttribute(): int
    {
        $words = str_word_count(strip_tags($this->body ?? ''));

        return max(1, (int) ceil($words / 200));
    }

    public function getExcerptOrTruncatedBodyAttribute(): string
    {
        if ($this->excerpt) {
            return $this->excerpt;
        }

        return Str::limit(strip_tags($this->body ?? ''), 160);
    }
}
