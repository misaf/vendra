<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Tables\Columns;

final class UpdatedAtColumn extends TimestampColumn
{
    public static function getDefaultName(): string
    {
        return 'updated_at';
    }
}
