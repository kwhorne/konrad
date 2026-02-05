<?php

use App\Models\UserNote;

describe('UserNote HTML Sanitization', function () {
    test('strips script tags from content', function () {
        $note = new UserNote;
        $note->content = '<p>Hello</p><script>alert("xss")</script><p>World</p>';

        expect($note->content)->toBe('<p>Hello</p>alert("xss")<p>World</p>');
    });

    test('strips iframe tags from content', function () {
        $note = new UserNote;
        $note->content = '<p>Content</p><iframe src="https://evil.com"></iframe>';

        expect($note->content)->not->toContain('<iframe');
    });

    test('preserves safe formatting tags', function () {
        $html = '<h2>Title</h2><p>Text with <strong>bold</strong> and <em>italic</em></p>'
            .'<ul><li>Item</li></ul><blockquote>Quote</blockquote>';

        $note = new UserNote;
        $note->content = $html;

        expect($note->content)->toContain('<h2>Title</h2>')
            ->and($note->content)->toContain('<strong>bold</strong>')
            ->and($note->content)->toContain('<em>italic</em>')
            ->and($note->content)->toContain('<ul><li>Item</li></ul>')
            ->and($note->content)->toContain('<blockquote>Quote</blockquote>');
    });

    test('strips event handler attributes', function () {
        $note = new UserNote;
        $note->content = '<p onclick="alert(1)">Click</p><img src="x" onerror="alert(2)">';

        expect($note->content)->not->toContain('onclick')
            ->and($note->content)->not->toContain('onerror');
    });

    test('strips javascript protocol in href', function () {
        $note = new UserNote;
        $note->content = '<a href="javascript:alert(1)">Evil link</a>';

        expect($note->content)->not->toContain('javascript:');
    });

    test('handles null content gracefully', function () {
        $note = new UserNote;
        $note->content = null;

        expect($note->content)->toBeNull();
    });
});
