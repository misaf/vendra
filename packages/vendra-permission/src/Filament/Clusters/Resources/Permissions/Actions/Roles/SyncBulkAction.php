<?php

declare(strict_types=1);

namespace Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\Actions\Roles;

use Filament\Actions\BulkAction;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\Actions\Roles\Concerns\ResolvesSelected;
use Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\Schemas\Components\RolesSelect;
use Misaf\VendraPermission\Models\Permission;
use Misaf\VendraPermission\Models\Role;

final class SyncBulkAction extends BulkAction
{
    use CanCustomizeProcess;
    use ResolvesSelected;

    public static function getDefaultName(): ?string
    {
        return 'sync';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->successNotificationTitle(__('filament-actions::edit.single.notifications.saved.title'));

        $this->color('primary');

        $this->icon(Heroicon::OutlinedLink);

        $this->requiresConfirmation();

        $this->modalIcon(Heroicon::OutlinedLink);

        $this->schema([
            RolesSelect::make('roles')
                ->options(
                    Role::query()
                        ->orderBy('name')
                        ->orderBy('guard_name')
                        ->get(['id', 'name', 'guard_name'])
                        ->mapWithKeys(static fn(Role $role): array => [$role->id => "{$role->name} ({$role->guard_name})"])
                        ->all()
                )
                ->required(),
        ]);

        $this->action(
            /**
             * @param  array{roles?: mixed}  $data
             */
            function (array $data): void {
                $rolesByGuard = $this->resolveRoleIdsByGuardFromPayload($data);

                $this->process(static function (Collection $records) use ($rolesByGuard): void {
                    foreach ($records as $record) {
                        if (! $record instanceof Permission) {
                            continue;
                        }

                        $roleIdsForGuard = $rolesByGuard[$record->guard_name] ?? null;

                        if ($roleIdsForGuard === null) {
                            continue;
                        }

                        $record->syncRoles($roleIdsForGuard);
                    }
                });

                $this->success();
            }
        );

        $this->deselectRecordsAfterCompletion();
    }
}
