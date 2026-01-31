<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactPerson extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $table = 'contact_persons';

    protected $fillable = [
        'contact_id',
        'name',
        'title',
        'department',
        'email',
        'phone',
        'linkedin',
        'notes',
        'birthday',
        'is_primary',
        'is_active',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
        'birthday' => 'date',
    ];

    // Relationships
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    // Accessors
    public function getInitials(): string
    {
        $words = explode(' ', $this->name);
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1).substr($words[count($words) - 1], 0, 1));
        }

        return strtoupper(substr($this->name, 0, 2));
    }
}
