<?php

declare(strict_types=1);

namespace App\Observers\Permission;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class RoleObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public function created(\App\Models\Permission\Role $role): void {}

    public function deleted(\App\Models\Permission\Role $role): void
    {
        $role->permissions()->delete();
    }

    public function forceDeleted(\App\Models\Permission\Role $role): void {}

    public function restored(\App\Models\Permission\Role $role): void {}

    public function updated(\App\Models\Permission\Role $role): void {}
}
