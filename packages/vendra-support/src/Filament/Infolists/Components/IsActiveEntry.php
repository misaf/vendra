<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Infolists\Components;

use Filament\Infolists\Components\IconEntry;

final class IsActiveEntry extends IconEntry
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
            ->boolean();
    }
}
