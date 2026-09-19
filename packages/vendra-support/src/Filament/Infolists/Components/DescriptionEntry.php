<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Infolists\Components;

use Filament\Infolists\Components\TextEntry;
use Misaf\VendraSupport\Filament\Concerns\RendersRichContent;

final class DescriptionEntry extends TextEntry
{
    use RendersRichContent;

    public static function getDefaultName(): string
    {
        return 'description';
    }

    public function richContent(): static
    {
        return $this
            ->formatStateUsing(fn (array|string|null $state): string => self::renderRichContent($state))
            ->html();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('vendra-support::attributes.description'))
            ->columnSpanFull();
    }
}
