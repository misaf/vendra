<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Misaf\VendraMultimedia\Filament\Clusters\Resources\Pages\ViewMultimedia;
use Misaf\VendraMultimedia\Models\Multimedia;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    setUpFilamentAdminTestContext();
});

it('renders the view multimedia page under strict authorization', function (): void {
    Filament::getPanel('admin')->strictAuthorization();

    $multimedia = Multimedia::create([
        'model_type' => 'dummy',
        'model_id' => 1,
        'collection_name' => 'default',
        'name' => 'test',
        'file_name' => 'test.txt',
        'mime_type' => 'text/plain',
        'disk' => 'public',
        'conversions_disk' => 'public',
        'size' => 0,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    livewire(ViewMultimedia::class, ['record' => $multimedia->getKey()])
        ->assertOk();
});
