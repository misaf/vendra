<?php

declare(strict_types=1);

namespace Misaf\VendraPermission\Filament\Clusters\Resources\Roles\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraPermission\Filament\Clusters\Resources\Roles\RoleResource;
use Misaf\VendraPermission\Models\Permission;
use Misaf\VendraPermission\Models\Role;

final class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/edit-record.breadcrumb').' '.__('vendra-permission::navigation.role');
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),

            DeleteAction::make(),
        ];
    }

    /**
     * @param  Role  $record
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $previousGuardName = $record->guard_name;

        $record->update($data);

        $nextGuardName = $record->guard_name;

        if ($previousGuardName === $nextGuardName) {
            return $record;
        }

        /** @var Collection<int, Permission> $permissions */
        $permissions = $record->permissions()->get();

        $permissions->each(static function (Permission $permission) use ($nextGuardName): void {
            $permission->update([
                'guard_name' => $nextGuardName,
            ]);
        });

        return $record;
    }
}
