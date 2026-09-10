<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;

it('keeps every deferred Filament badge lazy across app and package source', function (): void {
    $packageSourceFiles = collect(File::allFiles(base_path('packages')))
        ->filter(static fn (SplFileInfo $file): bool => str_contains(
            $file->getPathname(),
            DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR,
        ));

    $sourceFiles = collect(File::allFiles(app_path()))
        ->merge($packageSourceFiles)
        ->filter(static fn (SplFileInfo $file): bool => $file->getExtension() === 'php');

    $deferredBadgeCount = 0;
    $relationManagerBadgeCount = 0;

    foreach ($sourceFiles as $file) {
        $source = $file->getContents();
        $relativePath = str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getPathname());
        $searchOffset = 0;

        while (false !== ($deferOffset = mb_strpos($source, '->deferBadge(', $searchOffset))) {
            $sourceBeforeDeferral = mb_substr($source, 0, $deferOffset);
            $badgeOffset = mb_strrpos($sourceBeforeDeferral, '->badge(');

            if ($badgeOffset === false) {
                throw new RuntimeException("Deferred badge in [{$relativePath}] has no badge value.");
            }

            $badgeCall = mb_substr($sourceBeforeDeferral, $badgeOffset);

            expect($badgeCall, $relativePath)
                ->toMatch('/\A->badge\(\s*(?:static\s+)?(?:fn|function)\s*\(/');

            $deferredBadgeCount++;
            $searchOffset = $deferOffset + mb_strlen('->deferBadge(');
        }

        if (! str_contains($source, 'public static function getBadge(')) {
            continue;
        }

        $hasDeferredProperty = str_contains($source, 'protected static bool $isBadgeDeferred = true;');
        $hasDeferredMethod = preg_match('/public static function isBadgeDeferred\s*\(/', $source) === 1;

        expect($hasDeferredProperty || $hasDeferredMethod, $relativePath)->toBeTrue();

        $relationManagerBadgeCount++;
    }

    expect($deferredBadgeCount)->toBeGreaterThan(0)
        ->and($relationManagerBadgeCount)->toBeGreaterThan(0);
});
