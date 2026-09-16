<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Tables\Columns;

use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;

final class NameColumn extends TextColumn
{
    public static function getDefaultName(): string
    {
        return 'name';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('vendra-support::attributes.name'))
            ->icon(Heroicon::Tag)
            ->alignStart();
    }
}
