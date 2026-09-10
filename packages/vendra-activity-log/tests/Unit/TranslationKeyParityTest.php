<?php

declare(strict_types=1);

it('has at least two available locales', function (): void {
    expect(__DIR__.'/../../resources/lang')->toHaveAtLeastTwoLocales('vendra-activity-log');
});

it('keeps translation files and keys in sync across locales', function (): void {
    expect(__DIR__.'/../../resources/lang')->toHaveTranslationsInSync('vendra-activity-log');
});

it('keeps translation file keys sorted', function (): void {
    expect(__DIR__.'/../../resources/lang')->toHaveSortedTranslationKeys('vendra-activity-log');
});
