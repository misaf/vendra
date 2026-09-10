<?php

declare(strict_types=1);

namespace Misaf\VendraCurrency\Filament\Clusters\Resources\Currencies\Tables;

use Awcodes\BadgeableColumn\Components\Badge;
use Awcodes\BadgeableColumn\Components\BadgeableColumn;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Size;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\BooleanConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\SelectConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Table;
use Misaf\VendraCurrency\Enums\CurrencyType;
use Misaf\VendraCurrency\Filament\Clusters\Resources\Currencies\Actions\SetDefaultCurrencyTableAction;
use Misaf\VendraCurrency\Filament\Clusters\Resources\Currencies\CurrencyResource;
use Misaf\VendraCurrency\Models\Currency;

final class CurrencyTable
{
    public static function configure(Table $table): Table
    {
        /**
         * @var array<int, Column> $columns
         */
        $columns = [
            TextColumn::make('row')
                ->label('#')
                ->rowIndex()
                ->sortable(['id']),

            TextColumn::make('code')
                ->badge()
                ->label(__('vendra-currency::attributes.code'))
                ->icon(Heroicon::CodeBracket)
                ->searchable()
                ->sortable(),

            BadgeableColumn::make('name')
                ->label(__('vendra-currency::attributes.name'))
                ->icon(Heroicon::Tag)
                ->searchable()
                ->sortable()
                ->prefixBadges([
                    Badge::make('is_default')
                        ->label(__('vendra-currency::attributes.is_default'))
                        ->color('success')
                        ->size(Size::ExtraSmall)
                        ->hidden(fn (Currency $record): bool => ! $record->is_default),
                ]),

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

            ToggleColumn::make('active')
                ->label(__('vendra-currency::attributes.active'))
                ->onIcon(Heroicon::Bolt)
                ->disabled(fn (Currency $record): bool => ! CurrencyResource::canEdit($record)),

            TextColumn::make('created_at')
                ->extraCellAttributes(['dir' => 'ltr'])
                ->label(__('vendra-currency::attributes.created_at'))
                ->sinceTooltip()
                ->when(
                    app()->isLocale('fa'),
                    fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                    fn (TextColumn $column) => $column->dateTime('Y-m-d H:i')
                ),

            TextColumn::make('updated_at')
                ->extraCellAttributes(['dir' => 'ltr'])
                ->label(__('vendra-currency::attributes.updated_at'))
                ->sinceTooltip()
                ->when(
                    app()->isLocale('fa'),
                    fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                    fn (TextColumn $column) => $column->dateTime('Y-m-d H:i')
                ),
        ];

        return $table
            ->columns($columns)
            ->filters(
                [
                    QueryBuilder::make()
                        ->constraints([
                            TextConstraint::make('code')
                                ->label(__('vendra-currency::attributes.code')),

                            TextConstraint::make('name')
                                ->label(__('vendra-currency::attributes.name')),

                            SelectConstraint::make('type')
                                ->label(__('vendra-currency::attributes.type'))
                                ->options(CurrencyType::class),

                            BooleanConstraint::make('active')
                                ->label(__('vendra-currency::attributes.active')),

                            BooleanConstraint::make('is_default')
                                ->label(__('vendra-currency::attributes.is_default')),

                            NumberConstraint::make('position')
                                ->label(__('vendra-currency::attributes.position')),
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
