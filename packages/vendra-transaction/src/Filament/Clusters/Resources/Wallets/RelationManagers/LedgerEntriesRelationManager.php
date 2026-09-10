<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Filament\Clusters\Resources\Wallets\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class LedgerEntriesRelationManager extends RelationManager
{
    protected static string $relationship = 'ledgerEntries';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('vendra-transaction::navigation.ledger_entries');
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('row')
                    ->label('#')
                    ->rowIndex()
                    ->sortable(['id']),

                TextColumn::make('amount')
                    ->color(fn (int $state): string => $state < 0 ? 'danger' : 'success')
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-transaction::attributes.amount'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('balance_after')
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-transaction::attributes.balance_after'))
                    ->numeric(),

                TextColumn::make('source_type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->icon(Heroicon::Tag)
                    ->label(__('vendra-transaction::attributes.source')),

                TextColumn::make('created_at')
                    ->alignCenter()
                    ->badge()
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
            ->defaultSort(column: 'id', direction: 'desc');
    }
}
