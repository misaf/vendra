<?php

declare(strict_types=1);

namespace App\Tenancy\SwitchTasks;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Str;
use Spatie\Multitenancy\Models\Tenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

final class ClearFacadeInstancesTask implements SwitchTenantTask
{
    public function forgetCurrent(): void
    {
        $this->clearFacadeInstancesInTheAppNamespace();
    }

    public function makeCurrent(Tenant $tenant): void {}

    private function clearFacadeInstancesInTheAppNamespace(): void
    {
        // Discovers all facades in the App namespace and clears their resolved instance:
        collect(get_declared_classes())
            ->filter(fn($className) => is_subclass_of($className, Facade::class))
            ->filter(fn($className) => Str::startsWith($className, 'App') || Str::startsWith($className, 'Facades\\App'))
            ->each(fn($className) => $className::clearResolvedInstance(
                $className::getFacadeAccessor(),
            ));
    }
}
