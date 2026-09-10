<?php

declare(strict_types=1);

namespace Misaf\VendraWishlist\Filament\Clusters\Resources\Wishlists\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\BooleanConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class WishlistTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('row')
                    ->label('#')
                    ->rowIndex()
                    ->sortable(['id']),

                TextColumn::make('name')
                    ->icon(Heroicon::Heart)
                    ->label(__('vendra-wishlist::attributes.name'))
                    ->searchable(),

                TextColumn::make('owner_label')
                    ->label(__('vendra-wishlist::attributes.owner'))
                    ->placeholder('—'),

                TextColumn::make('items_count')
                    ->badge()
                    ->counts('items')
                    ->label(__('vendra-wishlist::attributes.items')),

                IconColumn::make('is_default')
                    ->boolean()
                    ->label(__('vendra-wishlist::attributes.is_default')),

                TextColumn::make('created_at')
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
                        TextConstraint::make('name')
                            ->label(__('vendra-wishlist::attributes.name')),

                        BooleanConstraint::make('is_default')
                            ->label(__('vendra-wishlist::attributes.is_default')),
                    ]),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->defaultSort(column: 'id', direction: 'desc');
    }
}
