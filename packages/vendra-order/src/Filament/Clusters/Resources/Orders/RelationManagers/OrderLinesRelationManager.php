<?php

declare(strict_types=1);

namespace Misaf\VendraOrder\Filament\Clusters\Resources\Orders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number;
use Misaf\VendraOrder\Models\Order;

final class OrderLinesRelationManager extends RelationManager
{
    protected static string $relationship = 'lines';

    protected static bool $isBadgeDeferred = true;

    protected static bool $isLazy = false;

    public static function getModelLabel(): string
    {
        return __('vendra-order::navigation.order_line');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-order::navigation.order_lines');
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): string
    {
        $lineCount = $ownerRecord instanceof Order
            ? $ownerRecord->lines()->count()
            : 0;

        return (string) Number::format($lineCount);
    }

    /**
     * Order lines are immutable purchase snapshots.
     */
    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('vendra-order::attributes.name'))
                    ->searchable(),

                TextColumn::make('sellable_type')
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->icon(Heroicon::Tag)
                    ->label(__('vendra-order::attributes.sellable_type')),

                TextColumn::make('sellable_id')
                    ->label(__('vendra-order::attributes.sellable_id'))
                    ->sortable(),

                TextColumn::make('quantity')
                    ->label(__('vendra-order::attributes.quantity'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('unit_amount')
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-order::attributes.unit_amount')),

                TextColumn::make('line_amount')
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-order::attributes.line_amount')),
            ])
            ->defaultSort('id', 'asc');
    }
}
