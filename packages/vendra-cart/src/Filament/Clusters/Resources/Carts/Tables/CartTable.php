<?php

declare(strict_types=1);

namespace Misaf\VendraCart\Filament\Clusters\Resources\Carts\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class CartTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('row')
                    ->label('#')
                    ->rowIndex()
                    ->sortable(['id']),

                TextColumn::make('token')
                    ->copyable()
                    ->label(__('vendra-cart::attributes.token'))
                    ->icon(Heroicon::Key)
                    ->searchable(),

                TextColumn::make('owner_label')
                    ->label(__('vendra-cart::attributes.owner'))
                    ->placeholder('—'),

                TextColumn::make('items_count')
                    ->badge()
                    ->counts('items')
                    ->label(__('vendra-cart::attributes.items')),

                TextColumn::make('expires_at')
                    ->alignCenter()
                    ->badge()
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-cart::attributes.expires_at'))
                    ->placeholder('—')
                    ->sinceTooltip()
                    ->sortable()
                    ->when(
                        app()->isLocale('fa'),
                        fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                        fn (TextColumn $column) => $column->dateTime('Y-m-d H:i'),
                    ),

                TextColumn::make('created_at')
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
            ->description(__('vendra-cart::tables.description.carts'))
            ->emptyStateHeading(__('vendra-cart::tables.empty_state.heading.carts'))
            ->emptyStateDescription(__('vendra-cart::tables.empty_state.description.carts'))
            ->emptyStateIcon(Heroicon::OutlinedShoppingCart)
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
                        TextConstraint::make('token'),
                        DateConstraint::make('expires_at'),
                    ]),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->defaultSort(column: 'id', direction: 'desc');
    }
}
