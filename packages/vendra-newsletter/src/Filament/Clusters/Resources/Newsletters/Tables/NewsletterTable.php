<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter\Filament\Clusters\Resources\Newsletters\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Misaf\VendraNewsletter\Enums\NewsletterStatusEnum;
use Misaf\VendraNewsletter\Filament\Clusters\Resources\Newsletters\Actions\SendNewsletterTableAction;

final class NewsletterTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('row')
                    ->label('#')
                    ->rowIndex()
                    ->sortable(['id']),

                TextColumn::make('subject')
                    ->alignStart()
                    ->label(__('vendra-newsletter::attributes.subject'))
                    ->limit(60)
                    ->searchable(),

                TextColumn::make('status')
                    ->alignCenter()
                    ->badge()
                    ->label(__('vendra-newsletter::attributes.status')),

                TextColumn::make('scheduled_at')
                    ->alignCenter()
                    ->badge()
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-newsletter::attributes.scheduled_at'))
                    ->placeholder('—')
                    ->toggleable()
                    ->when(
                        app()->isLocale('fa'),
                        fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                        fn (TextColumn $column) => $column->dateTime('Y-m-d H:i')
                    ),

                TextColumn::make('sent_at')
                    ->alignCenter()
                    ->badge()
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-newsletter::attributes.sent_at'))
                    ->placeholder('—')
                    ->toggleable()
                    ->when(
                        app()->isLocale('fa'),
                        fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                        fn (TextColumn $column) => $column->dateTime('Y-m-d H:i')
                    ),

                TextColumn::make('created_at')
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-newsletter::attributes.created_at'))
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
                        ->label(__('vendra-newsletter::attributes.status'))
                        ->options(NewsletterStatusEnum::class),
                    QueryBuilder::make()
                        ->constraints([
                            DateConstraint::make('scheduled_at')
                                ->label(__('vendra-newsletter::attributes.scheduled_at')),
                        ]),
                ],
                layout: FiltersLayout::AboveContentCollapsible,
            )
            ->description(__('vendra-newsletter::tables.description.newsletters'))
            ->emptyStateHeading(__('vendra-newsletter::tables.empty_state.heading.newsletters'))
            ->emptyStateDescription(__('vendra-newsletter::tables.empty_state.description.newsletters'))
            ->emptyStateIcon(Heroicon::OutlinedEnvelope)
            ->recordActions([
                SendNewsletterTableAction::make(),

                ActionGroup::make([
                    ViewAction::make(),

                    EditAction::make(),

                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort(column: 'id', direction: 'desc');
    }
}
