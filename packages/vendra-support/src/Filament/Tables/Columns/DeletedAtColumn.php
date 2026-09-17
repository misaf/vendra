<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Tables\Columns;

final class DeletedAtColumn extends TimestampColumn
{
    public static function getDefaultName(): string
    {
        return 'deleted_at';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->placeholder('—');
    }
}
