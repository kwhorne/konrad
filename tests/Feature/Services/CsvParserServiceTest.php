<?php

use App\Models\CsvFormatMapping;
use App\Services\CsvParserService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->parser = new CsvParserService;
});

it('parses dnb format csv', function () {
    $csv = "Dato;Tekst;Ut av konto;Inn på konto;Referanse\n";
    $csv .= "01.01.2025;Varekjop Rema 1000;250,00;;\n";
    $csv .= "02.01.2025;Lonnsinnbetaling;;35000,00;12345\n";
    $csv .= "03.01.2025;Husleie betaling;8500,50;;\n";

    $formats = CsvFormatMapping::getSystemFormats();
    $format = new CsvFormatMapping($formats['dnb']);

    $result = $this->parser->parse($csv, $format);

    expect($result)->toHaveCount(3);

    expect($result[0]['transaction_date'])->toBe('2025-01-01');
    expect($result[0]['description'])->toBe('Varekjop Rema 1000');
    expect($result[0]['amount'])->toBe(-250.00);
    expect($result[0]['transaction_type'])->toBe('debit');

    expect($result[1]['transaction_date'])->toBe('2025-01-02');
    expect($result[1]['amount'])->toBe(35000.00);
    expect($result[1]['transaction_type'])->toBe('credit');
    expect($result[1]['reference'])->toBe('12345');

    expect($result[2]['amount'])->toBe(-8500.50);
});

it('parses sbanken format csv', function () {
    $csv = "Date,Text,Out,In,Balance\n";
    $csv .= "2025-01-15,Spotify payment,99.00,,10000.00\n";
    $csv .= "2025-01-16,Salary,,50000.00,60000.00\n";

    $formats = CsvFormatMapping::getSystemFormats();
    $format = new CsvFormatMapping($formats['sbanken']);

    $result = $this->parser->parse($csv, $format);

    expect($result)->toHaveCount(2);

    expect($result[0]['transaction_date'])->toBe('2025-01-15');
    expect($result[0]['description'])->toBe('Spotify payment');
    expect($result[0]['amount'])->toBe(-99.00);
    expect($result[0]['running_balance'])->toBe(10000.00);

    expect($result[1]['amount'])->toBe(50000.00);
    expect($result[1]['running_balance'])->toBe(60000.00);
});

it('parses norwegian number format', function () {
    $csv = "Dato;Tekst;Ut;Inn\n";
    $csv .= "01.01.2025;Test;1 234,56;;\n";
    $csv .= "02.01.2025;Test 2;;9 876,54\n";

    $formats = CsvFormatMapping::getSystemFormats();
    $format = new CsvFormatMapping($formats['dnb']);

    $result = $this->parser->parse($csv, $format);

    expect($result)->toHaveCount(2);
    expect($result[0]['amount'])->toBe(-1234.56);
    expect($result[1]['amount'])->toBe(9876.54);
});

it('auto-detects dnb format', function () {
    $csv = "Dato;Tekst;Ut av konto;Inn på konto\n";
    $csv .= "01.01.2025;Test;;1000,00\n";

    $result = $this->parser->parseWithAutoDetect($csv);

    expect($result['format'])->toBe('dnb');
    expect($result['data'])->toHaveCount(1);
});

it('auto-detects sbanken format', function () {
    $csv = "Date,Text,Out,In,Balance\n";
    $csv .= "2025-01-01,Test,,1000.00,5000.00\n";

    $result = $this->parser->parseWithAutoDetect($csv);

    expect($result['format'])->toBe('sbanken');
    expect($result['data'])->toHaveCount(1);
});

it('extracts headers from csv', function () {
    $csv = "Dato;Beskrivelse;Belop;Saldo\n";
    $csv .= "01.01.2025;Test;1000;5000\n";

    $headers = $this->parser->getHeaders($csv, ';');

    expect($headers)->toBe(['Dato', 'Beskrivelse', 'Belop', 'Saldo']);
});

