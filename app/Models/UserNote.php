<?php

namespace App\Models;

use App\Models\Traits\SanitizesHtml;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNote extends Model
{
    use HasFactory, SanitizesHtml;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'is_pinned',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
    ];

    public function setContentAttribute(?string $value): void
    {
        $this->attributes['content'] = $value ? static::sanitizeHtml($value) : null;
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
