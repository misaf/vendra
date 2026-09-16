<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Infolists\Components;

use Filament\Infolists\Components\TextEntry;

final class NameEntry extends TextEntry
{
    public static function getDefaultName(): string
    {
        return 'name';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('vendra-support::attributes.name'));
    }
}
