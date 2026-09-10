<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Misaf\VendraSupport\Capabilities\HasOptionalTags;

it('documents the tag-agnostic relationship contract in every tag-consuming package', function (): void {
    $packagePaths = collect(File::directories(base_path('packages')))
        ->filter(fn (string $packagePath): bool => collect(File::allFiles($packagePath.'/src'))
            ->contains(fn (SplFileInfo $file): bool => Str::contains(
                File::get($file->getPathname()),
                'use Misaf\VendraSupport\Capabilities\HasOptionalTags;',
            )));

    expect($packagePaths)->not->toBeEmpty();

    foreach ($packagePaths as $packagePath) {
        $guidelinePath = $packagePath.'/resources/boost/guidelines/core.blade.php';
        $skillFiles = File::allFiles($packagePath.'/resources/boost/skills');

        expect($guidelinePath)->toBeFile()
            ->and($skillFiles)->toHaveCount(1);

        foreach ([$guidelinePath, Arr::get($skillFiles, 0)->getPathname()] as $instructionPath) {
            expect(File::get($instructionPath))
                ->toContain(HasOptionalTags::class)
                ->toContain('single source of their `tags()` relationship and pivot metadata')
                ->toContain('stable package-owned tag type')
                ->toContain('TagIntegration')
                ->toContain('never import the concrete Vendra Tagger model/provider')
                ->toContain('never import the concrete Vendra Tagger model/provider or define the relationship through Spatie `HasTags`')
                ->toContain('Composer `suggest`');
        }
    }
});
