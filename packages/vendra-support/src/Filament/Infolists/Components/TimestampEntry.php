<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Infolists\Components;

use Filament\Infolists\Components\TextEntry;

abstract class TimestampEntry extends TextEntry
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->when(
            app()->isLocale('fa'),
            fn (TextEntry $entry) => $entry->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
            fn (TextEntry $entry) => $entry->dateTime('Y-m-d H:i'),
        );
    }
}
