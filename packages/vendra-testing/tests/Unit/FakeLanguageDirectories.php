<?php

declare(strict_types=1);

namespace Misaf\VendraTesting\Tests\Unit;

use Illuminate\Filesystem\Filesystem;

final class FakeLanguageDirectories
{
    /** @var list<string> */
    private static array $directories = [];

    /**
     * @param  array<string, array<string, string>>  $localeFiles  locale => relative file path => PHP source
     */
    public static function create(array $localeFiles): string
    {
        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'vendra-testing-parity-'.bin2hex(random_bytes(8));
        self::$directories[] = $directory;

        foreach ($localeFiles as $locale => $files) {
            (new Filesystem)->ensureDirectoryExists($directory.DIRECTORY_SEPARATOR.$locale);

            foreach ($files as $relativePath => $source) {
                $filePath = $directory.DIRECTORY_SEPARATOR.$locale.DIRECTORY_SEPARATOR.$relativePath;
                (new Filesystem)->ensureDirectoryExists(dirname($filePath));
                file_put_contents($filePath, $source);
            }
        }

        return $directory;
    }

    public static function cleanup(): void
    {
        foreach (self::$directories as $directory) {
            (new Filesystem)->deleteDirectory($directory);
        }

        self::$directories = [];
    }
}
