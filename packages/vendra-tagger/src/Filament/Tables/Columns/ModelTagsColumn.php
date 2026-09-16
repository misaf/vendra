<?php

declare(strict_types=1);

namespace Misaf\VendraTagger\Filament\Tables\Columns;

use Filament\Tables\Columns\SpatieTagsColumn;

final class ModelTagsColumn extends SpatieTagsColumn
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
            ->toggleable();
    }
}
