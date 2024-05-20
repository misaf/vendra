<?php

declare(strict_types=1);

namespace App\Observers\Permission;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class PermissionObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public function created(\App\Models\Permission\Permission $permission): void {}

    public function deleted(\App\Models\Permission\Permission $permission): void {}

    public function forceDeleted(\App\Models\Permission\Permission $permission): void {}

    public function restored(\App\Models\Permission\Permission $permission): void {}

    public function updated(\App\Models\Permission\Permission $permission): void {}
}
