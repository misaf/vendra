<?php

declare(strict_types=1);

use Misaf\VendraLanguage\Support\TranslationCatalog;

it('registered translation namespaces are available as sorted select options', function (): void {
    $options = app(TranslationCatalog::class)->namespaceOptions();
    $sortedNamespaces = array_keys($options);
    sort($sortedNamespaces);

    expect($options)
        ->toHaveKey('vendra-language', 'vendra-language')
        ->and(array_keys($options))->each->toStartWith('vendra-')
        ->and(array_keys($options))->toBe($sortedNamespaces);
});

it('translation files and their keys are available as dependent select options', function (): void {
    $catalog = app(TranslationCatalog::class);

    expect($catalog->groupOptions('vendra-language'))
        ->toHaveKeys(['attributes', 'navigation'])
        ->and($catalog->keyOptions('vendra-language', 'navigation'))
        ->toHaveKeys(['language', 'language_line', 'language_management', 'languages'])
        ->and($catalog->keyOptions('filament-panels', 'resources'))
        ->toBe([])
        ->and($catalog->keyOptions('vendra-language', '../composer'))
        ->toBe([]);
});

it('translation files are exposed as locale-complete language lines', function (): void {
    $languageLine = collect(app(TranslationCatalog::class)->languageLines())
        ->first(fn (array $line): bool => $line['namespace'] === 'vendra-language'
            && $line['group'] === 'navigation'
            && $line['key'] === 'language');

    expect($languageLine)
        ->toBeArray()
        ->and($languageLine['text'])->toMatchArray([
            'de' => 'Sprache',
            'en' => 'Language',
            'fa' => 'زبان',
        ]);
});
