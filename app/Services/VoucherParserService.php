<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\IncomingVoucher;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Prism\Prism\ValueObjects\Media\Image;

class VoucherParserService
{
    public function __construct(
        protected AccountSuggestionService $accountSuggestionService
    ) {}

    /**
     * Parse et inngående bilag med AI.
     *
     * @return array<string, mixed>
     */
    public function parse(IncomingVoucher $voucher): array
    {
        $voucher->update(['status' => IncomingVoucher::STATUS_PARSING]);

        try {
            // Hent filinnhold
            $content = $this->getFileContent($voucher);

            // Kall AI via Prism
            $response = $this->callAI($voucher, $content);

            // Parse JSON-respons
            $data = $this->parseResponse($response);

            // Finn eller match leverandør
            $supplier = $this->matchSupplier($data);

            // Foreslå konto basert på historikk
            $suggestedAccount = $this->accountSuggestionService->suggestAccount(
                $supplier,
                $data['description'] ?? ''
            );

            // Oppdater IncomingVoucher med tolkede data
            $voucher->update([
                'status' => IncomingVoucher::STATUS_PARSED,
                'parsed_at' => now(),
                'parsed_data' => $data,
                'suggested_supplier_id' => $supplier?->id,
                'suggested_invoice_number' => $data['invoice_number'] ?? null,
                'suggested_invoice_date' => $this->parseDate($data['invoice_date'] ?? null),
                'suggested_due_date' => $this->parseDate($data['due_date'] ?? null),
                'suggested_total' => $data['total'] ?? null,
                'suggested_vat_total' => $data['vat_total'] ?? null,
                'suggested_account_id' => $suggestedAccount?->id,
                'confidence_score' => $data['confidence'] ?? 0.5,
            ]);

            return $data;
        } catch (\Exception $e) {
            Log::error('Feil ved parsing av bilag', [
                'voucher_id' => $voucher->id,
                'error' => $e->getMessage(),
            ]);

            $voucher->update([
                'status' => IncomingVoucher::STATUS_PENDING,
                'parsed_data' => ['error' => $e->getMessage()],
            ]);

            throw $e;
        }
    }

    /**
     * Hent filinnhold for AI-analyse.
     *
     * @return array<int, Image>|string
     */
    protected function getFileContent(IncomingVoucher $voucher): array|string
    {
        $disk = config('voucher.storage.disk', 'local');
        $path = $voucher->file_path;

        if (! Storage::disk($disk)->exists($path)) {
            throw new \RuntimeException("Filen finnes ikke: {$path}");
        }

        // For bilder, returner Image-objekt for visuell analyse
        if ($voucher->is_image) {
            return [Image::fromStoragePath($path, $disk)];
        }

        // For PDF-er, prøv å ekstrahere tekst eller konvertere til bilde
        if ($voucher->is_pdf) {
            return $this->handlePdf($voucher, $disk, $path);
        }

        // Fallback: les som tekst
        return Storage::disk($disk)->get($path);
    }

    /**
     * Håndter PDF-filer.
     *
     * @return array<int, Image>|string
     */
    protected function handlePdf(IncomingVoucher $voucher, string $disk, string $path): array|string
    {
        // Prøv å bruke pdftotext hvis tilgjengelig
        $fullPath = Storage::disk($disk)->path($path);

        if ($this->canUsePdfToText()) {
            $text = $this->extractTextFromPdf($fullPath);
            if (! empty(trim($text))) {
                return $text;
            }
        }

        // Fallback: Konverter første side til bilde for visuell analyse
        if ($this->canUseImageMagick()) {
            $imagePath = $this->convertPdfToImage($fullPath);
            if ($imagePath) {
                return [Image::fromLocalPath($imagePath)];
            }
        }

        // Siste utvei: Les PDF-innhold direkte (fungerer sjelden bra)
        return Storage::disk($disk)->get($path);
    }

    /**
     * Sjekk om pdftotext er tilgjengelig.
     */
    protected function canUsePdfToText(): bool
    {
        exec('which pdftotext 2>/dev/null', $output, $returnCode);

        return $returnCode === 0;
    }

