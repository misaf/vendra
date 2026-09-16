<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Tables\Columns;

use Filament\Tables\Columns\TextColumn;

final class RowIndexColumn extends TextColumn
{
    public static function getDefaultName(): string
    {
        return 'row';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('#')
            ->rowIndex()
            ->sortable(['id']);
    }
}
