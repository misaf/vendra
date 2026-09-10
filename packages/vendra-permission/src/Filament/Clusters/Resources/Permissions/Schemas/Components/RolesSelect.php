<?php

declare(strict_types=1);

namespace Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\Schemas\Components;

use Filament\Forms\Components\Select;
use Illuminate\Validation\Rule;
use Misaf\VendraPermission\Models\Role;
use Misaf\VendraSupport\Tenancy\TenantAwareness;
use Misaf\VendraSupport\Tenancy\TenantSchema;

final class RolesSelect
{
    public static function make(string $name = 'roles'): Select
    {
        $existsRule = Rule::exists((new Role)->getTable(), 'id');

        if (TenantAwareness::enabled()) {
            $existsRule->where(TenantSchema::column(), TenantAwareness::currentId());
        }

        return Select::make($name)
            ->columnSpanFull()
            ->label(__('vendra-permission::navigation.role'))
            ->multiple()
            ->native(false)
            ->preload()
            ->rule('array')
            ->nestedRecursiveRules([
                'integer',
                $existsRule,
            ])
            ->searchable();
    }
}
