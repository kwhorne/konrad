<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnualAccountNote extends Model
{
    use HasFactory;

    // Standard notetyper for norsk årsregnskap
    public const NOTE_TYPES = [
        'accounting_principles' => [
            'title' => 'Regnskapsprinsipper',
            'required' => true,
            'order' => 1,
        ],
        'employees' => [
            'title' => 'Ansatte, godtgjørelser m.v.',
            'required' => false,
            'order' => 2,
        ],
        'fixed_assets' => [
            'title' => 'Varige driftsmidler',
            'required' => false,
            'order' => 3,
        ],
        'share_capital' => [
            'title' => 'Aksjekapital og aksjonærer',
            'required' => false,
            'order' => 4,
        ],
        'equity' => [
            'title' => 'Egenkapital',
            'required' => true,
            'order' => 5,
        ],
        'debt' => [
            'title' => 'Gjeld',
            'required' => false,
            'order' => 6,
        ],
        'tax' => [
            'title' => 'Skatt',
            'required' => false,
            'order' => 7,
        ],
        'related_parties' => [
            'title' => 'Nærstående parter',
            'required' => false,
            'order' => 8,
        ],
        'subsequent_events' => [
            'title' => 'Hendelser etter balansedagen',
            'required' => false,
            'order' => 9,
        ],
        'other' => [
            'title' => 'Andre forhold',
            'required' => false,
            'order' => 99,
        ],
    ];

    protected $fillable = [
        'annual_account_id',
        'note_number',
        'note_type',
        'title',
        'content',
        'structured_data',
        'sort_order',
        'is_required',
        'is_visible',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'note_number' => 'integer',
            'structured_data' => 'array',
            'sort_order' => 'integer',
            'is_required' => 'boolean',
            'is_visible' => 'boolean',
        ];
    }

    // Relationships
    public function annualAccount(): BelongsTo
    {
        return $this->belongsTo(AnnualAccount::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('note_type', $type);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('note_number');
    }

    // Accessors
    public function getTypeLabel(): string
    {
        return self::NOTE_TYPES[$this->note_type]['title'] ?? $this->note_type;
    }

    public function getFormattedNoteNumber(): string
    {
        return 'Note '.$this->note_number;
    }

    // Static helpers
    public static function getTypeInfo(string $type): ?array
    {
        return self::NOTE_TYPES[$type] ?? null;
    }

    public static function getDefaultTitle(string $type): string
    {
        return self::NOTE_TYPES[$type]['title'] ?? $type;
    }

    public static function getDefaultOrder(string $type): int
    {
        return self::NOTE_TYPES[$type]['order'] ?? 99;
    }

    public static function isTypeRequired(string $type): bool
    {
        return self::NOTE_TYPES[$type]['required'] ?? false;
    }

    // Templates for common notes
    public static function getAccountingPrinciplesTemplate(): string
    {
        return <<<'EOT'
Årsregnskapet er utarbeidet i samsvar med regnskapsloven og god regnskapsskikk i Norge.

**Hovedregel for vurdering og klassifisering av eiendeler og gjeld**
Eiendeler bestemt til varig eie eller bruk er klassifisert som anleggsmidler. Andre eiendeler er klassifisert som omløpsmidler. Fordringer som skal tilbakebetales innen et år er klassifisert som omløpsmidler. Ved klassifisering av kortsiktig og langsiktig gjeld er tilsvarende kriterier lagt til grunn.

Anleggsmidler vurderes til anskaffelseskost, men nedskrives til virkelig verdi når verdifallet forventes ikke å være forbigående. Anleggsmidler med begrenset økonomisk levetid avskrives planmessig.

Omløpsmidler vurderes til laveste av anskaffelseskost og virkelig verdi.

**Driftsinntekter**
Inntekt regnskapsføres når den er opptjent, det vil si når krav på vederlag oppstår.

**Skatt**
Skattekostnaden i resultatregnskapet omfatter både periodens betalbare skatt og endring i utsatt skatt.
EOT;
    }

    public static function getEmployeesTemplate(): string
    {
        return <<<'EOT'
**Antall ansatte**
Gjennomsnittlig antall ansatte i regnskapsåret har vært {EMPLOYEES}.

**Lønnskostnader**
| Post | Beløp |
|------|-------|
| Lønninger | {SALARIES} |
| Folketrygdavgift | {PAYROLL_TAX} |
| Pensjonskostnader | {PENSION} |
| Andre ytelser | {OTHER} |
| **Sum** | **{TOTAL}** |

**Ytelser til ledende personer**
| Stilling | Lønn | Andre godtgjørelser |
|----------|------|---------------------|
| Daglig leder | {CEO_SALARY} | {CEO_OTHER} |
| Styret | {BOARD_FEE} | - |
EOT;
    }

    public static function getEquityTemplate(): string
    {
        return <<<'EOT'
**Endring i egenkapital**
| Post | Aksjekapital | Overkurs | Annen EK | Sum |
|------|--------------|----------|----------|-----|
| Pr. 01.01 | {SHARE_CAPITAL_OB} | {PREMIUM_OB} | {RETAINED_OB} | {TOTAL_OB} |
| Årets resultat | - | - | {NET_PROFIT} | {NET_PROFIT} |
| Utbytte | - | - | {DIVIDENDS} | {DIVIDENDS} |
| Pr. 31.12 | {SHARE_CAPITAL_CB} | {PREMIUM_CB} | {RETAINED_CB} | {TOTAL_CB} |
EOT;
    }
}
