<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HolidayPayBalance extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'user_id',
        'opptjeningsaar',
        'grunnlag',
        'opptjent',
        'utbetalt',
        'gjenstaaende',
    ];

    protected function casts(): array
    {
        return [
            'opptjeningsaar' => 'integer',
            'grunnlag' => 'decimal:2',
            'opptjent' => 'decimal:2',
            'utbetalt' => 'decimal:2',
            'gjenstaaende' => 'decimal:2',
        ];
    }

    /**
     * Get the employee (user) for this balance.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the payout year (year after earning year).
     */
    public function getUtbetalingsaarAttribute(): int
    {
        return $this->opptjeningsaar + 1;
    }

    /**
     * Record a payout.
     */
    public function recordPayout(float $amount): void
    {
        $this->utbetalt += $amount;
        $this->gjenstaaende = max(0, $this->opptjent - $this->utbetalt);
        $this->save();
    }

    /**
     * Update the accrued amount.
     */
    public function addAccrual(float $grunnlag, float $opptjent): void
    {
        $this->grunnlag += $grunnlag;
        $this->opptjent += $opptjent;
        $this->gjenstaaende = $this->opptjent - $this->utbetalt;
        $this->save();
    }

    /**
     * Check if there is remaining balance to pay out.
     */
    public function getHasRemainingBalanceAttribute(): bool
    {
        return $this->gjenstaaende > 0;
    }

    /**
     * Scope to get balances for a specific user.
     */
    public function scopeForUser($query, User|int $user)
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get balances for a specific earning year.
     */
    public function scopeForYear($query, int $year)
    {
        return $query->where('opptjeningsaar', $year);
    }

    /**
     * Scope to get balances with remaining amount.
     */
    public function scopeWithRemaining($query)
    {
        return $query->where('gjenstaaende', '>', 0);
    }
}
