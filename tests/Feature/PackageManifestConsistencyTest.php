<?php

declare(strict_types=1);

/**
 * Determine whether a package's own source imports the tenant provider.
 */
function packageSourceBindsTenantProvider(string $packagePath): bool
{
    foreach (glob("{$packagePath}/src/{,*/,*/*/,*/*/*/,*/*/*/*/,*/*/*/*/*/}*.php", GLOB_BRACE) ?: [] as $sourceFile) {
        if (preg_match('/^use Misaf\\\\VendraTenant\\\\/m', (string) file_get_contents($sourceFile)) === 1) {
            return true;
        }
    }

    return false;
}

it('keeps package manifest metadata consistent', function (): void {
    $manifestPaths = glob(base_path('packages/*/composer.json')) ?: [];
    $rootManifest = json_decode(
        file_get_contents(base_path('composer.json')),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($manifestPaths)->not->toBeEmpty()
        ->and($rootManifest['require']['php'] ?? null)->toBe('^8.4');

    foreach ($manifestPaths as $manifestPath) {
        $package = basename(dirname($manifestPath));
        $repository = "https://github.com/misaf/{$package}";
        $manifest = json_decode(
            file_get_contents($manifestPath),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        expect($manifest)
            ->name->toBe("misaf/{$package}")
            ->type->toBe('vendra-module')
            ->license->toBe('MIT')
            ->keywords->not->toBeEmpty()
            ->authors->toBe([[
                'name' => 'Ehsan Mahmoodi',
                'email' => 'misaf.1990@gmail.com',
                'role' => 'Developer',
            ]])
            ->homepage->toBe($repository)
            ->support->toBe([
                'issues' => "{$repository}/issues",
                'source' => $repository,
            ])
            ->and($manifest)
            ->not->toHaveKey('require-dev')
            ->not->toHaveKey('scripts')
            ->and($manifest['require']['php'] ?? null)
            ->toBe('^8.4');
    }
});

it('keeps package-only namespaces out of production autoloading', function (): void {
    $rootManifest = json_decode(
        file_get_contents(base_path('composer.json')),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect(array_values($rootManifest['autoload']['psr-4']))
        ->each->not->toStartWith('packages/');

    foreach (glob(base_path('packages/*/composer.json')) ?: [] as $manifestPath) {
        $manifest = json_decode(
            file_get_contents($manifestPath),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
        $productionPaths = array_values($manifest['autoload']['psr-4'] ?? []);
        $developmentPaths = array_values($manifest['autoload-dev']['psr-4'] ?? []);

        expect($productionPaths)
            ->not->toContain('database/factories/')
            ->not->toContain('tests/')
            ->and($developmentPaths)
            ->not->toContain('tests/');

        if (is_dir(dirname($manifestPath).'/database/factories')) {
            expect($developmentPaths)->toContain('database/factories/');
        } else {
            expect($manifest)->not->toHaveKey('autoload-dev');
        }
    }
});

it('keeps package test suites tenant-provider agnostic', function (): void {
    $offending = [];

    foreach (glob(base_path('packages/*/composer.json')) ?: [] as $manifestPath) {
        $packagePath = dirname($manifestPath);

        if (in_array(basename($packagePath), ['vendra-tenant'], true)) {
            continue;
        }

        // A package whose own source binds to the tenant provider gains nothing
        // from an agnostic test suite: its tests cannot be looser about the
        // provider than the code they cover. The rule exists to keep the
        // remaining packages swappable, so it only applies to those.
        if (packageSourceBindsTenantProvider($packagePath)) {
            continue;
        }

        foreach (glob("{$packagePath}/tests/{,*/,*/*/}*.php", GLOB_BRACE) ?: [] as $testFile) {
            $importsTenant = preg_match(
                '/^use Misaf\\\\VendraTenant\\\\/m',
                (string) file_get_contents($testFile),
            ) === 1;

            if ($importsTenant) {
                $offending[] = basename($packagePath).'/'.basename($testFile);
            }
        }
    }

    expect($offending)->toBe([]);
});

it('provides every Vendra module imported by package tests through the host', function (): void {
    $manifestPaths = glob(base_path('packages/*/composer.json')) ?: [];
    $namespacesByPackage = [];
    $rootManifest = json_decode(
        file_get_contents(base_path('composer.json')),
        true,
        flags: JSON_THROW_ON_ERROR,
    );
    $hostDependencies = ($rootManifest['require'] ?? []) + ($rootManifest['require-dev'] ?? []);

    foreach ($manifestPaths as $manifestPath) {
        $manifest = json_decode(
            file_get_contents($manifestPath),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        foreach (array_keys($manifest['autoload']['psr-4'] ?? []) as $namespace) {
            $namespacesByPackage[$manifest['name']][] = mb_rtrim($namespace, '\\');
        }
    }

    $missingFromHost = [];

    foreach ($manifestPaths as $manifestPath) {
        $packagePath = dirname($manifestPath);
        $manifest = json_decode(file_get_contents($manifestPath), true, flags: JSON_THROW_ON_ERROR);

        foreach (glob("{$packagePath}/tests/{,*/,*/*/}*.php", GLOB_BRACE) ?: [] as $testFile) {
            $contents = (string) file_get_contents($testFile);

            foreach ($namespacesByPackage as $package => $namespaces) {
                if ($package === $manifest['name'] || array_key_exists($package, $hostDependencies)) {
                    continue;
                }

                foreach ($namespaces as $namespace) {
                    $importsNamespace = preg_match(
                        '/^use '.preg_quote($namespace, '/').'\\\\/m',
                        $contents,
                    ) === 1;

                    if ($importsNamespace) {
                        $missingFromHost[basename($packagePath).' → '.$package] = true;
                    }
                }
            }
        }
    }

    expect(array_keys($missingFromHost))->toBe([]);
});

it('centralizes package test namespaces and mirrors factory namespaces in root autoload-dev', function (): void {
    $rootManifest = json_decode(
        file_get_contents(base_path('composer.json')),
        true,
        flags: JSON_THROW_ON_ERROR,
    );
    $rootAutoloadDev = $rootManifest['autoload-dev']['psr-4'] ?? [];
    $missing = [];

    foreach (glob(base_path('packages/*/composer.json')) ?: [] as $manifestPath) {
        $package = basename(dirname($manifestPath));
        $manifest = json_decode(
            file_get_contents($manifestPath),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
        $packageNamespace = array_search('src/', $manifest['autoload']['psr-4'] ?? [], true);

        if (! is_string($packageNamespace)) {
            $missing[] = "{$package} source namespace";

            continue;
        }

        $testNamespace = $packageNamespace.'Tests\\';
        $expectedTestPath = "packages/{$package}/tests/";

        if (($rootAutoloadDev[$testNamespace] ?? null) !== $expectedTestPath) {
            $missing[] = "{$testNamespace} => {$expectedTestPath}";
        }

        foreach ($manifest['autoload-dev']['psr-4'] ?? [] as $namespace => $path) {
            $expectedPath = "packages/{$package}/{$path}";

            if (($rootAutoloadDev[$namespace] ?? null) !== $expectedPath) {
                $missing[] = "{$namespace} => {$expectedPath}";
            }
        }
    }

    foreach ($rootAutoloadDev as $namespace => $path) {
        if (str_starts_with($path, 'packages/') && ! is_dir(base_path($path))) {
            $missing[] = "{$namespace} => missing {$path}";
        }
    }

    expect($missing)->toBe([]);
});

it('requires vendra-multimedia in every package whose src uses the Spatie media surface', function (): void {
    $undeclared = [];

    foreach (glob(base_path('packages/*/composer.json')) ?: [] as $manifestPath) {
        $packagePath = dirname($manifestPath);
        $sourcePath = "{$packagePath}/src";

        if (basename($packagePath) === 'vendra-multimedia' || ! is_dir($sourcePath)) {
            continue;
        }

        $usesSpatieMediaSurface = false;
        $sourceFiles = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourcePath, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($sourceFiles as $sourceFile) {
            if ($sourceFile->getExtension() !== 'php') {
                continue;
            }

            $matchesSurface = preg_match(
                '/^use (?:Spatie\\\\MediaLibrary\\\\|Filament\\\\[\w\\\\]+\\\\SpatieMediaLibrary)/m',
                (string) file_get_contents($sourceFile->getPathname()),
            );

            if ($matchesSurface === 1) {
                $usesSpatieMediaSurface = true;

                break;
            }
        }

        if (! $usesSpatieMediaSurface) {
            continue;
        }

        $manifest = json_decode(
            file_get_contents($manifestPath),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        if (! array_key_exists('misaf/vendra-multimedia', $manifest['require'] ?? [])) {
            $undeclared[] = basename($packagePath);
        }
    }

    expect($undeclared)->toBe([]);
});

it('does not keep package-level Composer lock files', function (): void {
    expect(array_values(glob(base_path('packages/*/composer.lock')) ?: []))->toBe([]);
});
