<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Support;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Symfony\Component\Finder\SplFileInfo;

final class TranslationCatalog
{
    /** @var array<string, array<string, string>> */
    private array $groupOptions = [];

    /** @var array<string, array<string, string>> */
    private array $keyOptions = [];

    /** @var array<string, string>|null */
    private ?array $namespaceOptions = null;

    public function __construct(
        private readonly Filesystem $files,
        private readonly Translator $translator,
    ) {}

    /**
     * @return array<string, string>
     */
    public function namespaceOptions(): array
    {
        if ($this->namespaceOptions !== null) {
            return $this->namespaceOptions;
        }

        $loader = $this->fileLoader();

        if ($loader === null) {
            return $this->namespaceOptions = [];
        }

        $namespaces = array_filter(
            array_keys($loader->namespaces()),
            fn (string $namespace): bool => Str::startsWith($namespace, 'vendra-'),
        );

        return $this->namespaceOptions = $this->options($namespaces);
    }

    /**
     * @return array<string, string>
     */
    public function groupOptions(?string $namespace): array
    {
        $cacheKey = $namespace ?? '*';

        if (array_key_exists($cacheKey, $this->groupOptions)) {
            return $this->groupOptions[$cacheKey];
        }

        $groups = [];

        foreach ($this->translationFiles($namespace) as $file) {
            $groups[] = $this->groupFromFile($file);
        }

        return $this->groupOptions[$cacheKey] = $this->options($groups);
    }

    /**
     * @return array<string, string>
     */
    public function keyOptions(?string $namespace, ?string $group): array
    {
        $cacheKey = ($namespace ?? '*').'::'.($group ?? '*');

        if (array_key_exists($cacheKey, $this->keyOptions)) {
            return $this->keyOptions[$cacheKey];
        }

        if ($group === null || ! array_key_exists($group, $this->groupOptions($namespace))) {
            return $this->keyOptions[$cacheKey] = [];
        }

        $keys = [];

        foreach ($this->translationFiles($namespace) as $file) {
            if ($this->groupFromFile($file) !== $group) {
                continue;
            }

            $translations = $this->files->getRequire($file->getRealPath());

            if (is_array($translations)) {
                $keys = [...$keys, ...array_keys(Arr::dot($translations))];
            }
        }

        return $this->keyOptions[$cacheKey] = $this->options($keys);
    }

    /**
     * @return list<array{
     *     namespace: string,
     *     group: string,
     *     key: string,
     *     text: array<string, string>
     * }>
     */
    public function languageLines(): array
    {
        $languageLines = [];

        foreach (array_keys($this->namespaceOptions()) as $namespace) {
            foreach ($this->translationFiles($namespace) as $file) {
                $locale = $this->localeFromFile($file);
                $group = $this->groupFromFile($file);
                $translations = $this->files->getRequire($file->getRealPath());

                if (! is_array($translations)) {
                    continue;
                }

                foreach (Arr::dot($translations) as $key => $translation) {
                    if (! is_string($key) || ! is_string($translation)) {
                        continue;
                    }

                    $identity = "{$namespace}\0{$group}\0{$key}";
                    $languageLines[$identity] ??= [
                        'namespace' => $namespace,
                        'group' => $group,
                        'key' => $key,
                        'text' => [],
                    ];
                    $languageLines[$identity]['text'][$locale] = $translation;
                }
            }
        }

        ksort($languageLines);

        foreach ($languageLines as &$languageLine) {
            ksort(Arr::get($languageLine, 'text'));
        }
        unset($languageLine);

        return array_values($languageLines);
    }

    private function fileLoader(): ?FileLoader
    {
        $loader = $this->translator->getLoader();

        return $loader instanceof FileLoader ? $loader : null;
    }

    /**
     * @return list<string>
     */
    private function paths(?string $namespace): array
    {
        $loader = $this->fileLoader();

        if ($loader === null) {
            return [];
        }

        if ($namespace === null) {
            return array_values(array_filter(
                $loader->paths(),
                is_string(...),
            ));
        }

        if (! Str::startsWith($namespace, 'vendra-')) {
            return [];
        }

        $path = $loader->namespaces()[$namespace] ?? null;

        return is_string($path) ? [$path] : [];
    }

    /**
     * @return list<SplFileInfo>
     */
    private function translationFiles(?string $namespace): array
    {
        $translationFiles = [];

        foreach ($this->paths($namespace) as $path) {
            if (! $this->files->isDirectory($path)) {
                continue;
            }

            foreach ($this->files->allFiles($path) as $file) {
                if ($file->getExtension() === 'php' && Str::contains($file->getRelativePathname(), DIRECTORY_SEPARATOR)) {
                    $translationFiles[] = $file;
                }
            }
        }

        return $translationFiles;
    }

    private function groupFromFile(SplFileInfo $file): string
    {
        return Str::of($file->getRelativePathname())
            ->after(DIRECTORY_SEPARATOR)
            ->beforeLast('.php')
            ->replace(DIRECTORY_SEPARATOR, '/')
            ->toString();
    }

    private function localeFromFile(SplFileInfo $file): string
    {
        return Str::of($file->getRelativePathname())
            ->before(DIRECTORY_SEPARATOR)
            ->toString();
    }

    /**
     * @param  array<int, string>  $values
     * @return array<string, string>
     */
    private function options(array $values): array
    {
        $values = array_values(array_unique($values));
        sort($values);

        return array_combine($values, $values);
    }
}
