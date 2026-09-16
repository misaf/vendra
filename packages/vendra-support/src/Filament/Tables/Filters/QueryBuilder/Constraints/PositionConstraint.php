<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Tables\Filters\QueryBuilder\Constraints;

use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;

final class PositionConstraint extends NumberConstraint
{
    public static function getDefaultName(): string
    {
        return 'position';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('vendra-support::attributes.position'));
    }
}
