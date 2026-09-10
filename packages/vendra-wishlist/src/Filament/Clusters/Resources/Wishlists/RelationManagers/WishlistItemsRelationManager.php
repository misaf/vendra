<?php

declare(strict_types=1);

namespace Misaf\VendraWishlist\Filament\Clusters\Resources\Wishlists\RelationManagers;

use Filament\Actions\DeleteAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number;
use Misaf\VendraWishlist\Models\Wishlist;

final class WishlistItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static bool $isBadgeDeferred = true;

    protected static bool $isLazy = false;

    public static function getModelLabel(): string
    {
        return __('vendra-wishlist::navigation.wishlist_item');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-wishlist::navigation.wishlist_items');
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): string
    {
        $itemCount = $ownerRecord instanceof Wishlist
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
                    ->label(__('vendra-wishlist::attributes.sellable_type'))
                    ->searchable(),

                TextColumn::make('sellable_id')
                    ->label(__('vendra-wishlist::attributes.sellable_id'))
                    ->sortable(),

                TextColumn::make('metadata')
                    ->formatStateUsing(fn (?array $state): string => $state ? json_encode($state, JSON_THROW_ON_ERROR) : '—')
                    ->label(__('vendra-wishlist::attributes.metadata')),

                TextColumn::make('created_at')
                    ->alignCenter()
                    ->badge()
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-wishlist::attributes.created_at'))
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
