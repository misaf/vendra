<?php

declare(strict_types=1);

namespace Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\Actions\Permissions;

use Filament\Actions\DeleteBulkAction as FilamentDeleteBulkAction;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Misaf\VendraPermission\Models\Permission;

final class DeleteBulkAction extends FilamentDeleteBulkAction
{
    public static function getDefaultName(): ?string
    {
        return 'delete';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->before(function (EloquentCollection|Collection|LazyCollection $records): void {
            foreach ($records as $record) {
                if (! $record instanceof Permission) {
                    continue;
                }

                $record->syncRoles([]);
            }
        });
    }
}
