<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliateCommissions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Misaf\VendraSupport\Filament\Infolists\Components\CreatedAtEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\UpdatedAtEntry;

final class AffiliateCommissionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('affiliate.code')
                    ->label(__('vendra-affiliate::attributes.affiliate')),

                TextEntry::make('conversion_type')
                    ->badge()
                    ->label(__('vendra-affiliate::attributes.conversion_type')),

                TextEntry::make('amount')
                    ->label(__('vendra-affiliate::attributes.amount'))
                    ->numeric(locale: 'en', maxDecimalPlaces: 0),

                TextEntry::make('status')
                    ->badge()
                    ->label(__('vendra-affiliate::attributes.status')),

                TextEntry::make('source_type'),
                TextEntry::make('source_id'),
                TextEntry::make('affiliate_referral_id'),
                TextEntry::make('affiliate_payout_id'),
                CreatedAtEntry::make(),
                UpdatedAtEntry::make(),
            ])
            ->columns(2);
    }
}
