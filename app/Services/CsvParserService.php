<?php

namespace App\Services;

use App\Models\CsvFormatMapping;
use Carbon\Carbon;
use Exception;

class CsvParserService
{
    /**
     * Parse CSV content using the specified format.
     *
     * @return array<int, array<string, mixed>>
     */
    public function parse(string $content, CsvFormatMapping $format, bool $alreadyNormalized = false): array
    {
        if (! $alreadyNormalized) {
            $content = $this->convertEncoding($content, $format->encoding);
        }
        $lines = $this->splitLines($content);

        if (empty($lines)) {
            return [];
        }

        $delimiter = $format->delimiter;
        $hasHeader = $format->has_header;
        $mapping = $format->column_mapping;

        $results = [];
        $startRow = $hasHeader ? 1 : 0;
        $sortOrder = 1;

        for ($i = $startRow; $i < count($lines); $i++) {
            $line = $lines[$i];
            if (empty(trim($line))) {
                continue;
            }

            $columns = str_getcsv($line, $delimiter);
            $parsed = $this->parseRow($columns, $mapping, $format->date_format);

            if ($parsed) {
                $parsed['sort_order'] = $sortOrder++;
                $parsed['raw_data'] = $columns;
                $results[] = $parsed;
            }
        }

        return $results;
    }

    /**
     * Parse CSV content and auto-detect the format.
     *
     * @return array{format: string|null, data: array<int, array<string, mixed>>}
     */
    public function parseWithAutoDetect(string $content): array
    {
        $content = $this->normalizeEncoding($content);
        $lines = $this->splitLines($content);

        if (empty($lines)) {
            return ['format' => null, 'data' => []];
        }

        $delimiter = $this->detectDelimiter($lines[0]);
        $headers = str_getcsv($lines[0], $delimiter);

        $formatKey = CsvFormatMapping::detectFormat($headers);
        $formats = CsvFormatMapping::getSystemFormats();

        if ($formatKey && isset($formats[$formatKey])) {
            $format = new CsvFormatMapping($formats[$formatKey]);
            $format->is_system = true;

            return [
                'format' => $formatKey,
                'data' => $this->parse($content, $format, alreadyNormalized: true),
            ];
        }

        return ['format' => null, 'data' => []];
    }

    /**
     * Get headers from CSV content.
     *
     * @return array<string>
     */
    public function getHeaders(string $content, string $delimiter = ';'): array
    {
        $content = $this->normalizeEncoding($content);
        $lines = $this->splitLines($content);

        if (empty($lines)) {
            return [];
        }

        return str_getcsv($lines[0], $delimiter);
    }

    /**
     * Parse a single row using the column mapping.
     *
     * @param  array<string>  $columns
     * @param  array<string, int>  $mapping
     * @return array<string, mixed>|null
     */
    protected function parseRow(array $columns, array $mapping, string $dateFormat): ?array
    {
        $dateCol = $mapping['date'] ?? null;
        $descCol = $mapping['description'] ?? null;
        $inCol = $mapping['in'] ?? null;
        $outCol = $mapping['out'] ?? null;
        $refCol = $mapping['reference'] ?? null;
        $balanceCol = $mapping['balance'] ?? null;

        if ($dateCol === null || ! isset($columns[$dateCol])) {
            return null;
        }

        $dateStr = trim($columns[$dateCol] ?? '');
        if (empty($dateStr)) {
            return null;
        }

        try {
            $date = Carbon::createFromFormat($dateFormat, $dateStr);
        } catch (Exception) {
            try {
                $date = Carbon::parse($dateStr);
            } catch (Exception) {
                return null;
            }
        }

        $description = $descCol !== null ? trim($columns[$descCol] ?? '') : '';
        $reference = $refCol !== null ? trim($columns[$refCol] ?? '') : null;

        $inAmount = $inCol !== null ? $this->parseAmount($columns[$inCol] ?? '') : 0;
        $outAmount = $outCol !== null ? $this->parseAmount($columns[$outCol] ?? '') : 0;

        $amount = 0;
        $transactionType = 'credit';

        if ($inAmount > 0) {
            $amount = $inAmount;
            $transactionType = 'credit';
        } elseif ($outAmount > 0) {
            $amount = -$outAmount;
            $transactionType = 'debit';
        } elseif ($inAmount < 0) {
            $amount = $inAmount;
            $transactionType = 'debit';
        } elseif ($outAmount < 0) {
            $amount = -$outAmount;
            $transactionType = 'credit';
        }

        if (abs($amount) < 0.01) {
            return null;
        }

        $balance = $balanceCol !== null ? $this->parseAmount($columns[$balanceCol] ?? '') : null;

        return [
            'transaction_date' => $date->toDateString(),
            'description' => $description,
            'reference' => $reference ?: null,
            'amount' => $amount,
            'transaction_type' => $transactionType,
            'running_balance' => $balance,
        ];
    }

