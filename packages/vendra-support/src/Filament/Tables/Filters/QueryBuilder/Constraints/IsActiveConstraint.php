<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Tables\Filters\QueryBuilder\Constraints;

use Filament\Tables\Filters\QueryBuilder\Constraints\BooleanConstraint;

final class IsActiveConstraint extends BooleanConstraint
{
    public static function getDefaultName(): string
    {
        return 'active';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('vendra-support::attributes.active'));
    }
}
