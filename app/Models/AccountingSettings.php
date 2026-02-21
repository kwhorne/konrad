<?php

namespace App\Models;

use App\Models\Scopes\CompanyScope;
use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountingSettings extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'company_id',
        'departments_enabled',
        'require_department_on_vouchers',
        'default_department_id',
    ];

    protected $casts = [
        'departments_enabled' => 'boolean',
        'require_department_on_vouchers' => 'boolean',
    ];

    public function defaultDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'default_department_id');
    }

    public static function forCompany(int $companyId): ?self
    {
        return static::withoutGlobalScope(CompanyScope::class)->where('company_id', $companyId)->first();
    }

    public static function getOrCreate(int $companyId): self
    {
        return static::withoutGlobalScope(CompanyScope::class)->firstOrCreate(
            ['company_id' => $companyId],
            [
                'departments_enabled' => false,
                'require_department_on_vouchers' => false,
            ]
        );
    }

    public function isDepartmentsEnabled(): bool
    {
        return $this->departments_enabled;
    }
}
