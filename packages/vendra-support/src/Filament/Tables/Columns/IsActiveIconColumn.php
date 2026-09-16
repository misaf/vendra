<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Tables\Columns;

use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;

final class IsActiveIconColumn extends IconColumn
{
    public static function getDefaultName(): string
    {
        return 'active';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('vendra-support::attributes.active'))
            ->boolean()
            ->trueIcon(Heroicon::Bolt);
    }
}
