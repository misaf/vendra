<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Infolists\Components;

final class CreatedAtEntry extends TimestampEntry
{
    public static function getDefaultName(): string
    {
        return 'created_at';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('vendra-support::attributes.created_at'));
    }
}
