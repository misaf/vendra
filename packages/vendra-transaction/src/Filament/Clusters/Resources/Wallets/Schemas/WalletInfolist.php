<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Filament\Clusters\Resources\Wallets\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Misaf\VendraSupport\Filament\Infolists\Components\CreatedAtEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\UpdatedAtEntry;
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
                CreatedAtEntry::make(),
                UpdatedAtEntry::make(),
            ])
            ->columns(2);
    }
}
