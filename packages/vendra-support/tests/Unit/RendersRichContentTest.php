<?php

declare(strict_types=1);

use Misaf\VendraSupport\Filament\Concerns\RendersRichContent;

$renderer = new class
{
    use RendersRichContent;

    /** @param array<array-key, mixed>|string|null $state */
    public static function render(array|string|null $state): string
    {
        return self::renderRichContent($state);
    }
};

it('renders plain string state', function () use ($renderer): void {
    expect($renderer::render('Plain text'))->toContain('Plain text');
});

it('renders empty state as an empty string', function () use ($renderer): void {
    expect($renderer::render(null))->toBeEmpty()
        ->and($renderer::render(''))->toBeEmpty()
        ->and($renderer::render([]))->toBeEmpty();
});

it('renders a valid tiptap document', function () use ($renderer): void {
    $html = $renderer::render([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'paragraph',
                'content' => [['type' => 'text', 'text' => 'Hello world']],
            ],
        ],
    ]);

    expect($html)->toContain('Hello world');
});

it('normalizes legacy documents with bare top-level text nodes', function () use ($renderer): void {
    $html = $renderer::render([
        'type' => 'doc',
        'content' => [['type' => 'text', 'text' => 'Bare text node']],
    ]);

    expect($html)->toContain('Bare text node');
});

it('falls back to escaped plain text for unparseable state', function () use ($renderer): void {
    $html = $renderer::render([
        'en' => ['type' => 'doc', 'content' => [['type' => 'text', 'text' => 'Nested <b>value</b>']]],
        'fa' => '',
    ]);

    expect($html)->toContain('Nested')
        ->and($html)->not->toContain('<b>');
});
