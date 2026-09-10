<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\Schemas;

use Filament\Infolists\Components\SpatieTagsEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Misaf\VendraAffiliate\Models\Affiliate;
use Misaf\VendraSupport\Capabilities\TagIntegration;

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

            self::dateEntry('created_at'),
            self::dateEntry('updated_at'),
        ];

        if (TagIntegration::isAvailable()) {
            $components[] = SpatieTagsEntry::make('tags')
                ->columnSpanFull()
                ->label(__('vendra-support::attributes.tags'))
                ->type(Affiliate::TAG_TYPE);
        }

        return $schema
            ->components($components)
            ->columns(2);
    }

    private static function dateEntry(string $name): TextEntry
    {
        return TextEntry::make($name)
            ->label(__("vendra-affiliate::attributes.{$name}"))
            ->when(
                app()->isLocale('fa'),
                fn (TextEntry $entry): TextEntry => $entry->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                fn (TextEntry $entry): TextEntry => $entry->dateTime('Y-m-d H:i'),
            );
    }
}
