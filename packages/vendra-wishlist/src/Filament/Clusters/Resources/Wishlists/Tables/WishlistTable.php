<?php

declare(strict_types=1);

namespace Misaf\VendraWishlist\Filament\Clusters\Resources\Wishlists\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Misaf\VendraSupport\Filament\Tables\Columns\CreatedAtColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\IsDefaultIconColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\NameColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\RowIndexColumn;
use Misaf\VendraSupport\Filament\Tables\Filters\QueryBuilder\Constraints\IsDefaultConstraint;
use Misaf\VendraSupport\Filament\Tables\Filters\QueryBuilder\Constraints\NameConstraint;

final class WishlistTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                RowIndexColumn::make(),

                NameColumn::make()
                    ->icon(Heroicon::Heart)
                    ->searchable(),

                TextColumn::make('owner_label')
                    ->label(__('vendra-wishlist::attributes.owner'))
                    ->placeholder('—'),

                TextColumn::make('items_count')
                    ->badge()
                    ->counts('items')
                    ->label(__('vendra-wishlist::attributes.items')),

                IsDefaultIconColumn::make(),

                CreatedAtColumn::make()
                    ->sortable(),
            ])
            ->description(__('vendra-wishlist::tables.description.wishlists'))
            ->emptyStateHeading(__('vendra-wishlist::tables.empty_state.heading.wishlists'))
            ->emptyStateDescription(__('vendra-wishlist::tables.empty_state.description.wishlists'))
            ->emptyStateIcon(Heroicon::OutlinedHeart)
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),

                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('owner'))
            ->filters([
                QueryBuilder::make()
                    ->constraints([
                        NameConstraint::make(),

                        IsDefaultConstraint::make(),
                    ]),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->defaultSort(column: 'id', direction: 'desc');
    }
}
