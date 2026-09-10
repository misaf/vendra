<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Filament\Widgets;

use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Misaf\VendraTransaction\Models\Transaction;
use Misaf\VendraTransaction\States\TransactionState;

final class LatestTransactionTableWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 1;
    }

    public static function isDiscovered(): bool
    {
        return true;
    }

    public static function canView(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('vendra-transaction::widgets.recent_transaction_table'))
            ->query(fn (): Builder => Transaction::query()->with(['wallet.user']))
            ->columns([
                TextColumn::make('token')
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->fontFamily('mono')
                    ->label(__('vendra-transaction::attributes.token'))
                    ->icon(Heroicon::Key)
                    ->limit(12),

                TextColumn::make('wallet.user')
                    ->label(__('vendra-transaction::attributes.user'))
                    ->state(function (Transaction $record): string {
                        return (string) ($record->wallet->user?->getAttribute('username')
                            ?? $record->wallet->user?->getAttribute('name')
                            ?? "#{$record->wallet->user_id}");
                    }),

                TextColumn::make('transaction_type')
                    ->badge()
                    ->label(__('vendra-transaction::attributes.transaction_type'))
                    ->icon(Heroicon::Tag),

                TextColumn::make('amount')
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-transaction::attributes.amount'))
                    ->numeric(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (TransactionState $state): array => $state->getColor())
                    ->formatStateUsing(fn (TransactionState $state): string => $state->getLabel())
                    ->label(__('vendra-transaction::attributes.status')),

                TextColumn::make('created_at')
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-transaction::attributes.created_at'))
                    ->sinceTooltip()
                    ->when(
                        app()->isLocale('fa'),
                        fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                        fn (TextColumn $column) => $column->dateTime('Y-m-d H:i'),
                    ),
            ])
            ->defaultSort(column: 'id', direction: 'desc')
            ->paginationPageOptions([5]);
    }
}
