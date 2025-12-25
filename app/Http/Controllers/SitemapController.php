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

        $content = <<<ROBOTS
User-agent: *
Allow: /

# Disallow admin and app sections
Disallow: /admin/
Disallow: /app/
Disallow: /login
Disallow: /logout

# Allow blog and public pages
Allow: /innsikt/
Allow: /om-oss
Allow: /kontakt
Allow: /priser
Allow: /bestill
Allow: /personvern
Allow: /vilkar

# Sitemap
Sitemap: {$sitemapUrl}
ROBOTS;

        return response($content, 200)
            ->header('Content-Type', 'text/plain');
    }
}
