<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

it('documents the Vendra transitive API policy in every package', function (): void {
    $policyStatements = [
        'Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.',
        'Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.',
        'Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.',
    ];

    $packagePaths = collect(File::directories(base_path('packages')))
        ->filter(fn (string $packagePath): bool => File::exists($packagePath.'/composer.json'));

    expect($packagePaths)->not->toBeEmpty();

    foreach ($packagePaths as $packagePath) {
        $guidelinePath = $packagePath.'/resources/boost/guidelines/core.blade.php';
        $skillFiles = File::allFiles($packagePath.'/resources/boost/skills');

        expect($guidelinePath)->toBeFile()
            ->and($skillFiles)->toHaveCount(1);

        foreach ([$guidelinePath, Arr::get($skillFiles, 0)->getPathname()] as $instructionPath) {
            $contents = File::get($instructionPath);

            expect(mb_substr_count($contents, 'Vendra Transitive API Policy'))->toBe(1);

            foreach ($policyStatements as $policyStatement) {
                expect(mb_substr_count($contents, $policyStatement))->toBe(1);
            }
        }
    }
});
