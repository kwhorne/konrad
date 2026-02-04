<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollEntry extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'payroll_run_id',
        'user_id',
        'timer_ordinaer',
        'timer_overtid_50',
        'timer_overtid_100',
        'grunnlonn',
        'overtid_belop',
        'bonus',
        'tillegg',
        'bruttolonn',
        'forskuddstrekk',
        'fagforening',
        'andre_trekk',
        'nettolonn',
        'feriepenger_grunnlag',
        'feriepenger_avsetning',
        'arbeidsgiveravgift',
        'otp_belop',
        'skatt_type_brukt',
        'skatteprosent_brukt',
        'a_melding_data',
        'notes',
        'payslip_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'timer_ordinaer' => 'decimal:2',
            'timer_overtid_50' => 'decimal:2',
            'timer_overtid_100' => 'decimal:2',
            'grunnlonn' => 'decimal:2',
            'overtid_belop' => 'decimal:2',
            'bonus' => 'decimal:2',
            'tillegg' => 'decimal:2',
            'bruttolonn' => 'decimal:2',
            'forskuddstrekk' => 'decimal:2',
            'fagforening' => 'decimal:2',
            'andre_trekk' => 'decimal:2',
            'nettolonn' => 'decimal:2',
            'feriepenger_grunnlag' => 'decimal:2',
            'feriepenger_avsetning' => 'decimal:2',
            'arbeidsgiveravgift' => 'decimal:2',
            'otp_belop' => 'decimal:2',
            'skatteprosent_brukt' => 'decimal:2',
            'a_melding_data' => 'array',
            'payslip_sent_at' => 'datetime',
        ];
    }

    /**
     * Get the payroll run this entry belongs to.
     */
    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    /**
     * Get the employee (user) for this entry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all entry lines (detailed pay components).
     */
    public function lines(): HasMany
    {
        return $this->hasMany(PayrollEntryLine::class);
    }

    /**
     * Calculate the net salary.
     */
    public function calculateNettolonn(): float
    {
        $totalTrekk = $this->forskuddstrekk + $this->fagforening + $this->andre_trekk;

        return $this->bruttolonn - $totalTrekk;
    }

    /**
     * Get total hours worked.
     */
    public function getTotalTimerAttribute(): float
    {
        return $this->timer_ordinaer + $this->timer_overtid_50 + $this->timer_overtid_100;
    }

    /**
     * Get total deductions.
     */
    public function getTotalTrekkAttribute(): float
    {
        return $this->forskuddstrekk + $this->fagforening + $this->andre_trekk;
    }

    /**
     * Get total employer cost for this employee.
     */
    public function getTotalEmployerCostAttribute(): float
    {
        return $this->bruttolonn + $this->arbeidsgiveravgift + $this->otp_belop;
    }

    /**
     * Scope to get entries for a specific user.
     */
    public function scopeForUser($query, User|int $user)
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $query->where('user_id', $userId);
    }
}
