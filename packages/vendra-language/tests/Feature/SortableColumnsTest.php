<?php

declare(strict_types=1);

use Misaf\VendraLanguage\Database\Factories\LanguageFactory;
use Misaf\VendraLanguage\Database\Factories\LanguageLineFactory;
use Misaf\VendraLanguage\Filament\Clusters\Resources\LanguageLines\Pages\ListLanguageLines;
use Misaf\VendraLanguage\Filament\Clusters\Resources\Languages\Pages\ListLanguages;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    setUpFilamentAdminTestContext();
});

it('sorts the languages table by every sortable column following the stored values', function (): void {
    $first = LanguageFactory::new()->createOne(['locale' => 'aa']);
    $second = LanguageFactory::new()->createOne(['locale' => 'zu']);

    expect(livewire(ListLanguages::class)->call('loadTable'))
        ->toSortByEverySortableColumn([$first, $second]);
});

it('sorts the language lines table by every sortable column following the stored values', function (): void {
    $first = LanguageLineFactory::new()->createOne([
        'namespace' => 'aaa-namespace',
        'group' => 'aaa-group',
        'key' => 'aaa-key',
    ]);

    $second = LanguageLineFactory::new()->createOne([
        'namespace' => 'bbb-namespace',
        'group' => 'bbb-group',
        'key' => 'bbb-key',
    ]);

    expect(livewire(ListLanguageLines::class)->call('loadTable'))
        ->toSortByEverySortableColumn([$first, $second]);
});
