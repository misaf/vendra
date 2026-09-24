<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Concerns;

use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Illuminate\Support\Arr;
use Throwable;

trait RendersRichContent
{
    /**
     * Render stored rich-editor content, falling back to escaped text if it cannot be parsed.
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
     * Wrap stray top-level text nodes in paragraphs to match the TipTap schema.
     *
     * @param  array<array-key, mixed>  $document
     * @return array<string, mixed>
     */
    private static function normalizeRichDocument(array $document): array
    {
        $document = array_filter($document, is_string(...), ARRAY_FILTER_USE_KEY);
        $content = Arr::get($document, 'content', null);

        if (! is_array($content)) {
            return $document;
        }

        $document['content'] = array_map(
            fn (mixed $node): mixed => is_array($node) && 'text' === (Arr::get($node, 'type', null))
                ? ['type' => 'paragraph', 'content' => [$node]]
                : $node,
            $content,
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
