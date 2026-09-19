<?php

declare(strict_types=1);

namespace Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\Actions\Roles;

use Filament\Actions\BulkAction;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\Schemas\Components\RolesSelect;
use Misaf\VendraPermission\Models\Permission;
use Misaf\VendraPermission\Models\Role;

final class SyncBulkAction extends BulkAction
{
    use CanCustomizeProcess;

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
                ->options(static fn (): array => Role::query()
                    ->orderBy('name')
                    ->orderBy('guard_name')
                    ->get(['id', 'name', 'guard_name'])
                    ->mapWithKeys(static fn (Role $role): array => [$role->id => "{$role->name} ({$role->guard_name})"])
                    ->all())
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

    /**
     * @param  array{roles?: mixed}  $data
     * @return array<string, list<ModelKey>>
     */
    private function resolveRoleIdsByGuardFromPayload(array $data): array
    {
        $rawRoleIds = Arr::get($data, 'roles', null);

        throw_unless(is_array($rawRoleIds), InvalidArgumentException::class, 'Invalid roles provided.');

        /** @var list<ModelKey> $roleIds */
        $roleIds = [];

        foreach ($rawRoleIds as $rawRoleId) {
            if (is_int($rawRoleId) || is_string($rawRoleId)) {
                $roleIds[] = $rawRoleId;
            }
        }

        $roleIds = array_values(array_unique($roleIds, SORT_REGULAR));

        throw_if($roleIds === [], InvalidArgumentException::class, 'Invalid roles provided.');

        /** @var Collection<int, Role> $roles */
        $roles = Role::query()
            ->whereKey($roleIds)
            ->get(['id', 'guard_name']);

        throw_if($roles->count() !== count($roleIds), InvalidArgumentException::class, 'Invalid roles provided.');

        /** @var array<string, list<ModelKey>> $resolvedRoleIdsByGuard */
        $resolvedRoleIdsByGuard = $roles
            ->groupBy('guard_name')
            ->map(
                /**
                 * @param  Collection<int, Role>  $rolesInGuard
                 * @return list<ModelKey>
                 */
                static fn (Collection $rolesInGuard): array => $rolesInGuard
                    ->map(static fn (Role $role): int => $role->id)
                    ->values()
                    ->all()
            )
            ->all();

        return $resolvedRoleIdsByGuard;
    }
}
