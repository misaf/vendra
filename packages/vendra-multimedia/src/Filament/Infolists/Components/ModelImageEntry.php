<?php

declare(strict_types=1);

namespace Misaf\VendraMultimedia\Filament\Infolists\Components;

use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;

final class ModelImageEntry extends SpatieMediaLibraryImageEntry
{
    public static function getDefaultName(): string
    {
        return 'image';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('vendra-multimedia::attributes.image'))
            ->columnSpanFull();
    }
}
