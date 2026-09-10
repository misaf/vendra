<?php

declare(strict_types=1);

namespace Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\Actions\Permissions;

use Filament\Actions\AttachAction as FilamentAttachAction;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Builder;
use Misaf\VendraPermission\Models\Role;

final class AttachAction extends FilamentAttachAction
{
    public static function getDefaultName(): ?string
    {
        return 'attach';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->multiple();

        $this->preloadRecordSelect();

        $this->recordSelectOptionsQuery(function (Builder $query): Builder {
            $livewire = $this->getLivewire();

            if (! $livewire instanceof RelationManager) {
                return $query;
            }

            /** @var Role $ownerRecord */
            $ownerRecord = $livewire->getOwnerRecord();

            return $query->where('guard_name', $ownerRecord->guard_name);
        });
    }
}
