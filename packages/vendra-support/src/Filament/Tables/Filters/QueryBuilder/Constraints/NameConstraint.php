<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Tables\Filters\QueryBuilder\Constraints;

use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;

final class NameConstraint extends TextConstraint
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
