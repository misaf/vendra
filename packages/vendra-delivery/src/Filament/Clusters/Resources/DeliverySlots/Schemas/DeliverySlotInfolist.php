<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Filament\Clusters\Resources\DeliverySlots\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Misaf\VendraSupport\Filament\Infolists\Components\CreatedAtEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\IsActiveEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\NameEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\UpdatedAtEntry;

final class DeliverySlotInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                NameEntry::make(),
                TextEntry::make('starts_at')
                    ->label(__('vendra-delivery::attributes.starts_at')),
                TextEntry::make('ends_at')
                    ->label(__('vendra-delivery::attributes.ends_at')),
                TextEntry::make('capacity')
                    ->label(__('vendra-delivery::attributes.capacity'))
                    ->placeholder('∞'),
                IsActiveEntry::make(),
                CreatedAtEntry::make(),
                UpdatedAtEntry::make(),
            ])
            ->columns(2);
    }
}
