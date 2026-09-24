<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Tables\Columns;

use Filament\Tables\Columns\IconColumn;

final class IsPrimaryIconColumn extends IconColumn
{
    public static function getDefaultName(): string
    {
        return 'is_primary';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('vendra-support::attributes.is_primary'))
            ->boolean();
    }
}
