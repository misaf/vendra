<?php

declare(strict_types=1);

namespace Termehsoft\Permission\Policies;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Termehsoft\Permission\Models\Permission;

final class PermissionObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the Permission "created" event.
     *
     * @param Permission $permission
     */
    public function created(Permission $permission): void {}

    /**
     * Handle the Permission "deleted" event.
     *
     * @param Permission $permission
     */
    public function deleted(Permission $permission): void {}

    /**
     * Handle the Permission "force deleted" event.
     *
     * @param Permission $permission
     */
    public function forceDeleted(Permission $permission): void {}

    /**
     * Handle the Permission "restored" event.
     *
     * @param Permission $permission
     */
    public function restored(Permission $permission): void {}

    /**
     * Handle the Permission "updated" event.
     *
     * @param Permission $permission
     */
    public function updated(Permission $permission): void {}
}
