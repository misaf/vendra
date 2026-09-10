<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Filament\Clusters\Resources\Wallets\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Misaf\VendraTransaction\Models\Wallet;

final class WalletInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user')
                    ->label(__('vendra-transaction::attributes.user'))
                    ->state(function (Wallet $record): string {
                        $username = $record->user?->getAttribute('username');

                        if (is_string($username) && filled($username)) {
                            return $username;
                        }

                        $name = $record->user?->getAttribute('name');

                        if (is_string($name) && filled($name)) {
                            return $name;
                        }

                        return "#{$record->user_id}";
                    }),
                TextEntry::make('currency_code')
                    ->badge()
                    ->label(__('vendra-transaction::attributes.currency')),
                TextEntry::make('balance')
                    ->label(__('vendra-transaction::attributes.balance'))
                    ->numeric(),
                self::dateEntry('created_at'),
                self::dateEntry('updated_at'),
            ])
            ->columns(2);
    }

    private static function dateEntry(string $name): TextEntry
    {
        return TextEntry::make($name)
            ->label(__("vendra-transaction::attributes.{$name}"))
            ->when(
                app()->isLocale('fa'),
                fn (TextEntry $entry): TextEntry => $entry->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                fn (TextEntry $entry): TextEntry => $entry->dateTime('Y-m-d H:i'),
            );
    }
}
