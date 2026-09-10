<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use Misaf\VendraAuthifyLog\Database\Factories\AuthifyLogFactory;
use Misaf\VendraAuthifyLog\Filament\Clusters\Resources\Pages\ListAuthifyLogs;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    setUpFilamentAdminTestContext();
});

it('sorts the authify logs table by every sortable column following the stored values', function (): void {
    $first = AuthifyLogFactory::new()->createOne();
    $second = AuthifyLogFactory::new()->createOne();

    expect(livewire(ListAuthifyLogs::class)->call('loadTable'))
        ->toSortByEverySortableColumn([$first, $second]);
});

it('escapes dynamic authentication log table markup', function (): void {
    $authifyLog = AuthifyLogFactory::new()->createOne([
        'ip_address' => '<script>alert(1)</script>',
        'ip_country' => 'US',
    ]);

    $formattedState = '<span class="flex items-center space-x-2">'
        .'<img src="'.e(asset('vendor/blade-country-flags/4x3-'.Str::lower($authifyLog->ip_country).'.svg')).'" alt="US" title="US" class="w-4 inline-block" />'
        .'<span>&lt;script&gt;alert(1)&lt;/script&gt;</span>'
        .'</span>';

    livewire(ListAuthifyLogs::class)
        ->call('loadTable')
        ->assertTableColumnFormattedStateSet('ip_address', $formattedState, $authifyLog);
});
