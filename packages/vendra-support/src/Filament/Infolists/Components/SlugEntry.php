<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Infolists\Components;

use Filament\Infolists\Components\TextEntry;

final class SlugEntry extends TextEntry
{
    public static function getDefaultName(): string
    {
        return 'slug';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('vendra-support::attributes.slug'));
    }
}
