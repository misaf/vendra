<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Tables\Columns;

use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;

final class DescriptionColumn extends TextColumn
{
    public static function getDefaultName(): string
    {
        return 'description';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('vendra-support::attributes.description'))
            ->icon(Heroicon::DocumentText)
            ->toggleable(isToggledHiddenByDefault: true);
    }
}
