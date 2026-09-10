<?php

declare(strict_types=1);

use Misaf\VendraTesting\Tests\Unit\FakeLanguageDirectories;
use Misaf\VendraTesting\TranslationParity;
use PHPUnit\Framework\AssertionFailedError;

/**
 * @param  array<string, array<string, string>>  $localeFiles  locale => relative file path => PHP source
 */
function fakeLanguageDirectory(array $localeFiles): string
{
    return FakeLanguageDirectories::create($localeFiles);
}

afterEach(function (): void {
    FakeLanguageDirectories::cleanup();
});

it('passes when a module has at least two locales', function (): void {
    $directory = fakeLanguageDirectory([
        'en' => ['module.php' => "<?php return ['title' => 'Title'];"],
        'fa' => ['module.php' => "<?php return ['title' => 'عنوان'];"],
    ]);

    TranslationParity::assertModuleHasAtLeastTwoLocales('demo', $directory);
});

it('fails when a module has a single locale', function (): void {
    $directory = fakeLanguageDirectory([
        'en' => ['module.php' => "<?php return ['title' => 'Title'];"],
    ]);

    expect(fn () => TranslationParity::assertModuleHasAtLeastTwoLocales('demo', $directory))
        ->toThrow(AssertionFailedError::class, 'Module [demo] must have at least two locales.');
});

it('passes when every locale has matching files and keys', function (): void {
    $directory = fakeLanguageDirectory([
        'en' => ['module.php' => "<?php return ['fields' => ['name' => 'Name'], 'title' => 'Title'];"],
        'fa' => ['module.php' => "<?php return ['fields' => ['name' => 'نام'], 'title' => 'عنوان'];"],
    ]);

    TranslationParity::assertModuleTranslationsAreInSync('demo', $directory);
});

it('fails when a locale is missing a translation file', function (): void {
    $directory = fakeLanguageDirectory([
        'en' => [
            'module.php' => "<?php return ['title' => 'Title'];",
            'extra.php' => "<?php return ['label' => 'Label'];",
        ],
        'fa' => ['module.php' => "<?php return ['title' => 'عنوان'];"],
    ]);

    expect(fn () => TranslationParity::assertModuleTranslationsAreInSync('demo', $directory))
        ->toThrow(AssertionFailedError::class, 'Module [demo] locale [fa] is missing file [extra.php] that exists in [en].');
});

it('fails when a locale is missing nested translation keys', function (): void {
    $directory = fakeLanguageDirectory([
        'en' => ['module.php' => "<?php return ['fields' => ['name' => 'Name', 'slug' => 'Slug']];"],
        'fa' => ['module.php' => "<?php return ['fields' => ['name' => 'نام']];"],
    ]);

    expect(fn () => TranslationParity::assertModuleTranslationsAreInSync('demo', $directory))
        ->toThrow(AssertionFailedError::class, 'Module [demo] locale [fa] is missing keys from [en] in file [module.php]: fields.slug');
});

it('fails when a translation file does not return an array', function (): void {
    $directory = fakeLanguageDirectory([
        'en' => ['module.php' => "<?php return 'not an array';"],
        'fa' => ['module.php' => "<?php return ['title' => 'عنوان'];"],
    ]);

    expect(fn () => TranslationParity::assertModuleTranslationsAreInSync('demo', $directory))
        ->toThrow(AssertionFailedError::class, 'must return an array.');
});

it('passes when translation keys are sorted at every level', function (): void {
    $directory = fakeLanguageDirectory([
        'en' => ['module.php' => "<?php return ['fields' => ['name' => 'Name', 'slug' => 'Slug'], 'title' => 'Title'];"],
    ]);

    TranslationParity::assertModuleTranslationKeysAreSorted('demo', $directory);
});

it('fails when top-level translation keys are unsorted', function (): void {
    $directory = fakeLanguageDirectory([
        'en' => ['module.php' => "<?php return ['title' => 'Title', 'fields' => ['name' => 'Name']];"],
    ]);

    expect(fn () => TranslationParity::assertModuleTranslationKeysAreSorted('demo', $directory))
        ->toThrow(AssertionFailedError::class, 'Module [demo] locale [en] file [module.php] has unsorted keys.');
});

it('fails when nested translation keys are unsorted', function (): void {
    $directory = fakeLanguageDirectory([
        'en' => ['module.php' => "<?php return ['fields' => ['slug' => 'Slug', 'name' => 'Name']];"],
    ]);

    expect(fn () => TranslationParity::assertModuleTranslationKeysAreSorted('demo', $directory))
        ->toThrow(AssertionFailedError::class, 'Module [demo] locale [en] file [module.php].fields has unsorted keys.');
});

it('ignores list values when asserting sorted translation keys', function (): void {
    $directory = fakeLanguageDirectory([
        'en' => ['module.php' => "<?php return ['options' => ['zebra', 'apple']];"],
    ]);

    TranslationParity::assertModuleTranslationKeysAreSorted('demo', $directory);
});