    /**
     * Ekstraher tekst fra PDF med pdftotext.
     */
    protected function extractTextFromPdf(string $pdfPath): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'pdf_text_');
        exec("pdftotext -layout \"{$pdfPath}\" \"{$tempFile}\" 2>/dev/null", $output, $returnCode);

        if ($returnCode === 0 && file_exists($tempFile)) {
            $text = file_get_contents($tempFile);
            unlink($tempFile);

            return $text ?: '';
        }

        return '';
    }

    /**
     * Sjekk om ImageMagick er tilgjengelig.
     */
    protected function canUseImageMagick(): bool
    {
        exec('which convert 2>/dev/null', $output, $returnCode);

        return $returnCode === 0;
    }

    /**
     * Konverter PDF til bilde med ImageMagick.
     */
    protected function convertPdfToImage(string $pdfPath): ?string
    {
        $imagePath = tempnam(sys_get_temp_dir(), 'pdf_img_').'.png';
        exec("convert -density 150 \"{$pdfPath}[0]\" -quality 90 \"{$imagePath}\" 2>/dev/null", $output, $returnCode);

        if ($returnCode === 0 && file_exists($imagePath)) {
            return $imagePath;
        }

        return null;
    }

    /**
     * Kall AI via Prism for å tolke bilag.
     *
     * @param  array<int, Image>|string  $content
     */
    protected function callAI(IncomingVoucher $voucher, array|string $content): string
    {
        $provider = config('voucher.ai.provider', 'openai');
        $model = config('voucher.ai.model', 'gpt-4o');

        $request = prism()
            ->text()
            ->using($provider, $model)
            ->withSystemPrompt($this->getSystemPrompt());

        if (is_array($content)) {
            // Bilde-innhold for visuell analyse
            $request->withPrompt(
                'Analyser dette bildet av en leverandørfaktura og ekstraher all relevant informasjon. Returner resultatet som JSON.',
                $content
            );
        } else {
            // Tekst-innhold
            $request->withPrompt($this->buildTextPrompt($content));
        }

        $response = $request->asText();

        return $response->text;
    }

    /**
     * Hent system-prompt for AI.
     */
    protected function getSystemPrompt(): string
    {
        return <<<'PROMPT'
Du er en norsk regnskapsassistent som tolker leverandørfakturaer.
Ekstraher følgende informasjon fra fakturaen og returner BARE gyldig JSON (ingen markdown, ingen kodeblokker):

{
  "supplier_name": "Leverandørens navn",
  "supplier_org_number": "Organisasjonsnummer (9 siffer uten mellomrom)",
  "invoice_number": "Fakturanummer",
  "invoice_date": "Fakturadato (YYYY-MM-DD)",
  "due_date": "Forfallsdato (YYYY-MM-DD)",
  "subtotal": 0.00,
  "vat_total": 0.00,
  "total": 0.00,
  "currency": "NOK",
  "description": "Kort beskrivelse av hva fakturaen gjelder",
  "line_items": [
    {"description": "Linjebeskrivelse", "quantity": 1, "unit_price": 0.00, "vat_percent": 25}
  ],
  "kid_number": "KID-nummer hvis tilgjengelig",
  "bank_account": "Kontonummer for betaling",
  "confidence": 0.0
}

Viktige regler:
- Alle beløp skal være desimaltall (ikke strenger)
- Datoer skal være i format YYYY-MM-DD
- Organisasjonsnummer skal være 9 siffer uten mellomrom eller punktum
- Sett confidence mellom 0.0 og 1.0 basert på hvor sikker du er på tolkningen
- Hvis du ikke finner en verdi, bruk null
- Returner KUN JSON, ingen forklaringer eller markdown
PROMPT;
    }

    /**
     * Bygg prompt for tekstbasert analyse.
     */
    protected function buildTextPrompt(string $content): string
    {
        return "Analyser denne teksten fra en leverandørfaktura og ekstraher all relevant informasjon. Returner resultatet som JSON.\n\nFakturainnhold:\n{$content}";
    }

    /**
     * Parse AI-responsen til et array.
     *
     * @return array<string, mixed>
     */
    protected function parseResponse(string $response): array
    {
        // Fjern eventuell markdown code block
        $response = preg_replace('/^```json?\s*/m', '', $response);
        $response = preg_replace('/```\s*$/m', '', $response);
        $response = trim($response);

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('Kunne ikke parse AI-respons som JSON', [
                'response' => $response,
                'error' => json_last_error_msg(),
            ]);

            return [
                'error' => 'Kunne ikke tolke AI-respons',
                'raw_response' => $response,
                'confidence' => 0,
            ];
        }

        return $data;
    }

    /**
     * Finn eller match leverandør basert på tolkede data.
     */
    protected function matchSupplier(array $data): ?Contact
    {
        // 1. Søk etter org.nr
        if (! empty($data['supplier_org_number'])) {
            $orgNumber = preg_replace('/\D/', '', $data['supplier_org_number']);
            $supplier = Contact::query()
                ->where('organization_number', $orgNumber)
                ->where('type', 'supplier')
                ->first();

            if ($supplier) {
                return $supplier;
            }
        }

        // 2. Søk etter navn (eksakt match)
        if (! empty($data['supplier_name'])) {
            $supplier = Contact::query()
                ->where('company_name', $data['supplier_name'])
                ->where('type', 'supplier')
                ->first();

            if ($supplier) {
                return $supplier;
            }
        }

        // 3. Søk etter navn (fuzzy match)
        if (! empty($data['supplier_name'])) {
            $supplier = Contact::query()
                ->where('company_name', 'like', '%'.str_replace(' ', '%', $data['supplier_name']).'%')
                ->where('type', 'supplier')
                ->first();

            if ($supplier) {
                return $supplier;
            }
        }

        return null;
    }

    /**
     * Parse dato fra streng til Carbon-dato.
     */
    protected function parseDate(?string $dateString): ?\Carbon\Carbon
    {
        if (empty($dateString)) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($dateString);
        } catch (\Exception $e) {
            return null;
        }
    }
}
