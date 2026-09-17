<?php

declare(strict_types=1);

namespace Misaf\VendraCurrency\Filament\Clusters\Resources\Currencies\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\SelectConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Table;
use Misaf\VendraCurrency\Enums\CurrencyType;
use Misaf\VendraCurrency\Filament\Clusters\Resources\Currencies\Actions\SetDefaultCurrencyTableAction;
use Misaf\VendraCurrency\Filament\Clusters\Resources\Currencies\CurrencyResource;
use Misaf\VendraCurrency\Models\Currency;
use Misaf\VendraSupport\Filament\Tables\Columns\CreatedAtColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\IsActiveToggleColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\IsDefaultIconColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\RowIndexColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\UpdatedAtColumn;
use Misaf\VendraSupport\Filament\Tables\Filters\QueryBuilder\Constraints\IsActiveConstraint;
use Misaf\VendraSupport\Filament\Tables\Filters\QueryBuilder\Constraints\IsDefaultConstraint;
use Misaf\VendraSupport\Filament\Tables\Filters\QueryBuilder\Constraints\NameConstraint;
use Misaf\VendraSupport\Filament\Tables\Filters\QueryBuilder\Constraints\PositionConstraint;

final class CurrencyTable
{
    public static function configure(Table $table): Table
    {
        /**
         * @var array<int, Column> $columns
         */
        $columns = [
            RowIndexColumn::make(),

            TextColumn::make('code')
                ->badge()
                ->label(__('vendra-currency::attributes.code'))
                ->icon(Heroicon::CodeBracket)
                ->searchable()
                ->sortable(),

            TextColumn::make('name')
                ->label(__('vendra-currency::attributes.name'))
                ->icon(Heroicon::Tag)
                ->searchable()
                ->sortable(),

            IsDefaultIconColumn::make(),

            TextColumn::make('symbol')
                ->label(__('vendra-currency::attributes.symbol'))
                ->icon(Heroicon::AtSymbol)
                ->placeholder('—'),

            TextColumn::make('type')
                ->badge()
                ->color(fn (CurrencyType $state): string => $state === CurrencyType::Fiat ? 'success' : 'warning')
                ->label(__('vendra-currency::attributes.type'))
                ->icon(Heroicon::Tag),

            TextColumn::make('decimal_places')
                ->alignCenter()
                ->label(__('vendra-currency::attributes.decimal_places')),

            IsActiveToggleColumn::make()
                ->disabled(fn (Currency $record): bool => ! CurrencyResource::canEdit($record)),

            CreatedAtColumn::make(),

            UpdatedAtColumn::make(),
        ];

        return $table
            ->columns($columns)
            ->filters(
                [
                    QueryBuilder::make()
                        ->constraints([
                            TextConstraint::make('code')
                                ->label(__('vendra-currency::attributes.code')),

                            NameConstraint::make(),

                            SelectConstraint::make('type')
                                ->label(__('vendra-currency::attributes.type'))
                                ->options(CurrencyType::class),

                            IsActiveConstraint::make(),

                            IsDefaultConstraint::make(),

                            PositionConstraint::make(),
                        ]),
                ],
                layout: FiltersLayout::AboveContentCollapsible,
            )
            ->description(__('vendra-currency::tables.description.currencies'))
            ->emptyStateHeading(__('vendra-currency::tables.empty_state.heading.currencies'))
            ->emptyStateDescription(__('vendra-currency::tables.empty_state.description.currencies'))
            ->emptyStateIcon(Heroicon::OutlinedBanknotes)
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),

                    EditAction::make(),

                    SetDefaultCurrencyTableAction::make(),

                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort(column: 'id', direction: 'desc')
            ->reorderable(column: 'position');
    }
}
