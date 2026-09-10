<?php

declare(strict_types=1);

use Awcodes\BadgeableColumn\Components\BadgeableColumn;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Misaf\VendraMultimedia\Filament\Clusters\Resources\Pages\ListMultimedia;
use Misaf\VendraMultimedia\Models\Multimedia;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    setUpFilamentAdminTestContext();
});

function createMultimediaRecord(string $collectionName, int $size): Multimedia
{
    return Multimedia::query()->create([
        'model_type' => 'test-model',
        'model_id' => 1,
        'uuid' => (string) Str::uuid(),
        'collection_name' => $collectionName,
        'name' => $collectionName,
        'file_name' => "{$collectionName}.jpg",
        'mime_type' => 'image/jpeg',
        'disk' => 'public',
        'conversions_disk' => 'public',
        'size' => $size,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);
}

it('sorts the multimedia table by every sortable column following the stored values', function (): void {
    $first = createMultimediaRecord('aaa-collection', 100);
    $second = createMultimediaRecord('bbb-collection', 90_000);

    expect(livewire(ListMultimedia::class)->call('loadTable'))
        ->toSortByEverySortableColumn([$first, $second]);
});

it('renders the model identifier as a suffix badge', function (): void {
    $multimedia = createMultimediaRecord('test-collection', 100);
    $multimedia->forceFill(['model_id' => 42])->save();

    livewire(ListMultimedia::class)
        ->call('loadTable')
        ->assertTableColumnExists(
            'model_type',
            function (BadgeableColumn $column): bool {
                $formattedState = (string) $column->formatState('test-model');

                return str_contains($formattedState, 'badgeable-column-badge')
                    && str_contains($formattedState, Number::format(42));
            },
            $multimedia,
        );
});
