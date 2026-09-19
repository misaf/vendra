<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliateCommissions\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\Summarizers\Range;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint\Operators\IsRelatedToOperator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Misaf\VendraAffiliate\Enums\CommissionStatusEnum;
use Misaf\VendraAffiliate\Enums\ConversionTypeEnum;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliateCommissions\Actions\ApproveCommissionTableAction;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliateCommissions\Actions\ReverseCommissionTableAction;
use Misaf\VendraSupport\Filament\Tables\Columns\CreatedAtColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\RowIndexColumn;

final class AffiliateCommissionTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                RowIndexColumn::make(),

                TextColumn::make('affiliate.code')
                    ->label(__('vendra-affiliate::attributes.affiliate'))
                    ->icon(Heroicon::CodeBracket)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('conversion_type')
                    ->alignCenter()
                    ->badge()
                    ->label(__('vendra-affiliate::attributes.conversion_type'))
                    ->icon(Heroicon::Tag),

                TextColumn::make('amount')
                    ->alignCenter()
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-affiliate::attributes.amount'))
                    ->numeric(locale: 'en', maxDecimalPlaces: 0)
                    ->sortable()
                    ->summarize([Sum::make(), Average::make(), Range::make()]),

                TextColumn::make('status')
                    ->alignCenter()
                    ->badge()
                    ->label(__('vendra-affiliate::attributes.status')),

                CreatedAtColumn::make(),
            ])
            ->filters(
                [
                    SelectFilter::make('status')
                        ->label(__('vendra-affiliate::attributes.status'))
                        ->options(CommissionStatusEnum::class),

                    SelectFilter::make('conversion_type')
                        ->label(__('vendra-affiliate::attributes.conversion_type'))
                        ->options(ConversionTypeEnum::class),

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

                            DateConstraint::make('created_at')
                                ->label(__('vendra-affiliate::attributes.created_at')),
                        ]),
                ],
                layout: FiltersLayout::AboveContentCollapsible,
            )
            ->description(__('vendra-affiliate::tables.description.affiliate_commissions'))
            ->emptyStateHeading(__('vendra-affiliate::tables.empty_state.heading.affiliate_commissions'))
            ->emptyStateDescription(__('vendra-affiliate::tables.empty_state.description.affiliate_commissions'))
            ->emptyStateIcon(Heroicon::OutlinedReceiptPercent)
            ->recordActions([
                ActionGroup::make([
                    ApproveCommissionTableAction::make(),

                    ReverseCommissionTableAction::make(),

                    ViewAction::make(),
                ]),
            ])
            ->defaultSort(column: 'id', direction: 'desc');
    }
}
