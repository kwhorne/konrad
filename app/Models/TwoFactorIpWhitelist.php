<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TwoFactorIpWhitelist extends Model
{
    protected $table = 'two_factor_ip_whitelist';

    protected $fillable = [
        'ip_address',
        'cidr_range',
        'description',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the user who created this whitelist entry.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get only active entries.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if the given IP matches this whitelist entry.
     */
    public function matchesIp(string $ip): bool
    {
        // Exact match
        if ($this->ip_address === $ip) {
            return true;
        }

        // CIDR range match
        if ($this->cidr_range) {
            return $this->ipInCidr($ip, $this->cidr_range);
        }

        return false;
    }

    /**
     * Check if an IP is within a CIDR range.
     */
    protected function ipInCidr(string $ip, string $cidr): bool
    {
        [$subnet, $bits] = explode('/', $cidr);

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ip = ip2long($ip);
            $subnet = ip2long($subnet);
            $mask = -1 << (32 - (int) $bits);
            $subnet &= $mask;

            return ($ip & $mask) === $subnet;
        }

        return false;
    }

    /**
     * Check if any whitelist entry matches the given IP.
     */
    public static function isWhitelisted(string $ip): bool
    {
        return static::active()->get()->contains(fn ($entry) => $entry->matchesIp($ip));
    }
}
