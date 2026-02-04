<?php

use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('SEO Routes', function () {
    test('robots.txt returns correct content type and includes sitemap', function () {
        $response = $this->get('/robots.txt');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('User-agent: *')
            ->assertSee('Sitemap:')
            ->assertSee('sitemap.xml')
            ->assertSee('Disallow: /admin/')
            ->assertSee('Disallow: /app/')
            ->assertSee('User-agent: GPTBot')
            ->assertSee('User-agent: Claude-Web')
            ->assertSee('LLMs-Txt:');
    });

    test('sitemap.xml returns valid XML with static pages', function () {
        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/xml');

        $content = $response->getContent();
        expect($content)->toContain('<?xml version="1.0" encoding="UTF-8"?>')
            ->toContain('<urlset')
            ->toContain(url('/'))
            ->toContain(url('/priser'))
            ->toContain(url('/om-oss'))
            ->toContain(url('/kontakt'))
            ->toContain(url('/innsikt'));
    });

    test('sitemap.xml includes published blog posts', function () {
        $post = Post::factory()->create([
            'is_published' => true,
            'slug' => 'test-article',
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200)
            ->assertSee(route('blog.show', 'test-article'));
    });

    test('sitemap.xml excludes unpublished posts', function () {
        $post = Post::factory()->create([
            'is_published' => false,
            'slug' => 'draft-article',
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200)
            ->assertDontSee('draft-article');
    });

    test('sitemap.xml includes categories with published posts', function () {
        $category = PostCategory::factory()->create(['slug' => 'test-category']);
        Post::factory()->create([
            'is_published' => true,
            'post_category_id' => $category->id,
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200)
            ->assertSee(route('blog.category', 'test-category'));
    });

    test('llms.txt returns correct content', function () {
        $response = $this->get('/llms.txt');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'text/plain; charset=utf-8')
            ->assertSee('# Konrad Office')
            ->assertSee('Fakturering')
            ->assertSee('Regnskap')
            ->assertSee('Lønn')
            ->assertSee('https://konradoffice.no');
    });
});
