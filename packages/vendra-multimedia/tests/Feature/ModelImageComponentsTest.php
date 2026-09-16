<?php

declare(strict_types=1);

use Misaf\VendraMultimedia\Filament\Forms\Components\ModelImageUpload;
use Misaf\VendraMultimedia\Filament\Infolists\Components\ModelImageEntry;
use Misaf\VendraMultimedia\Filament\Tables\Columns\ModelImageColumn;

it('defaults the image upload, entry and column to the image collection with the shared label', function (): void {
    $label = __('vendra-multimedia::attributes.image');
    $upload = ModelImageUpload::make()->collection('avatars');
    $column = ModelImageColumn::make()->collection('avatars');

    expect($upload->getName())->toBe('image')
        ->and($upload->getLabel())->toBe($label)
        ->and($upload->getCollection())->toBe('avatars')
        ->and($upload->getPanelLayout())->toBe('grid')
        ->and($upload->hasResponsiveImages())->toBeTrue()
        ->and(ModelImageEntry::make()->getName())->toBe('image')
        ->and(ModelImageEntry::make()->getLabel())->toBe($label)
        ->and($column->getName())->toBe('image')
        ->and($column->getLabel())->toBe($label)
        ->and($column->getConversion())->toBe('thumb-table')
        ->and($column->isStacked())->toBeTrue();
});
