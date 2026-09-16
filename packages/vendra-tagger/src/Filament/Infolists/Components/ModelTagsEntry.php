<?php

declare(strict_types=1);

namespace Misaf\VendraTagger\Filament\Infolists\Components;

use Filament\Infolists\Components\SpatieTagsEntry;

final class ModelTagsEntry extends SpatieTagsEntry
{
    public static function getDefaultName(): string
    {
        return 'tags';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('vendra-tagger::attributes.tags'))
            ->columnSpanFull();
    }
}
