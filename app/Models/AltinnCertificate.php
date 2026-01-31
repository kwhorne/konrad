<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class AltinnCertificate extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'name',
        'certificate',
        'private_key',
        'passphrase',
        'file_path',
        'serial_number',
        'issuer',
        'subject',
        'valid_from',
        'valid_to',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'valid_from' => 'date',
            'valid_to' => 'date',
            'is_active' => 'boolean',
        ];
    }

    protected $hidden = [
        'certificate',
        'private_key',
        'passphrase',
    ];

    // Encrypted attributes

    protected function certificate(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? Crypt::decryptString($value) : null,
            set: fn (?string $value) => $value ? Crypt::encryptString($value) : null,
        );
    }

    protected function privateKey(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? Crypt::decryptString($value) : null,
            set: fn (?string $value) => $value ? Crypt::encryptString($value) : null,
        );
    }

    protected function passphrase(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? Crypt::decryptString($value) : null,
            set: fn (?string $value) => $value ? Crypt::encryptString($value) : null,
        );
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid($query)
    {
        return $query->where('valid_to', '>=', now()->toDateString());
    }

    // Helpers

    public function isValid(): bool
    {
        if (! $this->valid_from || ! $this->valid_to) {
            return false;
        }

        $now = now();

        return $now->gte($this->valid_from) && $now->lte($this->valid_to);
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        if (! $this->valid_to) {
            return false;
        }

        return $this->valid_to->diffInDays(now()) <= $days;
    }

    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (! $this->valid_to) {
            return null;
        }

        return now()->diffInDays($this->valid_to, false);
    }

    public function activate(): void
    {
        // Deactivate all other certificates first
        static::where('id', '!=', $this->id)->update(['is_active' => false]);

        $this->update(['is_active' => true]);
    }

    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    // Static helpers

    public static function getActiveCertificate(): ?self
    {
        return static::active()->valid()->first();
    }

    public static function hasCertificate(): bool
    {
        return static::active()->valid()->exists();
    }

    /**
     * Parse certificate info from a PEM file or string
     */
    public static function parseCertificateInfo(string $certificateContent): array
    {
        $cert = openssl_x509_parse($certificateContent);

        if (! $cert) {
            return [];
        }

        return [
            'serial_number' => $cert['serialNumberHex'] ?? null,
            'issuer' => $cert['issuer']['CN'] ?? ($cert['issuer']['O'] ?? null),
            'subject' => $cert['subject']['CN'] ?? ($cert['subject']['O'] ?? null),
            'valid_from' => isset($cert['validFrom_time_t'])
                ? \Carbon\Carbon::createFromTimestamp($cert['validFrom_time_t'])
                : null,
            'valid_to' => isset($cert['validTo_time_t'])
                ? \Carbon\Carbon::createFromTimestamp($cert['validTo_time_t'])
                : null,
        ];
    }

    /**
     * Create a certificate from uploaded file
     */
    public static function createFromFile(
        string $name,
        string $certificatePath,
        ?string $privateKeyPath = null,
        ?string $passphrase = null
    ): self {
        $certificateContent = file_get_contents($certificatePath);
        $privateKeyContent = $privateKeyPath ? file_get_contents($privateKeyPath) : null;

        $info = static::parseCertificateInfo($certificateContent);

        return static::create([
            'name' => $name,
            'certificate' => $certificateContent,
            'private_key' => $privateKeyContent,
            'passphrase' => $passphrase,
            'serial_number' => $info['serial_number'],
            'issuer' => $info['issuer'],
            'subject' => $info['subject'],
            'valid_from' => $info['valid_from'],
            'valid_to' => $info['valid_to'],
            'is_active' => false,
        ]);
    }
}
