<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Tables\Columns;

use Filament\Tables\Columns\TextColumn;

abstract class TimestampColumn extends TextColumn
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__("vendra-support::attributes.{$this->getName()}"))
            ->extraCellAttributes(['dir' => 'ltr'])
            ->sinceTooltip()
            ->when(
                app()->isLocale('fa'),
                fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                fn (TextColumn $column) => $column->dateTime('Y-m-d H:i'),
            );
    }
}
