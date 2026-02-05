<?php

use App\Models\Post;

describe('Post HTML Sanitization', function () {
    test('strips script tags from body', function () {
        $post = new Post;
        $post->body = '<p>Hello</p><script>alert("xss")</script><p>World</p>';

        expect($post->body)->toBe('<p>Hello</p>alert("xss")<p>World</p>');
    });

    test('strips iframe tags from body', function () {
        $post = new Post;
        $post->body = '<p>Content</p><iframe src="https://evil.com"></iframe>';

        expect($post->body)->not->toContain('<iframe');
    });

    test('strips object and embed tags from body', function () {
        $post = new Post;
        $post->body = '<p>Content</p><object data="evil.swf"></object><embed src="evil.swf">';

        expect($post->body)->not->toContain('<object')
            ->and($post->body)->not->toContain('<embed');
    });

    test('strips form and input tags from body', function () {
        $post = new Post;
        $post->body = '<p>Content</p><form action="/steal"><input type="text"></form>';

        expect($post->body)->not->toContain('<form')
            ->and($post->body)->not->toContain('<input');
    });

    test('preserves safe formatting tags', function () {
        $html = '<h1>Title</h1><h2>Subtitle</h2><p>Text with <strong>bold</strong> and <em>italic</em></p>'
            .'<ul><li>Item 1</li></ul><ol><li>Item 2</li></ol>'
            .'<blockquote>Quote</blockquote><code>code</code><pre>preformatted</pre>';

        $post = new Post;
        $post->body = $html;

        expect($post->body)->toContain('<h1>Title</h1>')
            ->and($post->body)->toContain('<h2>Subtitle</h2>')
            ->and($post->body)->toContain('<strong>bold</strong>')
            ->and($post->body)->toContain('<em>italic</em>')
            ->and($post->body)->toContain('<ul><li>Item 1</li></ul>')
            ->and($post->body)->toContain('<blockquote>Quote</blockquote>')
            ->and($post->body)->toContain('<code>code</code>')
            ->and($post->body)->toContain('<pre>preformatted</pre>');
    });

    test('preserves links and images', function () {
        $post = new Post;
        $post->body = '<p><a href="https://example.com">Link</a> and <img src="photo.jpg" alt="Photo"></p>';

        expect($post->body)->toContain('<a href="https://example.com">Link</a>')
            ->and($post->body)->toContain('<img src="photo.jpg" alt="Photo">');
    });

    test('strips event handler attributes', function () {
        $post = new Post;
        $post->body = '<p onclick="alert(1)">Click</p><img src="x" onerror="alert(2)"><a onmouseover="alert(3)" href="#">Link</a>';

        expect($post->body)->not->toContain('onclick')
            ->and($post->body)->not->toContain('onerror')
            ->and($post->body)->not->toContain('onmouseover');
    });

    test('strips javascript protocol in href', function () {
        $post = new Post;
        $post->body = '<a href="javascript:alert(1)">Evil link</a>';

        expect($post->body)->not->toContain('javascript:');
    });

    test('strips javascript protocol in img src', function () {
        $post = new Post;
        $post->body = '<img src="javascript:alert(1)">';

        expect($post->body)->not->toContain('javascript:');
    });

    test('handles null body gracefully', function () {
        $post = new Post;
        $post->body = null;

        expect($post->body)->toBeNull();
    });

    test('preserves text alignment styles from editor', function () {
        $post = new Post;
        $post->body = '<p style="text-align: center">Centered text</p>';

        expect($post->body)->toContain('<p style="text-align: center">');
    });
});
