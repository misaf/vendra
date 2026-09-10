<?php

declare(strict_types=1);

namespace Misaf\VendraCart\Filament\Clusters\Resources\Carts\RelationManagers;

use Filament\Actions\DeleteAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number;
use Misaf\VendraCart\Models\Cart;

final class CartItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static bool $isBadgeDeferred = true;

    protected static bool $isLazy = false;

    public static function getModelLabel(): string
    {
        return __('vendra-cart::navigation.cart_item');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-cart::navigation.cart_items');
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): string
    {
        $itemCount = $ownerRecord instanceof Cart
            ? $ownerRecord->items()->count()
            : 0;

        return (string) Number::format($itemCount);
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sellable_type')
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->icon(Heroicon::Tag)
                    ->label(__('vendra-cart::attributes.sellable_type'))
                    ->searchable(),

                TextColumn::make('sellable_id')
                    ->label(__('vendra-cart::attributes.sellable_id'))
                    ->sortable(),

                TextColumn::make('quantity')
                    ->label(__('vendra-cart::attributes.quantity'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('metadata')
                    ->formatStateUsing(fn (?array $state): string => $state ? json_encode($state, JSON_THROW_ON_ERROR) : '—')
                    ->label(__('vendra-cart::attributes.metadata')),

                TextColumn::make('created_at')
                    ->alignCenter()
                    ->badge()
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-cart::attributes.created_at'))
                    ->sinceTooltip()
                    ->sortable()
                    ->when(
                        app()->isLocale('fa'),
                        fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                        fn (TextColumn $column) => $column->dateTime('Y-m-d H:i'),
                    ),
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
