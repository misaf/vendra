<?php

declare(strict_types=1);

namespace Misaf\VendraTagger\Filament\Clusters\Resources\Taggers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Misaf\VendraSupport\Filament\Infolists\Components\CreatedAtEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\NameEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\SlugEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\UpdatedAtEntry;

final class TaggerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                NameEntry::make(),
                SlugEntry::make(),
                TextEntry::make('type')
                    ->badge()
                    ->label(__('vendra-tagger::attributes.type')),
                CreatedAtEntry::make(),
                UpdatedAtEntry::make(),
            ])
            ->columns(2);
    }
}
