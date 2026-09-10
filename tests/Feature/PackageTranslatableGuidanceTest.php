<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

it('documents translatable persistence in every package guideline and skill', function (): void {
    $packagePaths = collect(File::directories(base_path('packages')))
        ->filter(fn (string $packagePath): bool => File::isDirectory($packagePath.'/resources/boost'));

    expect($packagePaths)->not->toBeEmpty();

    foreach ($packagePaths as $packagePath) {
        $guidelinePath = $packagePath.'/resources/boost/guidelines/core.blade.php';
        $skillFiles = File::allFiles($packagePath.'/resources/boost/skills');

        expect($guidelinePath)->toBeFile()
            ->and($skillFiles)->toHaveCount(1);

        foreach ([$guidelinePath, $skillFiles[0]->getPathname()] as $instructionPath) {
            expect(File::get($instructionPath))
                ->toContain('Translatable Persistence')
                ->toContain('explicit domain choice')
                ->toContain('listed in a model\'s `$translatable` array')
                ->toContain('JSON database column')
                ->toContain('not listed in `$translatable`')
                ->toContain('scalar database type')
                ->toContain('must not use Spatie Translatable');
        }
    }
});
