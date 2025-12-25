<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'title',
        'password',
        'is_admin',
        'is_active',
        'invitation_token',
        'invited_at',
        'invitation_accepted_at',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'invitation_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
            'invited_at' => 'datetime',
            'invitation_accepted_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Check if user has a pending invitation.
     */
    public function hasPendingInvitation(): bool
    {
        return $this->invitation_token !== null && $this->invitation_accepted_at === null;
    }

    /**
     * Generate an invitation token.
     */
    public function generateInvitationToken(): string
    {
        $this->invitation_token = Str::random(64);
        $this->invited_at = now();
        $this->save();

        return $this->invitation_token;
    }

    /**
     * Accept the invitation.
     */
    public function acceptInvitation(string $password): void
    {
        $this->password = $password;
        $this->invitation_token = null;
        $this->invitation_accepted_at = now();
        $this->save();
    }

    /**
     * Record the last login time.
     */
    public function recordLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * Scope to get only active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only inactive users.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Get the user's initials.
     */
    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);
        $initials = '';

        foreach ($words as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }

        return substr($initials, 0, 2);
    }

    /**
     * Get the user's status label.
     */
    public function getStatusLabelAttribute(): string
    {
        if (! $this->is_active) {
            return 'Inaktiv';
        }

        if ($this->hasPendingInvitation()) {
            return 'Invitert';
        }

        return 'Aktiv';
    }

    /**
     * Get the user's status color.
     */
    public function getStatusColorAttribute(): string
    {
        if (! $this->is_active) {
            return 'red';
        }

        if ($this->hasPendingInvitation()) {
            return 'yellow';
        }

        return 'green';
    }
}
