<?php

declare(strict_types=1);

use Illuminate\Support\Arr;

/**
 * @return array<string, list<string>>
 */
function vendraPackageDependencyGraph(): array
{
    $dependencyGraph = [];

    foreach (glob(base_path('packages/*/composer.json')) ?: [] as $manifestPath) {
        $manifest = json_decode(
            file_get_contents($manifestPath),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        $dependencyGraph[Arr::get($manifest, 'name')] = array_values(array_filter(
            array_keys(Arr::get($manifest, 'require', [])),
            fn (string $package): bool => str_starts_with($package, 'misaf/vendra-'),
        ));
    }

    ksort($dependencyGraph);

    return $dependencyGraph;
}

/**
 * @param  array<string, list<string>>  $dependencyGraph
 * @return list<string>
 */
function reachableVendraPackages(string $package, array $dependencyGraph): array
{
    $reachablePackages = [];
    $pendingPackages = $dependencyGraph[$package] ?? [];

    while ($pendingPackages !== []) {
        $dependency = array_shift($pendingPackages);

        if (isset($reachablePackages[$dependency])) {
            continue;
        }

        $reachablePackages[$dependency] = true;
        array_push($pendingPackages, ...($dependencyGraph[$dependency] ?? []));
    }

    return array_keys($reachablePackages);
}

it('keeps the Vendra package dependency graph complete and acyclic', function (): void {
    $dependencyGraph = vendraPackageDependencyGraph();
    $knownPackages = array_keys($dependencyGraph);
    $unknownDependencies = [];
    $cycles = [];

    foreach ($dependencyGraph as $package => $dependencies) {
        foreach ($dependencies as $dependency) {
            if (! in_array($dependency, $knownPackages, true)) {
                $unknownDependencies[] = "{$package} → {$dependency}";
            }
        }

        if (in_array($package, reachableVendraPackages($package, $dependencyGraph), true)) {
            $cycles[] = $package;
        }
    }

    expect($unknownDependencies)->toBeEmpty()->and($cycles)->toBeEmpty();
});

it('points the tenancy, store, reseller and console layers one way', function (): void {
    $dependencyGraph = vendraPackageDependencyGraph();

    /*
     | The layering the whole platform rests on. `vendra-tenant` is a generic
     | tenancy engine, so it may not know the ecommerce Store; the Store is the
     | concrete tenant and sits below reseller ownership, which is supplied
     | through `Misaf\VendraStore\Contracts\StoreOwnerResolver` rather than a
     | dependency; the console composes both.
     */
    expect(reachableVendraPackages('misaf/vendra-tenant', $dependencyGraph))
        ->not->toContain('misaf/vendra-store')
        ->not->toContain('misaf/vendra-reseller')
        ->not->toContain('misaf/vendra-console')
        ->and(Arr::get($dependencyGraph, 'misaf/vendra-store'))
        ->toContain('misaf/vendra-tenant')
        ->not->toContain('misaf/vendra-reseller')
        ->and(Arr::get($dependencyGraph, 'misaf/vendra-reseller'))
        ->toContain('misaf/vendra-store')
        ->and(Arr::get($dependencyGraph, 'misaf/vendra-console'))
        ->toContain('misaf/vendra-store')
        ->toContain('misaf/vendra-reseller');
});

it('keeps reusable domain packages free of the tenant provider and the store', function (): void {
    $dependencyGraph = vendraPackageDependencyGraph();

    foreach (['misaf/vendra-product', 'misaf/vendra-blog', 'misaf/vendra-cart', 'misaf/vendra-attribute'] as $package) {
        expect(reachableVendraPackages($package, $dependencyGraph))
            ->not->toContain('misaf/vendra-tenant', "[{$package}] must stay tenant-provider agnostic.")
            ->not->toContain('misaf/vendra-store', "[{$package}] must not depend on the Store.");
    }
});

it('imports only Vendra namespaces reachable through declared package dependencies', function (): void {
    $dependencyGraph = vendraPackageDependencyGraph();
    $namespacePackages = [];
    $unreachableImports = [];

    foreach (glob(base_path('packages/*/composer.json')) ?: [] as $manifestPath) {
        $manifest = json_decode(
            file_get_contents($manifestPath),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        foreach (array_keys(Arr::get($manifest, 'autoload.psr-4', [])) as $namespace) {
            $namespacePackages[mb_rtrim($namespace, '\\')] = Arr::get($manifest, 'name');
        }
    }

    foreach (array_keys($dependencyGraph) as $package) {
        $packagePath = base_path('packages/'.mb_substr($package, mb_strlen('misaf/')));
        $sourcePath = "{$packagePath}/src";

        if (! is_dir($sourcePath)) {
            continue;
        }

        $manifest = json_decode(
            file_get_contents("{$packagePath}/composer.json"),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
        $reachablePackages = [
            ...reachableVendraPackages($package, $dependencyGraph),
            ...array_values(array_filter(
                array_keys(Arr::get($manifest, 'suggest', [])),
                fn (string $dependency): bool => str_starts_with($dependency, 'misaf/vendra-'),
            )),
        ];
        $sourceFiles = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourcePath, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($sourceFiles as $sourceFile) {
            if ($sourceFile->getExtension() !== 'php') {
                continue;
            }

            $contents = (string) file_get_contents($sourceFile->getPathname());

            foreach ($namespacePackages as $namespace => $namespacePackage) {
                if ($package === $namespacePackage || in_array($namespacePackage, $reachablePackages, true)) {
                    continue;
                }

                if (preg_match('/^use '.preg_quote($namespace, '/').'\\\\/m', $contents) === 1) {
                    $relativePath = mb_substr($sourceFile->getPathname(), mb_strlen(base_path()) + 1);
                    $unreachableImports[] = "{$relativePath} → {$namespacePackage}";
                }
            }
        }
    }

    expect($unreachableImports)->toBeEmpty();
});
