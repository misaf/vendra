<?php

declare(strict_types=1);

namespace App\Observers\Permission;

use App\Models\Permission\Role;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class RoleObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the Role "created" event.
     *
     * @param Role $role
     * @return void
     */
    public function created(Role $role): void {}

    /**
     * Handle the Role "deleted" event.
     *
     * @param Role $role
     * @return void
     */
    public function deleted(Role $role): void
    {
        $role->permissions()->delete();
    }

    /**
     * Handle the Role "force deleted" event.
     *
     * @param Role $role
     * @return void
     */
    public function forceDeleted(Role $role): void {}

    /**
     * Handle the Role "restored" event.
     *
     * @param Role $role
     * @return void
     */
    public function restored(Role $role): void {}

    /**
     * Handle the Role "updated" event.
     *
     * @param Role $role
     * @return void
     */
    public function updated(Role $role): void {}
}