it('extracts account number from filename', function () {
    $accountNumber = $this->parser->extractAccountNumber('kontoutskrift_1234.56.12345_januar.csv');

    expect($accountNumber)->toBe('12345612345');
});

it('extracts date range from transactions', function () {
    $transactions = [
        ['transaction_date' => '2025-01-15'],
        ['transaction_date' => '2025-01-01'],
        ['transaction_date' => '2025-01-31'],
        ['transaction_date' => '2025-01-10'],
    ];

    $range = $this->parser->extractDateRange($transactions);

    expect($range['from'])->toBe('2025-01-01');
    expect($range['to'])->toBe('2025-01-31');
});

it('returns empty date range for empty transactions', function () {
    $range = $this->parser->extractDateRange([]);

    expect($range['from'])->toBeNull();
    expect($range['to'])->toBeNull();
});

it('skips empty lines', function () {
    $csv = "Dato;Tekst;Ut;Inn\n";
    $csv .= "01.01.2025;Test 1;;1000,00\n";
    $csv .= "\n";
    $csv .= "   \n";
    $csv .= "02.01.2025;Test 2;;2000,00\n";

    $formats = CsvFormatMapping::getSystemFormats();
    $format = new CsvFormatMapping($formats['dnb']);

    $result = $this->parser->parse($csv, $format);

    expect($result)->toHaveCount(2);
});

it('skips rows without valid date', function () {
    $csv = "Dato;Tekst;Ut;Inn\n";
    $csv .= "01.01.2025;Valid;;1000,00\n";
    $csv .= ";Invalid no date;;500,00\n";
    $csv .= "invalid;Invalid date format;;500,00\n";

    $formats = CsvFormatMapping::getSystemFormats();
    $format = new CsvFormatMapping($formats['dnb']);

    $result = $this->parser->parse($csv, $format);

    expect($result)->toHaveCount(1);
    expect($result[0]['description'])->toBe('Valid');
});

it('skips rows with zero amount', function () {
    $csv = "Dato;Tekst;Ut;Inn\n";
    $csv .= "01.01.2025;Has amount;;1000,00\n";
    $csv .= "02.01.2025;No amount;;\n";
    $csv .= "03.01.2025;Zero amount;0,00;0,00\n";

    $formats = CsvFormatMapping::getSystemFormats();
    $format = new CsvFormatMapping($formats['dnb']);

    $result = $this->parser->parse($csv, $format);

    expect($result)->toHaveCount(1);
});

it('handles different line endings', function () {
    $csv = "Dato;Tekst;Ut;Inn\r\n";
    $csv .= "01.01.2025;Test 1;;1000,00\r\n";
    $csv .= "02.01.2025;Test 2;;2000,00\r";
    $csv .= "03.01.2025;Test 3;;3000,00\n";

    $formats = CsvFormatMapping::getSystemFormats();
    $format = new CsvFormatMapping($formats['dnb']);

    $result = $this->parser->parse($csv, $format);

    expect($result)->toHaveCount(3);
});

it('assigns sort order to transactions', function () {
    $csv = "Dato;Tekst;Ut;Inn\n";
    $csv .= "01.01.2025;First;;1000,00\n";
    $csv .= "02.01.2025;Second;;2000,00\n";
    $csv .= "03.01.2025;Third;;3000,00\n";

    $formats = CsvFormatMapping::getSystemFormats();
    $format = new CsvFormatMapping($formats['dnb']);

    $result = $this->parser->parse($csv, $format);

    expect($result[0]['sort_order'])->toBe(1);
    expect($result[1]['sort_order'])->toBe(2);
    expect($result[2]['sort_order'])->toBe(3);
});

