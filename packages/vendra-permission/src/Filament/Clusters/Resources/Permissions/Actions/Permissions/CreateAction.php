<?php

declare(strict_types=1);

namespace Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\Actions\Permissions;

use Filament\Actions\CreateAction as FilamentCreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Misaf\VendraPermission\Models\Permission;
use Misaf\VendraPermission\Models\Role;

final class CreateAction extends FilamentCreateAction
{
    public static function getDefaultName(): ?string
    {
        return 'create';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->mutateDataUsing(function (array $data): array {
            $livewire = $this->getLivewire();

            if (! $livewire instanceof RelationManager) {
                return $data;
            }

            /** @var Role $ownerRecord */
            $ownerRecord = $livewire->getOwnerRecord();

            $data['guard_name'] = $ownerRecord->guard_name;

            return $data;
        });

        $this->after(function (Permission $record): void {
            $livewire = $this->getLivewire();

            if (! $livewire instanceof RelationManager) {
                return;
            }

            /** @var Role $ownerRecord */
            $ownerRecord = $livewire->getOwnerRecord();

            $ownerRecord->givePermissionTo($record);
        });
    }
}
