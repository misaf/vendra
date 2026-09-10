<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

it('stores package resources according to their cluster assignment', function (): void {
    $resourceFiles = collect(File::allFiles(base_path('packages')))
        ->filter(fn (SplFileInfo $file): bool => Str::contains(
            $file->getPathname(),
            DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Filament'.DIRECTORY_SEPARATOR,
        ))
        ->filter(fn (SplFileInfo $file): bool => Str::endsWith($file->getFilename(), 'Resource.php'));

    expect($resourceFiles)->not->toBeEmpty();

    foreach ($resourceFiles as $resourceFile) {
        $path = $resourceFile->getPathname();
        $contents = File::get($path);
        $hasCluster = preg_match('/protected static [^;]+\\$cluster\\s*=/', $contents) === 1;
        $expectedDirectory = $hasCluster
            ? DIRECTORY_SEPARATOR.'Filament'.DIRECTORY_SEPARATOR.'Clusters'.DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR
            : DIRECTORY_SEPARATOR.'Filament'.DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR;

        expect($path)->toContain($expectedDirectory);
    }
});

it('documents the resource location invariant in every resource package', function (): void {
    $resourcePackages = collect(File::directories(base_path('packages')))
        ->filter(fn (string $packagePath): bool => File::isDirectory($packagePath.'/src/Filament'))
        ->filter(fn (string $packagePath): bool => collect(File::allFiles($packagePath.'/src/Filament'))
            ->contains(fn (SplFileInfo $file): bool => Str::endsWith($file->getFilename(), 'Resource.php')));

    expect($resourcePackages)->not->toBeEmpty();

    foreach ($resourcePackages as $packagePath) {
        $guidelinePath = $packagePath.'/resources/boost/guidelines/core.blade.php';
        $skillFiles = File::allFiles($packagePath.'/resources/boost/skills');

        expect($guidelinePath)->toBeFile()
            ->and($skillFiles)->toHaveCount(1);

        foreach ([$guidelinePath, $skillFiles[0]->getPathname()] as $instructionPath) {
            $instructions = File::get($instructionPath);

            expect($instructions)
                ->toContain('src/Filament/Clusters/Resources/')
                ->toContain('src/Filament/Resources/');
        }
    }
});
