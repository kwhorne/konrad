<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountSuggestion;
use App\Models\Contact;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AccountSuggestionService
{
    /**
     * Foreslå en konto basert på leverandør og beskrivelse.
     */
    public function suggestAccount(?Contact $supplier, string $description): ?Account
    {
        if (! $supplier) {
            return $this->suggestFromKeywordsOnly($description);
        }

        $keywords = $this->extractKeywords($description);

        if (empty($keywords)) {
            return null;
        }

        // 1. Søk i historikk for denne leverandøren
        $suggestion = AccountSuggestion::query()
            ->where('contact_id', $supplier->id)
            ->whereIn('keyword', $keywords)
            ->orderByDesc('usage_count')
            ->first();

        if ($suggestion) {
            return $suggestion->account;
        }

        // 2. Søk på tvers av alle leverandører
        return $this->suggestFromKeywordsOnly($description);
    }

    /**
     * Foreslå konto kun basert på nøkkelord (uten leverandør).
     */
    public function suggestFromKeywordsOnly(string $description): ?Account
    {
        $keywords = $this->extractKeywords($description);

        if (empty($keywords)) {
            return null;
        }

        $suggestion = AccountSuggestion::query()
            ->whereIn('keyword', $keywords)
            ->orderByDesc('usage_count')
            ->first();

        return $suggestion?->account;
    }

    /**
     * Registrer kontobruk for læring.
     */
    public function recordUsage(Contact $supplier, string $description, Account $account): void
    {
        $keywords = $this->extractKeywords($description);

        foreach ($keywords as $keyword) {
            AccountSuggestion::query()
                ->updateOrInsert(
                    [
                        'contact_id' => $supplier->id,
                        'keyword' => $keyword,
                        'account_id' => $account->id,
                    ],
                    [
                        'usage_count' => DB::raw('COALESCE(usage_count, 0) + 1'),
                        'updated_at' => now(),
                        'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                    ]
                );
        }
    }

    /**
     * Ekstraher relevante nøkkelord fra tekst.
     *
     * @return array<string>
     */
    public function extractKeywords(string $text): array
    {
        $text = Str::lower($text);

        // Norske stoppord
        $stopWords = [
            'og', 'i', 'på', 'for', 'med', 'til', 'fra', 'av', 'en', 'et', 'den', 'det',
            'de', 'er', 'som', 'var', 'har', 'om', 'vi', 'du', 'deg', 'meg', 'seg',
            'sin', 'sitt', 'sine', 'han', 'hun', 'dem', 'denne', 'dette', 'disse',
            'ved', 'kan', 'skal', 'vil', 'må', 'etter', 'før', 'under', 'over',
            'mellom', 'gjennom', 'hos', 'mot', 'ut', 'inn', 'opp', 'ned',
            'faktura', 'fakturanr', 'nr', 'dato', 'beløp', 'mva', 'total', 'sum',
            'periode', 'konto', 'kontonr', 'ref', 'referanse', 'betaling',
        ];

        // Split på whitespace og spesialtegn
        $words = preg_split('/[\s\-_\/\\\\.,;:!?()]+/', $text);
        $words = array_filter($words, fn ($w) => strlen($w) > 3 && ! in_array($w, $stopWords) && ! is_numeric($w)
        );

        // Fjern duplikater og begrens antall
        $words = array_unique($words);
        $words = array_slice($words, 0, 10);

        return array_values($words);
    }

    /**
     * Hent de mest brukte kontiene for en leverandør.
     *
     * @return array<Account>
     */
    public function getMostUsedAccounts(Contact $supplier, int $limit = 5): array
    {
        return AccountSuggestion::query()
            ->where('contact_id', $supplier->id)
            ->with('account')
            ->selectRaw('account_id, SUM(usage_count) as total_usage')
            ->groupBy('account_id')
            ->orderByDesc('total_usage')
            ->limit($limit)
            ->get()
            ->pluck('account')
            ->filter()
            ->toArray();
    }
}
