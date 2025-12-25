<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountSuggestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'keyword',
        'account_id',
        'usage_count',
    ];

    protected function casts(): array
    {
        return [
            'usage_count' => 'integer',
        ];
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function scopeForContact($query, Contact $contact)
    {
        return $query->where('contact_id', $contact->id);
    }

    public function scopeWithKeyword($query, string $keyword)
    {
        return $query->where('keyword', $keyword);
    }

    public function scopeMostUsed($query)
    {
        return $query->orderByDesc('usage_count');
    }
}
