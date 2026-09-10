<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Filament\Clusters\Resources\Wallets\Tables;

use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\Summarizers\Range;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Table;
use Misaf\VendraTransaction\Models\Wallet;

final class WalletTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['user'])->withCount('transactions'))
            ->description(__('vendra-transaction::tables.description.wallets'))
            ->emptyStateHeading(__('vendra-transaction::tables.empty_state.heading.wallets'))
            ->emptyStateDescription(__('vendra-transaction::tables.empty_state.description.wallets'))
            ->emptyStateIcon(Heroicon::OutlinedWallet)
            ->columns([
                TextColumn::make('row')
                    ->label('#')
                    ->rowIndex()
                    ->sortable(['id']),

                TextColumn::make('user')
                    ->label(__('vendra-transaction::attributes.user'))
                    ->state(fn(Wallet $record): string => (string) ($record->user?->getAttribute('username')
                        ?? $record->user?->getAttribute('name')
                        ?? "#{$record->user_id}")),

                TextColumn::make('currency_code')
                    ->badge()
                    ->label(__('vendra-transaction::attributes.currency'))
                    ->icon(Heroicon::CurrencyDollar),

                TextColumn::make('balance')
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-transaction::attributes.balance'))
                    ->numeric()
                    ->sortable()
                    ->summarize([Sum::make(), Average::make(), Range::make()]),

                TextColumn::make('transactions_count')
                    ->alignCenter()
                    ->badge()
                    ->label(__('vendra-transaction::navigation.transactions')),

                TextColumn::make('created_at')
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-transaction::attributes.created_at'))
                    ->sinceTooltip()
                    ->sortable()
                    ->when(
                        app()->isLocale('fa'),
                        fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                        fn (TextColumn $column) => $column->dateTime('Y-m-d H:i'),
                    ),
            ])
            ->filters(
                [
                    QueryBuilder::make()
                        ->constraints([
                            NumberConstraint::make('balance')
                                ->label(__('vendra-transaction::attributes.balance')),
                        ]),
                ],
                layout: FiltersLayout::AboveContentCollapsible,
            )
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort(column: 'id', direction: 'desc');
    }
}
