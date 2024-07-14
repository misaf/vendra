<?php

declare(strict_types=1);

namespace App\Models\Tenant\Services;

use Illuminate\Support\Facades\Facade;

final class ClearFacadeInstancesTask
{
    public static function clearFacadeInstancesInTheAppNamespace(): void
    {
        // Discovers all facades in the App namespace and clears their resolved instance:
        collect(get_declared_classes())
            ->filter(fn($className) => is_subclass_of($className, Facade::class))
            ->filter(fn($className) => str()->startsWith($className, 'App') || str()->startsWith($className, 'Facades\\App'))
            ->each(fn($className) => $className::clearResolvedInstance(
                $className::getFacadeAccessor(),
            ));
    }
}
