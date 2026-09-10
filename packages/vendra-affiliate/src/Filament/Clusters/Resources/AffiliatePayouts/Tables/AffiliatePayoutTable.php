<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliatePayouts\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint\Operators\IsRelatedToOperator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Misaf\VendraAffiliate\Enums\PayoutStatusEnum;

final class AffiliatePayoutTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('row')
                    ->label('#')
                    ->rowIndex()
                    ->sortable(['id']),

                TextColumn::make('affiliate.code')
                    ->label(__('vendra-affiliate::attributes.affiliate'))
                    ->icon(Heroicon::CodeBracket)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('amount')
                    ->alignCenter()
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-affiliate::attributes.amount'))
                    ->numeric(locale: 'en', maxDecimalPlaces: 0)
                    ->sortable(),

                TextColumn::make('status')
                    ->alignCenter()
                    ->badge()
                    ->label(__('vendra-affiliate::attributes.status')),

                TextColumn::make('transaction_id')
                    ->alignCenter()
                    ->label(__('vendra-affiliate::attributes.transaction'))
                    ->toggleable(),

                TextColumn::make('processed_at')
                    ->alignCenter()
                    ->badge()
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-affiliate::attributes.processed_at'))
                    ->sinceTooltip()
                    ->when(
                        app()->isLocale('fa'),
                        fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                        fn (TextColumn $column) => $column->dateTime('Y-m-d H:i')
                    ),

                TextColumn::make('created_at')
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-affiliate::attributes.created_at'))
                    ->sinceTooltip()
                    ->when(
                        app()->isLocale('fa'),
                        fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                        fn (TextColumn $column) => $column->dateTime('Y-m-d H:i')
                    ),
            ])
            ->filters(
                [
                    SelectFilter::make('status')
                        ->label(__('vendra-affiliate::attributes.status'))
                        ->options(PayoutStatusEnum::class),

                    QueryBuilder::make()
                        ->constraints([
                            RelationshipConstraint::make('affiliate')
                                ->selectable(
                                    IsRelatedToOperator::make()
                                        ->preload()
                                        ->searchable()
                                        ->titleAttribute('code'),
                                ),

                            NumberConstraint::make('amount')
                                ->label(__('vendra-affiliate::attributes.amount')),

                            DateConstraint::make('processed_at')
                                ->label(__('vendra-affiliate::attributes.processed_at')),
                        ]),
                ],
                layout: FiltersLayout::AboveContentCollapsible,
            )
            ->description(__('vendra-affiliate::tables.description.affiliate_payouts'))
            ->emptyStateHeading(__('vendra-affiliate::tables.empty_state.heading.affiliate_payouts'))
            ->emptyStateDescription(__('vendra-affiliate::tables.empty_state.description.affiliate_payouts'))
            ->emptyStateIcon(Heroicon::OutlinedBanknotes)
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                ]),
            ])
            ->defaultSort(column: 'id', direction: 'desc');
    }
}
