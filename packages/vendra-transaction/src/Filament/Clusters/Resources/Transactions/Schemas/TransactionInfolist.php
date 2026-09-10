<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Misaf\VendraTransaction\Models\Transaction;

final class TransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('token')
                    ->copyable()
                    ->extraAttributes(['dir' => 'ltr'])
                    ->fontFamily('mono')
                    ->label(__('vendra-transaction::attributes.token')),
                TextEntry::make('wallet.user')
                    ->label(__('vendra-transaction::attributes.user'))
                    ->state(function (Transaction $record): string {
                        $wallet = $record->wallet;

                        if ($wallet === null) {
                            return '—';
                        }

                        $username = $wallet->user?->getAttribute('username');

                        if (is_string($username) && filled($username)) {
                            return $username;
                        }

                        $name = $wallet->user?->getAttribute('name');

                        if (is_string($name) && filled($name)) {
                            return $name;
                        }

                        return "#{$wallet->user_id}";
                    }),
                TextEntry::make('wallet.currency_code')
                    ->badge()
                    ->label(__('vendra-transaction::attributes.currency')),
                TextEntry::make('transactionGateway.name')
                    ->label(__('vendra-transaction::attributes.transaction_gateway')),
                TextEntry::make('transaction_type')
                    ->badge()
                    ->label(__('vendra-transaction::attributes.transaction_type')),
                TextEntry::make('amount')
                    ->extraAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-transaction::attributes.amount'))
                    ->numeric(),
                TextEntry::make('transactionFee.amount')
                    ->label(__('vendra-transaction::attributes.fee'))
                    ->numeric()
                    ->placeholder('—'),
                TextEntry::make('status')
                    ->badge()
                    ->color(fn (Transaction $record): array => $record->status->getColor())
                    ->formatStateUsing(fn (Transaction $record): string => $record->status->getLabel())
                    ->label(__('vendra-transaction::attributes.status')),
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
