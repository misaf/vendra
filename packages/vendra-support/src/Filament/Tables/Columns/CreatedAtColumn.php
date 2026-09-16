<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Tables\Columns;

final class CreatedAtColumn extends TimestampColumn
{
    public static function getDefaultName(): string
    {
        return 'created_at';
    }
}
