<?php

declare(strict_types=1);

namespace Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\Actions\Permissions;

use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Misaf\VendraPermission\Models\Permission;
use Misaf\VendraPermission\Models\Role;

final class DetachAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'detach';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->requiresConfirmation();

        $this->action(function (Permission $record): void {
            $livewire = $this->getLivewire();

            if (! $livewire instanceof RelationManager) {
                return;
            }

            /** @var Role $ownerRecord */
            $ownerRecord = $livewire->getOwnerRecord();

            $ownerRecord->revokePermissionTo($record);
        });
    }
}
