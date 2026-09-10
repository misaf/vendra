<?php

declare(strict_types=1);

use Illuminate\Support\Facades\App;
use Misaf\VendraSupport\Tests\Feature\SupportTestTranslatedGlobalSearchHarness;
use Misaf\VendraSupport\Tests\Feature\SupportTestTranslatedRecord;

it('scopes translated global search attributes and titles to the application locale', function (): void {
    App::setLocale('de');

    $record = new SupportTestTranslatedRecord;
    $record->recordTranslations = [
        'name' => [
            'en' => 'English name',
            'de' => 'Deutscher Name',
        ],
    ];

    expect(SupportTestTranslatedGlobalSearchHarness::getGloballySearchableAttributes())
        ->toBe(['name->de', 'slug->de'])
        ->and(SupportTestTranslatedGlobalSearchHarness::getGlobalSearchResultTitle($record))
        ->toBe('Deutscher Name');
});
