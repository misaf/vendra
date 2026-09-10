<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tests\Feature;

it('resolves the translation for the livewire active locale', function (): void {
    $record = new SupportTestTranslatedRecord;
    $record->recordTranslations = ['name' => ['en' => 'Hello', 'fa' => 'سلام']];

    $livewire = new SupportTestTranslatedTableComponent;
    $livewire->activeLocale = 'fa';

    expect(SupportTestTranslatedTableHarness::resolveTranslatedAttribute($record, 'name', $livewire))->toBe('سلام');
});

it('falls back to the app locale when the livewire component has no active locale', function (): void {
    app()->setLocale('en');

    $record = new SupportTestTranslatedRecord;
    $record->recordTranslations = ['name' => ['en' => 'Hello', 'fa' => 'سلام']];

    $livewire = new SupportTestTranslatedTableComponent;

    expect(SupportTestTranslatedTableHarness::resolveTranslatedAttribute($record, 'name', $livewire))->toBe('Hello');

    $livewire->activeLocale = '';

    expect(SupportTestTranslatedTableHarness::resolveTranslatedAttribute($record, 'name', $livewire))->toBe('Hello');
});

it('returns an empty string when the translation is missing or not a string', function (): void {
    $record = new SupportTestTranslatedRecord;
    $record->recordTranslations = ['name' => ['en' => ['unexpected' => 'shape']]];

    $livewire = new SupportTestTranslatedTableComponent;
    $livewire->activeLocale = 'en';

    expect(SupportTestTranslatedTableHarness::resolveTranslatedAttribute($record, 'name', $livewire))->toBe('')
        ->and(SupportTestTranslatedTableHarness::resolveTranslatedAttribute($record, 'missing', $livewire))->toBe('');
});

it('coerces integer attributes', function (): void {
    $record = new SupportTestTranslatedRecord;
    $record->setRawAttributes([
        'position' => 7,
        'numeric' => '12',
        'malformed' => 'not-a-number',
    ]);

    expect(SupportTestTranslatedTableHarness::resolveIntegerAttribute($record, 'position'))->toBe(7)
        ->and(SupportTestTranslatedTableHarness::resolveIntegerAttribute($record, 'numeric'))->toBe(12)
        ->and(SupportTestTranslatedTableHarness::resolveIntegerAttribute($record, 'malformed'))->toBe(0)
        ->and(SupportTestTranslatedTableHarness::resolveIntegerAttribute($record, 'absent'))->toBe(0);
});
