<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'is_pinned',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
    ];

    /**
     * Sanitize HTML content to prevent XSS.
     */
    public function setContentAttribute(?string $value): void
    {
        $this->attributes['content'] = $value ? self::sanitizeHtml($value) : null;
    }

    private static function sanitizeHtml(string $html): string
    {
        $allowedTags = '<p><br><strong><b><em><i><u><s><del><mark><sup><sub>'
            .'<code><pre><h1><h2><h3><h4><h5><h6>'
            .'<blockquote><ul><ol><li><hr><div><span><a><img>';

        $html = strip_tags($html, $allowedTags);

        // Strip event handler attributes (onclick, onerror, etc.)
        $html = preg_replace('/\s+on\w+\s*=\s*"[^"]*"/i', '', $html);
        $html = preg_replace('/\s+on\w+\s*=\s*\'[^\']*\'/i', '', $html);
        $html = preg_replace('/\s+on\w+\s*=\s*[^\s>]*/i', '', $html);

        // Strip javascript: protocol in href and src attributes
        $html = preg_replace('/(<a\b[^>]*)\s+href\s*=\s*["\']?\s*javascript:[^"\'>\s]*/i', '$1', $html);
        $html = preg_replace('/(<img\b[^>]*)\s+src\s*=\s*["\']?\s*javascript:[^"\'>\s]*/i', '$1', $html);

        return $html;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderByDesc('is_pinned')->orderByDesc('updated_at');
    }
}
