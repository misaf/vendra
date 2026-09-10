<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliatePayouts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

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

                self::dateEntry('processed_at'),
                self::dateEntry('created_at'),
                self::dateEntry('updated_at'),
            ])
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
