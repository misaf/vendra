<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Tables\Filters;

use Filament\Tables\Filters\TernaryFilter;

final class IsActiveFilter extends TernaryFilter
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
            ->trueLabel(__('vendra-support::attributes.active'))
            ->falseLabel(__('vendra-support::attributes.inactive'));
    }
}
