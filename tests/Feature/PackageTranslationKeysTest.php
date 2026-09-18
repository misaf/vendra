<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Lang;

it('resolves every literal package translation key used in package code', function (): void {
    $missingKeys = [];

    foreach (glob(base_path('packages/*'), GLOB_ONLYDIR) ?: [] as $packagePath) {
        foreach (['src', 'database'] as $sourceDirectory) {
            if (! is_dir("{$packagePath}/{$sourceDirectory}")) {
                continue;
            }

            $sourceFiles = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator("{$packagePath}/{$sourceDirectory}", FilesystemIterator::SKIP_DOTS),
            );

            foreach ($sourceFiles as $sourceFile) {
                if ($sourceFile->getExtension() !== 'php') {
                    continue;
                }

                preg_match_all(
                    "/\\b(?:__|trans|trans_choice)\\(\\s*'(vendra-[a-z-]+::[\\w.-]+)'\\s*[,)]/",
                    (string) file_get_contents($sourceFile->getPathname()),
                    $matches,
                );

                foreach (Arr::get($matches, 1) as $key) {
                    if (! Lang::has($key, 'en', false)) {
                        $relativePath = mb_substr($sourceFile->getPathname(), mb_strlen(base_path()) + 1);
                        $missingKeys[] = "{$relativePath} → {$key}";
                    }
                }
            }
        }
    }

    expect($missingKeys)->toBeEmpty();
});
