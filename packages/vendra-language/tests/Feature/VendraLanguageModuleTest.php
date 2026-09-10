<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Misaf\VendraLanguage\Support\TranslationCatalog;

it('registered translation namespaces are available as sorted select options', function (): void {
    $options = resolve(TranslationCatalog::class)->namespaceOptions();
    $sortedNamespaces = array_keys($options);
    sort($sortedNamespaces);

    expect($options)
        ->toHaveKey('vendra-language', 'vendra-language')
        ->and(array_keys($options))->each->toStartWith('vendra-')
        ->and(array_keys($options))->toBe($sortedNamespaces);
});

it('translation files and their keys are available as dependent select options', function (): void {
    $catalog = resolve(TranslationCatalog::class);

    expect($catalog->groupOptions('vendra-language'))
        ->toHaveKeys(['attributes', 'navigation'])
        ->and($catalog->keyOptions('vendra-language', 'navigation'))
        ->toHaveKeys(['language', 'language_line', 'language_management', 'languages'])
        ->and($catalog->keyOptions('filament-panels', 'resources'))->toBeEmpty()
        ->and($catalog->keyOptions('vendra-language', '../composer'))->toBeEmpty();
});

it('translation files are exposed as locale-complete language lines', function (): void {
    $languageLine = collect(resolve(TranslationCatalog::class)->languageLines())
        ->first(fn (array $line): bool => Arr::get($line, 'namespace') === 'vendra-language'
            && Arr::get($line, 'group') === 'navigation'
            && Arr::get($line, 'key') === 'language');

    expect($languageLine)
        ->toBeArray()
        ->and(Arr::get($languageLine, 'text'))->toMatchArray([
            'de' => 'Sprache',
            'en' => 'Language',
            'fa' => 'زبان',
        ]);
});
