<?php

declare(strict_types=1);

namespace Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\Actions\Permissions;

use Filament\Actions\DetachBulkAction as FilamentDetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Collection;
use Misaf\VendraPermission\Models\Permission;
use Misaf\VendraPermission\Models\Role;

final class DetachBulkAction extends FilamentDetachBulkAction
{
    public static function getDefaultName(): ?string
    {
        return 'detach';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->action(function (): void {
            $livewire = $this->getLivewire();

            if (! $livewire instanceof RelationManager) {
                return;
            }

            /** @var Role $ownerRecord */
            $ownerRecord = $livewire->getOwnerRecord();

            $this->process(function (Collection $records) use ($ownerRecord): void {
                foreach ($records as $record) {
                    if (! $record instanceof Permission) {
                        continue;
                    }

                    $ownerRecord->revokePermissionTo($record);
                }
            });

            $this->success();
        });

        $this->deselectRecordsAfterCompletion();
    }
}
