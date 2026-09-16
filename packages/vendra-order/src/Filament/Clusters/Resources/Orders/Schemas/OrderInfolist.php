<?php

declare(strict_types=1);

namespace Misaf\VendraOrder\Filament\Clusters\Resources\Orders\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Misaf\VendraOrder\Models\Order;
use Misaf\VendraSupport\Filament\Infolists\Components\CreatedAtEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\DateTimeEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\UpdatedAtEntry;

final class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('number')
                    ->copyable()
                    ->label(__('vendra-order::attributes.number')),
                TextEntry::make('status')
                    ->badge()
                    ->label(__('vendra-order::attributes.status')),
                TextEntry::make('customer_label')
                    ->label(__('vendra-order::attributes.customer'))
                    ->placeholder('—'),
                TextEntry::make('lines_count')
                    ->badge()
                    ->label(__('vendra-order::attributes.lines'))
                    ->state(fn (Order $record): int => $record->lines()->count()),
                TextEntry::make('items_amount')
                    ->label(__('vendra-order::attributes.items_amount')),
                TextEntry::make('delivery_amount')
                    ->label(__('vendra-order::attributes.delivery_amount')),
                TextEntry::make('total_amount')
                    ->label(__('vendra-order::attributes.total_amount')),
                TextEntry::make('transactionGateway.name')
                    ->label(__('vendra-order::attributes.transaction_gateway'))
                    ->placeholder('—'),
                TextEntry::make('payment_reference')
                    ->copyable()
                    ->label(__('vendra-order::attributes.payment_reference'))
                    ->placeholder('—'),
                TextEntry::make('card_message')
                    ->columnSpanFull()
                    ->label(__('vendra-order::attributes.card_message'))
                    ->placeholder('—'),
                DateTimeEntry::make('placed_at')
                    ->label(__('vendra-order::attributes.placed_at')),
                CreatedAtEntry::make(),
                UpdatedAtEntry::make(),
            ])
            ->columns(2);
    }
}
