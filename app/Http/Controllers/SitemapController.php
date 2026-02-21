<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function sitemap(): Response
    {
        $posts = Post::query()
            ->published()
            ->ordered()
            ->get();

        $categories = PostCategory::query()
            ->whereHas('posts', fn ($q) => $q->published())
            ->get();

        $content = view('sitemap.index', [
            'posts' => $posts,
            'categories' => $categories,
        ])->render();

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $sitemapUrl = url('/sitemap.xml');
        $llmsTxtUrl = url('/llms.txt');

        $content = <<<ROBOTS
User-agent: *
Allow: /

# Disallow admin and app sections
Disallow: /admin/
Disallow: /app/
Disallow: /login
Disallow: /logout
Disallow: /onboarding/
Disallow: /invitation/

# Allow blog and public pages
Allow: /innsikt/
Allow: /om-oss
Allow: /kontakt
Allow: /priser
Allow: /bestill
Allow: /personvern
Allow: /vilkar

# AI/LLM Crawlers - Allow access to public content
User-agent: GPTBot
Allow: /
Disallow: /admin/
Disallow: /app/
Disallow: /login

User-agent: Claude-Web
Allow: /
Disallow: /admin/
Disallow: /app/
Disallow: /login

User-agent: Anthropic-AI
Allow: /
Disallow: /admin/
Disallow: /app/
Disallow: /login

User-agent: Google-Extended
Allow: /
Disallow: /admin/
Disallow: /app/
Disallow: /login

User-agent: CCBot
Allow: /
Disallow: /admin/
Disallow: /app/
Disallow: /login

User-agent: PerplexityBot
Allow: /
Disallow: /admin/
Disallow: /app/
Disallow: /login

User-agent: OAI-SearchBot
Allow: /
Disallow: /admin/
Disallow: /app/
Disallow: /login

User-agent: Applebot-Extended
Allow: /
Disallow: /admin/
Disallow: /app/
Disallow: /login

# Sitemap and LLMs.txt
Sitemap: {$sitemapUrl}
LLMs-Txt: {$llmsTxtUrl}
ROBOTS;

        return response($content, 200)
            ->header('Content-Type', 'text/plain');
    }

    public function llmsTxt(): Response
    {
        $baseUrl = url('/');

        $content = <<<LLMS
# Konrad Office

> Konrad Office er et komplett norsk forretningssystem (SaaS) for små og mellomstore bedrifter (SMB). Systemet samler fakturering, regnskap, lønn, lager, prosjektstyring, timeregistrering og aksjonærregister i én løsning. Skreddersydd for norske AS og ENK med 1–50 ansatte.

- Nettside: https://konradoffice.no

## Hva er Konrad Office?

Konrad Office er en moderne nettbasert plattform som erstatter behovet for separate systemer for regnskap, lønn, lager og prosjektstyring. Systemet er bygget spesifikt for norske bedrifter og oppfyller alle norske lovkrav innen bokføring, lønn og rapportering.

## Moduler og funksjoner

### Salg og fakturering
- Tilbud med godkjenningsflyt og konvertering til ordre
- Ordrebehandling med varesporing
- Fakturering med norsk MVA (25 %, 15 %, 12 % og 0 %)
- PDF-generering av profesjonelle tilbud, ordrer og fakturaer
- Betalingssporing og purringer

### Regnskap
- Norsk standard kontoplan NS 4102
- Bilagsføring og automatisk bokføring fra fakturaer
- Leverandørfakturaer og innkommende bilag
- Kunde- og leverandørreskontro
- MVA-rapportering og SAF-T-eksport
- Årsoppgjør med noter og årsregnskap

### Lønn
- Full lønnskjøring for fast- og timelønte ansatte
- Automatisk skattetrekk basert på skattekort
- Feriepengeopptjening og utbetaling
- Arbeidsgiveravgift (AGA) beregning
- A-melding til Skatteetaten
- Lønnsslipper i PDF
- Ansattregister med stillinger og lønnstrinn

### Lager og innkjøp
- Lagerbeholdning med lokasjoner og lagerplasser
- Innkjøpsordrer til leverandører
- Varemottak og avvikshåndtering
- Varetelling med avviksrapport
- Lagertransaksjoner og historikk

### Prosjekter og timer
- Prosjektstyring med budsjett og fremdrift
- Timeregistrering per prosjekt og aktivitet
- Ukelister og godkjenningsflyt
- Timerapporter og lønnsgrunnlag
- Kobling mellom timer og fakturering

### Arbeidsordrer
- Ordresystem med 8 statuser og prioriteter
- Tildeling til ansvarlig medarbeider
- Timeregistrering direkte på arbeidsordre
- Kobling til kunder og produkter

### Kontakter (CRM)
- Kunde- og leverandørregister
- Aktivitetslogg per kontakt
- Organisasjonsnummer-oppslag
- Kontaktpersoner og kommunikasjonshistorikk

### Kontrakter
- Kontraktsoversikt med leverandører og kunder
- Fornyelsesvarsel
- Dokumenthåndtering
- Kontraktstyper og kategorier

### Eiendeler
- Register over maskiner, utstyr og inventar
- Eiendelskategorier og lokasjoner
- Vedlikeholdsplan og historikk

### Aksjonærregister og årsoppgjør
- Komplett aksjebok med eierstruktur
- Aksjetransaksjoner (kjøp, salg, emisjon, splitt)
- Kapitalendringer og generalforsamlinger
- Utbytteberegning og -utbetaling
- Skattemessig skjermingsfradrag
- Årsregnskap med noter
- AI-drevet selskapsanalyse (likviditet, lønnsomhet, nøkkeltall)

### Mine aktiviteter (AI)
- Intelligente prioriteringsforslag basert på ventende oppgaver
- Arbeidsmengde-score
- Personlige notater og påminnelser

## Norske lovkrav som støttes

- Bokføringsloven og regnskapsloven
- Norsk standard kontoplan NS 4102
- MVA-rapportering til Skatteetaten
- A-melding (ansatte, lønn, skattetrekk)
- SAF-T-eksport for revisjon
- Aksjeloven (aksjonærregister)

## Målgruppe

- Norske aksjeselskap (AS)
- Enkeltpersonforetak (ENK)
- Bedrifter med 1–50 ansatte
- Bransjer: handel, bygg/anlegg, konsulent, tjenesteyting, håndverk
- Bedrifter som ønsker ett integrert system fremfor mange separate løsninger

## Prissetting

- Modulbasert prissetting fra 399 kr/mnd
- Betaler kun for modulene du bruker
- Gratis prøveperiode uten betalingskort
- Detaljer: {$baseUrl}priser

## Ofte stilte spørsmål

**Hva er Konrad Office?**
Konrad Office er et komplett norsk forretningssystem som samler fakturering, regnskap, lønn, lager, prosjektstyring og aksjonærregister i én løsning.

**Hvem passer Konrad Office for?**
Norske AS og ENK med 1–50 ansatte som ønsker ett samlet system for hele driften.

**Støtter systemet A-melding?**
Ja. Lønnssystemet håndterer skattetrekk, feriepenger, AGA og innsending av A-melding til Skatteetaten.

**Hvilken kontoplan brukes?**
Norsk standard kontoplan NS 4102 med støtte for alle norske MVA-satser.

**Er aksjonærregister inkludert?**
Ja. Komplett aksjebok med transaksjoner, kapitalendringer, utbytte og skjermingsfradrag.

**Har systemet AI?**
Ja. AI-drevet selskapsanalyse og intelligente aktivitetsforslag er inkludert.

**Hva koster det?**
Fra 399 kr/mnd med modulbasert prissetting. Gratis prøveperiode tilgjengelig.

## Offentlige sider

- Forside: {$baseUrl}
- Moduler og funksjoner: {$baseUrl}#modules
- Priser: {$baseUrl}priser
- Om oss: {$baseUrl}om-oss
- Kontakt: {$baseUrl}kontakt
- Blogg/Innsikt: {$baseUrl}innsikt
- Bestill / Prøv gratis: {$baseUrl}bestill
- Personvern: {$baseUrl}personvern
- Vilkår: {$baseUrl}vilkar

## Kontakt

- E-post: kontakt@konradoffice.no
- Support: support@konradoffice.no
LLMS;

        return response($content, 200)
            ->header('Content-Type', 'text/plain; charset=utf-8');
    }
}
