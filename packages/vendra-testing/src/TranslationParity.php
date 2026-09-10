<?php

declare(strict_types=1);

namespace Misaf\VendraTesting;

use Illuminate\Support\Arr;
use PHPUnit\Framework\Assert;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class TranslationParity
{
    public static function assertModuleHasAtLeastTwoLocales(string $moduleName, string $languageDirectory): void
    {
        if (count(self::availableLocales($languageDirectory)) <= 1) {
            Assert::fail(sprintf('Module [%s] must have at least two locales.', $moduleName));
        }

        expect(true)->toBeTrue();
    }

    public static function assertModuleTranslationsAreInSync(string $moduleName, string $languageDirectory): void
    {
        $locales = self::availableLocales($languageDirectory);

        foreach ($locales as $sourceLocale) {
            foreach ($locales as $targetLocale) {
                if ($sourceLocale === $targetLocale) {
                    continue;
                }

                self::assertLocaleHasMatchingFilesAndKeys(
                    languageDirectory: $languageDirectory,
                    moduleName: $moduleName,
                    sourceLocale: $sourceLocale,
                    targetLocale: $targetLocale,
                );
            }
        }

        expect(true)->toBeTrue();
    }

    public static function assertModuleTranslationKeysAreSorted(string $moduleName, string $languageDirectory): void
    {
        $locales = self::availableLocales($languageDirectory);

        foreach ($locales as $locale) {
            $localeFiles = self::translationFilesForLocale($languageDirectory, $locale);

            foreach ($localeFiles as $relativeFilePath) {
                $filePath = self::localeFilePath($languageDirectory, $locale, $relativeFilePath);
                $translations = self::requireTranslationArray(
                    filePath: $filePath,
                    context: sprintf('Module [%s] locale [%s] file [%s]', $moduleName, $locale, $relativeFilePath),
                );

                self::assertSortedTranslationKeys(
                    $translations,
                    sprintf('Module [%s] locale [%s] file [%s]', $moduleName, $locale, $relativeFilePath),
                );
            }
        }

        expect(true)->toBeTrue();
    }

    /**
     * @return list<string>
     */
    private static function availableLocales(string $languageDirectory): array
    {
        $directories = glob($languageDirectory.DIRECTORY_SEPARATOR.'*', GLOB_ONLYDIR);

        if ($directories === false) {
            return [];
        }

        $locales = array_map(basename(...), $directories);
        sort($locales, SORT_STRING);

        return $locales;
    }

    /**
     * @return list<string>
     */
    private static function translationFilesForLocale(string $languageDirectory, string $locale): array
    {
        $localeDirectory = $languageDirectory.DIRECTORY_SEPARATOR.$locale;

        if (! is_dir($localeDirectory)) {
            return [];
        }

        $files = array_map(
            static fn (SplFileInfo $file): string => str_replace('\\', '/', $file->getRelativePathname()),
            iterator_to_array((new Finder)->files()->name('*.php')->in($localeDirectory)),
        );

        sort($files, SORT_STRING);

        return $files;
    }

    /**
     * @return list<string>
     */
    private static function translationKeysForFile(string $filePath, string $context): array
    {
        $translations = self::requireTranslationArray($filePath, $context);
        $keys = array_map(static fn (string $key): string => $key, array_keys(Arr::dot($translations)));
        sort($keys, SORT_STRING);

        return $keys;
    }

    private static function localeFilePath(string $languageDirectory, string $locale, string $relativeFilePath): string
    {
        return $languageDirectory
            .DIRECTORY_SEPARATOR
            .$locale
            .DIRECTORY_SEPARATOR
            .str_replace('/', DIRECTORY_SEPARATOR, $relativeFilePath);
    }

    private static function assertLocaleHasMatchingFilesAndKeys(
        string $languageDirectory,
        string $moduleName,
        string $sourceLocale,
        string $targetLocale,
    ): void {
        $sourceFiles = self::translationFilesForLocale($languageDirectory, $sourceLocale);

        foreach ($sourceFiles as $relativeFilePath) {
            $sourceFilePath = self::localeFilePath($languageDirectory, $sourceLocale, $relativeFilePath);
            $targetFilePath = self::localeFilePath($languageDirectory, $targetLocale, $relativeFilePath);

            if (! file_exists($targetFilePath)) {
                Assert::fail(sprintf(
                    'Module [%s] locale [%s] is missing file [%s] that exists in [%s].',
                    $moduleName,
                    $targetLocale,
                    $relativeFilePath,
                    $sourceLocale,
                ));
            }

            $sourceKeys = self::translationKeysForFile(
                filePath: $sourceFilePath,
                context: sprintf('Module [%s] locale [%s] file [%s]', $moduleName, $sourceLocale, $relativeFilePath),
            );
            $targetKeys = self::translationKeysForFile(
                filePath: $targetFilePath,
                context: sprintf('Module [%s] locale [%s] file [%s]', $moduleName, $targetLocale, $relativeFilePath),
            );

            $missingKeys = array_values(array_diff($sourceKeys, $targetKeys));

            if ($missingKeys !== []) {
                Assert::fail(sprintf(
                    'Module [%s] locale [%s] is missing keys from [%s] in file [%s]: %s',
                    $moduleName,
                    $targetLocale,
                    $sourceLocale,
                    $relativeFilePath,
                    implode(', ', $missingKeys),
                ));
            }
        }
    }

    /**
     * @param  array<array-key, mixed>  $translations
     */
    private static function assertSortedTranslationKeys(array $translations, string $context): void
    {
        if (array_is_list($translations)) {
            return;
        }

        $keys = array_map(static fn (string $key): string => $key, array_keys($translations));
        $sortedKeys = $keys;

        usort($sortedKeys, strcmp(...));

        if ($keys !== $sortedKeys) {
            Assert::fail(sprintf(
                '%s has unsorted keys. Expected order: [%s]. Actual order: [%s].',
                $context,
                implode(', ', $sortedKeys),
                implode(', ', $keys),
            ));
        }

        foreach ($translations as $key => $value) {
            if (is_array($value)) {
                self::assertSortedTranslationKeys($value, sprintf('%s.%s', $context, (string) $key));
            }
        }
    }

    /**
     * @return array<array-key, mixed>
     */
    private static function requireTranslationArray(string $filePath, string $context): array
    {
        $translations = require $filePath;

        self::assertIsTranslationArray($translations, $context);

        return $translations;
    }

    /**
     * @phpstan-assert array<array-key, mixed> $translations
     */
    private static function assertIsTranslationArray(mixed $translations, string $context): void
    {
        if (! is_array($translations)) {
            Assert::fail(sprintf('%s must return an array.', $context));
        }
    }
}
