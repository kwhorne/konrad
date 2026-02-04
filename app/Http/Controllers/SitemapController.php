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

# Sitemap and LLMs.txt
Sitemap: {$sitemapUrl}
LLMs-Txt: {$llmsTxtUrl}
ROBOTS;

        return response($content, 200)
            ->header('Content-Type', 'text/plain');
    }

    public function llmsTxt(): Response
    {
        $content = <<<'LLMS'
# Konrad Office

> Konrad Office er et komplett norsk forretningssystem for små og mellomstore bedrifter.

## Om Konrad Office

Konrad Office er en moderne SaaS-plattform som tilbyr:

- **Fakturering**: Opprett og send profesjonelle fakturaer med norsk MVA-håndtering
- **Regnskap**: Komplett regnskapssystem med bilagsføring og rapporter
- **Prosjektstyring**: Planlegg og følg opp prosjekter med timeregistrering
- **Kontraktshåndtering**: Administrer kontrakter og avtaler
- **Kontakter/CRM**: Hold oversikt over kunder og leverandører
- **Lønn**: Norsk lønnssystem med A-melding, skattetrekk og feriepenger
- **Aksjonærregister**: Oversikt over aksjer og eierforhold

## Målgruppe

- Norske AS og ENK
- Små og mellomstore bedrifter (1-50 ansatte)
- Bedrifter som ønsker et integrert forretningssystem

## Nøkkelfunksjoner

- Skreddersydd for norske krav (MVA, A-melding, SAF-T)
- Modulbasert - betal kun for det du bruker
- Moderne og brukervennlig grensesnitt
- Integrasjoner med norske banker og offentlige registre

## Kontakt

- Nettside: https://konradoffice.no
- E-post: kontakt@konradoffice.no

## Offentlige sider

- Forside: /
- Priser: /priser
- Om oss: /om-oss
- Kontakt: /kontakt
- Blogg/Innsikt: /innsikt
- Personvern: /personvern
- Vilkår: /vilkar
LLMS;

        return response($content, 200)
            ->header('Content-Type', 'text/plain; charset=utf-8');
    }
}
