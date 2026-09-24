<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Filament\Clusters\Resources\Wallets\RelationManagers;

use BackedEnum;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraSupport\Filament\Tables\Columns\CreatedAtColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\RowIndexColumn;

final class LedgerEntriesRelationManager extends RelationManager
{
    protected static string $relationship = 'ledgerEntries';

    protected static string|BackedEnum|null $icon = Heroicon::OutlinedBookOpen;

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
                RowIndexColumn::make(),

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

                CreatedAtColumn::make()
                    ->alignCenter()
                    ->badge()
                    ->sortable(),
            ])
            ->defaultSort(column: 'id', direction: 'desc');
    }
}
