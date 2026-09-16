<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliatePayouts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Misaf\VendraSupport\Filament\Infolists\Components\CreatedAtEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\DateTimeEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\UpdatedAtEntry;

final class AffiliatePayoutInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('affiliate.code')
                    ->label(__('vendra-affiliate::attributes.affiliate')),

                TextEntry::make('amount')
                    ->label(__('vendra-affiliate::attributes.amount'))
                    ->numeric(locale: 'en', maxDecimalPlaces: 0),

                TextEntry::make('status')
                    ->badge()
                    ->label(__('vendra-affiliate::attributes.status')),

                TextEntry::make('transaction_id')
                    ->label(__('vendra-affiliate::attributes.transaction')),

                DateTimeEntry::make('processed_at')

                    ->label(__('vendra-affiliate::attributes.processed_at')),
                CreatedAtEntry::make(),
                UpdatedAtEntry::make(),
            ])
            ->columns(2);
    }
}
