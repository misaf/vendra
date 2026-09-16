<?php

declare(strict_types=1);

namespace Misaf\VendraMultimedia\Filament\Tables\Columns;

use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;

final class ModelImageColumn extends SpatieMediaLibraryImageColumn
{
    public static function getDefaultName(): string
    {
        return 'image';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('vendra-multimedia::attributes.image'))
            ->alignCenter()
            ->conversion('thumb-table')
            ->extraImgAttributes(['class' => 'saturate-50', 'loading' => 'lazy'])
            ->stacked();
    }
}
