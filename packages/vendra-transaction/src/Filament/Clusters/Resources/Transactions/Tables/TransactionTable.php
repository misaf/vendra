<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\Summarizers\Range;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\SelectConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Table;
use Misaf\VendraTransaction\Enums\TransactionTypeEnum;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Actions\ApproveTransactionAction;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Actions\DeclineTransactionAction;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Actions\FailTransactionAction;
use Misaf\VendraTransaction\Models\Transaction;
use Misaf\VendraTransaction\States\TransactionState;

final class TransactionTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['wallet.user', 'transactionGateway']))
            ->description(__('vendra-transaction::tables.description.transactions'))
            ->emptyStateHeading(__('vendra-transaction::tables.empty_state.heading.transactions'))
            ->emptyStateDescription(__('vendra-transaction::tables.empty_state.description.transactions'))
            ->emptyStateIcon(Heroicon::OutlinedArrowsRightLeft)
            ->columns([
                TextColumn::make('row')
                    ->label('#')
                    ->rowIndex()
                    ->sortable(['id']),

                TextColumn::make('token')
                    ->copyable()
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->fontFamily('mono')
                    ->label(__('vendra-transaction::attributes.token'))
                    ->icon(Heroicon::Key)
                    ->limit(12)
                    ->searchable(),

                TextColumn::make('wallet.user')
                    ->label(__('vendra-transaction::attributes.user'))
                    ->state(fn (Transaction $record): string => (string) ($record->wallet->user?->getAttribute('username')
                        ?? $record->wallet->user?->getAttribute('name')
                        ?? "#{$record->wallet->user_id}")),

                TextColumn::make('wallet.currency_code')
                    ->badge()
                    ->label(__('vendra-transaction::attributes.currency'))
                    ->icon(Heroicon::CurrencyDollar),

                TextColumn::make('transactionGateway.name')
                    ->label(__('vendra-transaction::attributes.transaction_gateway'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('transaction_type')
                    ->badge()
                    ->label(__('vendra-transaction::attributes.transaction_type'))
                    ->icon(Heroicon::Tag),

                TextColumn::make('amount')
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-transaction::attributes.amount'))
                    ->numeric()
                    ->sortable()
                    ->summarize([Sum::make(), Average::make(), Range::make()]),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (TransactionState $state): array => $state->getColor())
                    ->formatStateUsing(fn (TransactionState $state): string => $state->getLabel())
                    ->icon(fn (TransactionState $state) => $state->getIcon())
                    ->label(__('vendra-transaction::attributes.status')),

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
                            TextConstraint::make('token')
                                ->label(__('vendra-transaction::attributes.token')),

                            SelectConstraint::make('transaction_type')
                                ->label(__('vendra-transaction::attributes.transaction_type'))
                                ->options(
                                    collect(TransactionTypeEnum::cases())
                                        ->mapWithKeys(fn (TransactionTypeEnum $type): array => [$type->value => $type->getLabel()])
                                        ->all(),
                                ),

                            SelectConstraint::make('status')
                                ->label(__('vendra-transaction::attributes.status'))
                                ->options(
                                    TransactionState::all()
                                        ->mapWithKeys(fn (string $state): array => [$state::getMorphClass() => new $state(new Transaction)->getLabel()])
                                        ->all(),
                                ),

                            NumberConstraint::make('amount')
                                ->label(__('vendra-transaction::attributes.amount')),
                        ]),
                ],
                layout: FiltersLayout::AboveContentCollapsible,
            )
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),

                    ApproveTransactionAction::make(),

                    DeclineTransactionAction::make(),

                    FailTransactionAction::make(),
                ]),
            ])
            ->defaultSort(column: 'id', direction: 'desc');
    }
}
