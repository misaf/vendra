<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Infolists\Components;

final class UpdatedAtEntry extends TimestampEntry
{
    public static function getDefaultName(): string
    {
        return 'updated_at';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('vendra-support::attributes.updated_at'));
    }
}
