<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Misaf\VendraAffiliate\Models\Affiliate;
use Misaf\VendraSupport\Capabilities\TagIntegration;
use Misaf\VendraSupport\Filament\Infolists\Components\CreatedAtEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\UpdatedAtEntry;
use Misaf\VendraTagger\Filament\Infolists\Components\ModelTagsEntry;

final class AffiliateInfolist
{
    public static function configure(Schema $schema): Schema
    {
        $components = [
            TextEntry::make('user.username')
                ->label(__('vendra-affiliate::attributes.user')),

            TextEntry::make('code')
                ->copyable()
                ->label(__('vendra-affiliate::attributes.code')),

            TextEntry::make('commission_percent')
                ->label(__('vendra-affiliate::attributes.commission_percent'))
                ->suffix('%'),

            TextEntry::make('signup_bounty')
                ->label(__('vendra-affiliate::attributes.signup_bounty'))
                ->numeric(locale: 'en', maxDecimalPlaces: 0),

            TextEntry::make('pending_balance')
                ->label(__('vendra-affiliate::attributes.pending_balance'))
                ->numeric(locale: 'en', maxDecimalPlaces: 0)
                ->state(fn (Affiliate $record): int => $record->pendingBalance()),

            TextEntry::make('status')
                ->badge()
                ->label(__('vendra-affiliate::attributes.status')),

            CreatedAtEntry::make(),
            UpdatedAtEntry::make(),
        ];

        if (TagIntegration::isAvailable()) {
            $components[] = ModelTagsEntry::make()
                ->type(Affiliate::TAG_TYPE);
        }

        return $schema
            ->components($components)
            ->columns(2);
    }
}