it('stores raw data for each transaction', function () {
    $csv = "Dato;Tekst;Ut;Inn;Ref\n";
    $csv .= "01.01.2025;Test transaksjon;;1000,00;ABC123\n";

    $formats = CsvFormatMapping::getSystemFormats();
    $format = new CsvFormatMapping($formats['dnb']);

    $result = $this->parser->parse($csv, $format);

    expect($result[0]['raw_data'])->toBeArray();
    expect($result[0]['raw_data'][0])->toBe('01.01.2025');
    expect($result[0]['raw_data'][1])->toBe('Test transaksjon');
});

it('detects delimiter', function () {
    $semicolonCsv = "Dato;Tekst;Ut;Inn\n01.01.2025;Test;;1000\n";
    $commaCsv = "Date,Text,Out,In\n2025-01-01,Test,,1000\n";

    $semicolonResult = $this->parser->parseWithAutoDetect($semicolonCsv);
    $commaResult = $this->parser->parseWithAutoDetect($commaCsv);

    expect($semicolonResult['data'])->toHaveCount(1);
    expect($commaResult['data'])->toHaveCount(1);
});

it('handles norwegian characters in utf-8', function () {
    // UTF-8 encoded Norwegian characters
    $csv = "Dato;Tekst;Ut av konto;Inn på konto\n";
    $csv .= "01.01.2025;Lønn fra Bærums Verk AS;;25000,00\n";
    $csv .= "02.01.2025;Kjøp hos Rørlegger Ås;1500,00;;\n";
    $csv .= "03.01.2025;Overføring til Ærlig Møbler;;500,00\n";

    $formats = CsvFormatMapping::getSystemFormats();
    $format = new CsvFormatMapping($formats['dnb']);
    $format->encoding = 'UTF-8'; // Override to UTF-8 for this test

    $result = $this->parser->parse($csv, $format);

    expect($result)->toHaveCount(3);
    expect($result[0]['description'])->toBe('Lønn fra Bærums Verk AS');
    expect($result[1]['description'])->toBe('Kjøp hos Rørlegger Ås');
    expect($result[2]['description'])->toBe('Overføring til Ærlig Møbler');
});

it('handles norwegian characters in iso-8859-1', function () {
    // ISO-8859-1 encoded Norwegian characters (æ=0xE6, ø=0xF8, å=0xE5)
    $csv = "Dato;Tekst;Ut av konto;Inn p\xE5 konto\n";
    $csv .= "01.01.2025;L\xF8nn fra B\xE6rums Verk AS;;25000,00\n";
    $csv .= "02.01.2025;Kj\xF8p hos R\xF8rlegger \xC5s;1500,00;;\n";

    $formats = CsvFormatMapping::getSystemFormats();
    $format = new CsvFormatMapping($formats['dnb']);

    $result = $this->parser->parse($csv, $format);

    expect($result)->toHaveCount(2);
    expect($result[0]['description'])->toBe('Lønn fra Bærums Verk AS');
    expect($result[1]['description'])->toBe('Kjøp hos Rørlegger Ås');
});

it('auto-detects and converts iso-8859-1 with norwegian characters', function () {
    // ISO-8859-1 encoded content that should be auto-detected
    $csv = "Dato;Tekst;Ut av konto;Inn p\xE5 konto\n";
    $csv .= "01.01.2025;Varekj\xF8p \xD8stlandske M\xF8bler;;5000,00\n";

    $result = $this->parser->parseWithAutoDetect($csv);

    expect($result['format'])->toBe('dnb');
    expect($result['data'])->toHaveCount(1);
    expect($result['data'][0]['description'])->toBe('Varekjøp Østlandske Møbler');
});

it('handles utf-8 bom', function () {
    // UTF-8 with BOM
    $csv = "\xEF\xBB\xBFDato;Tekst;Ut av konto;Inn på konto\n";
    $csv .= "01.01.2025;Betaling Blåfjell Ferie;;1000,00\n";

    $result = $this->parser->parseWithAutoDetect($csv);

    expect($result['format'])->toBe('dnb');
    expect($result['data'])->toHaveCount(1);
    expect($result['data'][0]['description'])->toBe('Betaling Blåfjell Ferie');
});
