<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Tables\Columns;

use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ToggleColumn;

final class ActiveToggleColumn extends ToggleColumn
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
            ->onIcon(Heroicon::Bolt);
    }
}
