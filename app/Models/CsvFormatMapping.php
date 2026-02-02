<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class CsvFormatMapping extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'bank_name',
        'delimiter',
        'encoding',
        'date_format',
        'has_header',
        'column_mapping',
        'is_active',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'has_header' => 'boolean',
            'column_mapping' => 'array',
            'is_active' => 'boolean',
            'is_system' => 'boolean',
        ];
    }

    /**
     * Get the column index for a specific field.
     */
    public function getColumnIndex(string $field): ?int
    {
        $mapping = $this->column_mapping ?? [];

        return $mapping[$field] ?? null;
    }

    /**
     * Get all available system formats for Norwegian banks.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getSystemFormats(): array
    {
        return [
            'dnb' => [
                'name' => 'DNB',
                'bank_name' => 'DNB',
                'delimiter' => ';',
                'encoding' => 'ISO-8859-1',
                'date_format' => 'd.m.Y',
                'has_header' => true,
                'column_mapping' => [
                    'date' => 0,
                    'description' => 1,
                    'out' => 2,
                    'in' => 3,
                    'reference' => 4,
                ],
            ],
            'nordea' => [
                'name' => 'Nordea',
                'bank_name' => 'Nordea',
                'delimiter' => ';',
                'encoding' => 'UTF-8',
                'date_format' => 'd.m.Y',
                'has_header' => true,
                'column_mapping' => [
                    'date' => 0,
                    'description' => 1,
                    'interest_date' => 2,
                    'out' => 3,
                    'in' => 4,
                ],
            ],
            'sparebank1' => [
                'name' => 'SpareBank 1',
                'bank_name' => 'SpareBank 1',
                'delimiter' => ';',
                'encoding' => 'UTF-8',
                'date_format' => 'd.m.Y',
                'has_header' => true,
                'column_mapping' => [
                    'date' => 0,
                    'description' => 1,
                    'in' => 2,
                    'out' => 3,
                    'balance' => 4,
                ],
            ],
            'sbanken' => [
                'name' => 'Sbanken',
                'bank_name' => 'Sbanken',
                'delimiter' => ',',
                'encoding' => 'UTF-8',
                'date_format' => 'Y-m-d',
                'has_header' => true,
                'column_mapping' => [
                    'date' => 0,
                    'description' => 1,
                    'out' => 2,
                    'in' => 3,
                    'balance' => 4,
                ],
            ],
        ];
    }

    /**
     * Try to auto-detect format from CSV headers.
     *
     * @param  array<string>  $headers
     */
    public static function detectFormat(array $headers): ?string
    {
        $headerStr = implode('|', array_map('strtolower', $headers));

        // Nordea - has "bokført" column
        if (str_contains($headerStr, 'bokført') || str_contains($headerStr, 'bokfort')) {
            return 'nordea';
        }

        // Sbanken - English headers with comma delimiter
        if (str_contains($headerStr, 'date') && str_contains($headerStr, 'text')) {
            return 'sbanken';
        }

        // SpareBank1 - Norwegian with "beskrivelse" and "saldo"
        if (str_contains($headerStr, 'beskrivelse') && str_contains($headerStr, 'saldo')) {
            return 'sparebank1';
        }

        // DNB - Norwegian "Dato" and "Tekst" columns (fallback for Norwegian format)
        if (str_contains($headerStr, 'dato') && str_contains($headerStr, 'tekst')) {
            return 'dnb';
        }

        return null;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }

    public function scopeAvailable($query)
    {
        return $query->where(function ($q) {
            $q->where('is_system', true)
                ->orWhere(function ($sq) {
                    $sq->whereNotNull('company_id');
                });
        })->where('is_active', true);
    }
}
