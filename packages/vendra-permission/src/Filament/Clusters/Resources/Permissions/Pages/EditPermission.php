<?php

declare(strict_types=1);

namespace Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;
use Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\PermissionResource;
use Misaf\VendraPermission\Models\Permission;
use Misaf\VendraPermission\Models\Role;

final class EditPermission extends EditRecord
{
    protected static string $resource = PermissionResource::class;

    /**
     * @var list<int>
     */
    protected array $selectedRoleIds = [];

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/edit-record.breadcrumb').' '.__('vendra-permission::navigation.permission');
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),

            DeleteAction::make(),
        ];
    }

    protected function afterValidate(): void
    {
        $this->selectedRoleIds = $this->resolveSelectedRoleIds();
    }

    protected function beforeSave(): void
    {
        /** @var Permission $permission */
        $permission = $this->getRecord();

        $resolvedRoleIds = Role::query()
            ->whereKey($this->selectedRoleIds)
            ->pluck('id');

        $permission->syncRoles($resolvedRoleIds);
    }

    /**
     * @return list<int>
     */
    protected function resolveSelectedRoleIds(): array
    {
        $selectedRoles = Arr::get($this->form->getRawState(), 'roles', []);

        if (! is_array($selectedRoles)) {
            $selectedRoles = [];
        }

        return array_values(array_filter(
            array_map(static fn (mixed $roleId): int => (int) $roleId, $selectedRoles),
            static fn (int $roleId): bool => $roleId > 0,
        ));
    }
}
