<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Concerns;

use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Illuminate\Support\Arr;
use Throwable;

trait RendersRichContent
{
    /**
     * Render persisted rich-editor state defensively: plain strings render
     * as-is, TipTap documents are normalized first, and content the renderer
     * cannot parse degrades to escaped plain text instead of failing the page.
     *
     * @param  array<array-key, mixed>|string|null  $state
     */
    protected static function renderRichContent(array|string|null $state): string
    {
        if ($state === null || $state === [] || $state === '') {
            return '';
        }

        try {
            return RichContentRenderer::make(
                is_array($state) ? self::normalizeRichDocument($state) : $state,
            )->toHtml();
        } catch (Throwable) {
            return e(self::flattenRichText($state));
        }
    }

    /**
     * Wrap stray top-level text nodes in paragraphs so legacy documents match
     * the TipTap schema.
     *
     * @param  array<array-key, mixed>  $document
     * @return array<array-key, mixed>
     */
    private static function normalizeRichDocument(array $document): array
    {
        if (! is_array(Arr::get($document, 'content', null))) {
            return $document;
        }

        $document['content'] = array_map(
            fn (mixed $node): mixed => is_array($node) && 'text' === (Arr::get($node, 'type', null))
                ? ['type' => 'paragraph', 'content' => [$node]]
                : $node,
            Arr::get($document, 'content'),
        );

        return $document;
    }

    /**
     * @param  array<array-key, mixed>|string  $state
     */
    private static function flattenRichText(array|string $state): string
    {
        if (is_string($state)) {
            return $state;
        }

        $text = [];

        array_walk_recursive($state, function (mixed $value, mixed $key) use (&$text): void {
            if ($key === 'text' && is_string($value)) {
                $text[] = $value;
            }
        });

        return implode(' ', $text);
    }
}
