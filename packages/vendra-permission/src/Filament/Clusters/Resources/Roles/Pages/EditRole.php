<?php

declare(strict_types=1);

namespace Misaf\VendraPermission\Filament\Clusters\Resources\Roles\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Misaf\VendraPermission\Actions\ChangeRoleGuardAction;
use Misaf\VendraPermission\Filament\Clusters\Resources\Roles\RoleResource;
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
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $guardName = Arr::pull($data, 'guard_name');

        return DB::transaction(function () use ($record, $data, $guardName): Role {
            $record->update($data);

            if (is_string($guardName)) {
                return resolve(ChangeRoleGuardAction::class)->execute($record, $guardName);
            }

            return $record;
        });
    }
}
