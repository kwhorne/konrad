<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'is_economy',
        'is_active',
        'marketing_emails',
        'invitation_token',
        'invited_at',
        'invitation_accepted_at',
        'last_login_at',
        'seen_version',
        'current_company_id',
        'onboarding_completed',
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
            'is_economy' => 'boolean',
            'is_active' => 'boolean',
            'marketing_emails' => 'boolean',
            'invited_at' => 'datetime',
            'invitation_accepted_at' => 'datetime',
            'last_login_at' => 'datetime',
            'onboarding_completed' => 'boolean',
        ];
    }

    /**
     * Get all companies the user belongs to.
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class)
            ->withPivot(['role', 'is_default', 'joined_at', 'department_id'])
            ->withTimestamps();
    }

    /**
     * Get the user's currently active company.
     */
    public function currentCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'current_company_id');
    }

    /**
     * Get the user's default company.
     */
    public function defaultCompany(): ?Company
    {
        return $this->companies()->wherePivot('is_default', true)->first()
            ?? $this->companies()->first();
    }

    /**
     * Check if user is the owner of a company.
     */
    public function isOwnerOf(Company $company): bool
    {
        return $this->companies()
            ->wherePivot('company_id', $company->id)
            ->wherePivot('role', 'owner')
            ->exists();
    }

    /**
     * Check if user is a manager of a company.
     */
    public function isManagerOf(Company $company): bool
    {
        return $this->companies()
            ->wherePivot('company_id', $company->id)
            ->wherePivot('role', 'manager')
            ->exists();
    }

    /**
     * Check if user can manage a company (owner or manager).
     */
    public function canManage(Company $company): bool
    {
        return $this->companies()
            ->wherePivot('company_id', $company->id)
            ->whereIn('company_user.role', ['owner', 'manager'])
            ->exists();
    }

    /**
     * Check if user belongs to any company.
     */
    public function hasCompany(): bool
    {
        return $this->companies()->exists();
    }

    /**
     * Check if user needs onboarding.
     */
    public function needsOnboarding(): bool
    {
        return ! $this->onboarding_completed || ! $this->hasCompany();
    }

    /**
     * Get the user's role in a specific company.
     */
    public function roleIn(Company $company): ?string
    {
        $pivot = $this->companies()
            ->wherePivot('company_id', $company->id)
            ->first()?->pivot;

        return $pivot?->role;
    }

    /**
     * Get role label in Norwegian.
     */
    public function getRoleLabelIn(Company $company): string
    {
        return match ($this->roleIn($company)) {
            'owner' => 'Eier',
            'manager' => 'Administrator',
            'member' => 'Medlem',
            default => 'Ukjent',
        };
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

    /**
     * Get the user's department in their current company.
     */
    public function departmentInCurrentCompany(): ?Department
    {
        if (! $this->current_company_id) {
            return null;
        }

        $pivot = $this->companies()
            ->wherePivot('company_id', $this->current_company_id)
            ->first()?->pivot;

        return $pivot?->department_id
            ? Department::find($pivot->department_id)
            : null;
    }

    /**
     * Get the user's department_id in their current company.
     */
    public function getCurrentDepartmentIdAttribute(): ?int
    {
        return $this->departmentInCurrentCompany()?->id;
    }
}