    /**
     * Parse a Norwegian-formatted amount string.
     */
    protected function parseAmount(string $value): float
    {
        $value = trim($value);

        if (empty($value)) {
            return 0.0;
        }

        $value = str_replace([' ', "\u{00A0}"], '', $value);

        if (str_contains($value, ',') && str_contains($value, '.')) {
            if (strrpos($value, ',') > strrpos($value, '.')) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        } elseif (str_contains($value, ',')) {
            $value = str_replace(',', '.', $value);
        }

        return (float) $value;
    }

    /**
     * Convert content from the specified encoding to UTF-8.
     */
    protected function convertEncoding(string $content, string $encoding): string
    {
        // Remove BOM if present
        $content = $this->removeBom($content);

        if (strtoupper($encoding) === 'UTF-8') {
            // Verify it's actually valid UTF-8
            if ($this->isValidUtf8($content)) {
                return $content;
            }
            // If marked as UTF-8 but invalid, try ISO-8859-1
            $encoding = 'ISO-8859-1';
        }

        $converted = @mb_convert_encoding($content, 'UTF-8', $encoding);

        return $converted !== false ? $converted : $content;
    }

    /**
     * Try to normalize encoding to UTF-8.
     * Handles Norwegian characters (æ, ø, å) properly.
     */
    protected function normalizeEncoding(string $content): string
    {
        // Remove BOM if present
        $content = $this->removeBom($content);

        // If it's already valid UTF-8, return as-is
        if ($this->isValidUtf8($content)) {
            return $content;
        }

        // Check for Norwegian characters in ISO-8859-1/Windows-1252
        // æ=0xE6, ø=0xF8, å=0xE5, Æ=0xC6, Ø=0xD8, Å=0xC5
        $hasLatin1Chars = preg_match('/[\xC5\xC6\xD8\xE5\xE6\xF8]/', $content);

        if ($hasLatin1Chars) {
            // Try Windows-1252 first (superset of ISO-8859-1)
            $converted = @mb_convert_encoding($content, 'UTF-8', 'Windows-1252');
            if ($converted !== false && $this->isValidUtf8($converted)) {
                return $converted;
            }

            // Fall back to ISO-8859-1
            $converted = @mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1');
            if ($converted !== false) {
                return $converted;
            }
        }

        // Last resort: use mb_detect_encoding
        $encoding = mb_detect_encoding($content, ['UTF-8', 'Windows-1252', 'ISO-8859-1'], true);

        if ($encoding && $encoding !== 'UTF-8') {
            $converted = @mb_convert_encoding($content, 'UTF-8', $encoding);
            if ($converted !== false) {
                return $converted;
            }
        }

        return $content;
    }

    /**
     * Check if a string is valid UTF-8.
     */
    protected function isValidUtf8(string $content): bool
    {
        return mb_check_encoding($content, 'UTF-8');
    }

    /**
     * Remove BOM (Byte Order Mark) from content.
     */
    protected function removeBom(string $content): string
    {
        // UTF-8 BOM
        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            return substr($content, 3);
        }

        // UTF-16 BE BOM
        if (str_starts_with($content, "\xFE\xFF")) {
            return substr($content, 2);
        }

        // UTF-16 LE BOM
        if (str_starts_with($content, "\xFF\xFE")) {
            return substr($content, 2);
        }

        return $content;
    }

    /**
     * Split content into lines.
     *
     * @return array<string>
     */
    protected function splitLines(string $content): array
    {
        $content = str_replace("\r\n", "\n", $content);
        $content = str_replace("\r", "\n", $content);

        return explode("\n", $content);
    }

    /**
     * Detect the delimiter used in a CSV line.
     */
    protected function detectDelimiter(string $line): string
    {
        $delimiters = [';', ',', "\t", '|'];
        $counts = [];

        foreach ($delimiters as $delimiter) {
            $counts[$delimiter] = substr_count($line, $delimiter);
        }

        arsort($counts);

        return array_key_first($counts) ?: ';';
    }

    /**
     * Extract account number from filename or content.
     */
    public function extractAccountNumber(string $filename, string $content = ''): ?string
    {
        // Pattern to match Norwegian bank account numbers: XXXX.XX.XXXXX or XXXX XX XXXXX
        $pattern = '/(\d{4})[\s\.](\d{2})[\s\.](\d{5})/';

        if (preg_match($pattern, $filename, $matches)) {
            return $matches[1].$matches[2].$matches[3];
        }

        $lines = $this->splitLines($content);
        foreach (array_slice($lines, 0, 5) as $line) {
            if (preg_match($pattern, $line, $matches)) {
                return $matches[1].$matches[2].$matches[3];
            }
        }

        return null;
    }

    /**
     * Extract date range from parsed transactions.
     *
     * @param  array<int, array<string, mixed>>  $transactions
     * @return array{from: string|null, to: string|null}
     */
    public function extractDateRange(array $transactions): array
    {
        if (empty($transactions)) {
            return ['from' => null, 'to' => null];
        }

        $dates = array_column($transactions, 'transaction_date');
        sort($dates);

        return [
            'from' => $dates[0] ?? null,
            'to' => $dates[count($dates) - 1] ?? null,
        ];
    }
}
