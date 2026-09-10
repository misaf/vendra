<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;

it('keeps created and updated timestamps visible in every Filament table', function (): void {
    $violations = collect([
        app_path('Filament'),
        base_path('packages'),
    ])
        ->filter(fn (string $directory): bool => File::isDirectory($directory))
        ->flatMap(fn (string $directory): array => File::allFiles($directory))
        ->filter(fn (SplFileInfo $file): bool => $file->getExtension() === 'php'
            && str_contains($file->getPathname(), DIRECTORY_SEPARATOR.'Filament'.DIRECTORY_SEPARATOR))
        ->filter(function (SplFileInfo $file): bool {
            $timestampColumn = null;

            foreach (preg_split('/\R/', $file->getContents()) ?: [] as $line) {
                if (preg_match('/([A-Za-z]+Column)::make\(([\'\"])([^\'\"]+)\2\)/', $line, $matches) === 1) {
                    $timestampColumn = $matches[1] === 'TextColumn'
                        && in_array($matches[3], ['created_at', 'updated_at'], true)
                            ? $matches[3]
                            : null;
                }

                if ($timestampColumn !== null
                    && str_contains($line, '->toggleable(isToggledHiddenByDefault: true)')) {
                    return true;
                }
            }

            return false;
        })
        ->map(fn (SplFileInfo $file): string => $file->getRelativePathname())
        ->values()
        ->all();

    expect($violations)->toBeEmpty();
});
