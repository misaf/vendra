<?php

declare(strict_types=1);

namespace Termehsoft\Permission\Policies;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Termehsoft\Permission\Models\Role;

final class RoleObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the Role "created" event.
     *
     * @param Role $role
     */
    public function created(Role $role): void {}

    /**
     * Handle the Role "deleted" event.
     *
     * @param Role $role
     */
    public function deleted(Role $role): void
    {
        $role->permissions()->delete();
    }

    /**
     * Handle the Role "force deleted" event.
     *
     * @param Role $role
     */
    public function forceDeleted(Role $role): void {}

    /**
     * Handle the Role "restored" event.
     *
     * @param Role $role
     */
    public function restored(Role $role): void {}

    /**
     * Handle the Role "updated" event.
     *
     * @param Role $role
     */
    public function updated(Role $role): void {}
}
