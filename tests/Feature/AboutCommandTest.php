<?php

declare(strict_types=1);

use Composer\InstalledVersions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

it('reports the installed version of every Vendra package', function (): void {
    Artisan::call('about', ['--json' => true]);

    /** @var array<string, array<string, string|null>> $about */
    $about = json_decode(Artisan::output(), true, flags: JSON_THROW_ON_ERROR);

    $packages = collect(glob(base_path('packages/*/composer.json')) ?: [])
        ->map(fn (string $manifest): string => basename(dirname($manifest)))
        ->reject(fn (string $package): bool => $package === 'vendra-testing')
        ->mapWithKeys(fn (string $package): array => [
            Str::of($package)->replace('-', ' ')->title()->replace(' Api', ' API')->toString() => "misaf/{$package}",
        ]);

    expect($packages)->not->toBeEmpty();

    foreach ($packages as $section => $package) {
        expect($about[Str::snake($section)]['version'])
            ->toBe(InstalledVersions::getPrettyVersion($package));
    }

    expect(Artisan::output())->not->toContain('dev-master');
});
