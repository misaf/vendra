<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Tables\Columns;

use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;

final class SlugColumn extends TextColumn
{
    public static function getDefaultName(): string
    {
        return 'slug';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('vendra-support::attributes.slug'))
            ->icon(Heroicon::Link)
            ->alignStart()
            ->toggleable(isToggledHiddenByDefault: true);
    }
}
