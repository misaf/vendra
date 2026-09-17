<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Tables\Filters;

use Filament\Tables\Filters\TernaryFilter;

final class IsDefaultFilter extends TernaryFilter
{
    public static function getDefaultName(): string
    {
        return 'is_default';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('vendra-support::attributes.is_default'));
    }
}
