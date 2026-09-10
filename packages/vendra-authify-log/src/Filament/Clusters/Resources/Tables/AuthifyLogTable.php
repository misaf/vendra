<?php

declare(strict_types=1);

namespace Misaf\VendraAuthifyLog\Filament\Clusters\Resources\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\QueryBuilder\Constraints\SelectConstraint;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\Layout\Component as LayoutComponent;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint\Operators\IsRelatedToOperator;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Misaf\LaravelAuthifyLog\Enums\AuthifyLogActionEnum;
use Misaf\VendraAuthifyLog\Models\AuthifyLog;

final class AuthifyLogTable
{
    public static function configure(Table $table): Table
    {
        /**
         * @var array<int, Column|ColumnGroup|LayoutComponent> $columns
         */
        $columns = [
            TextColumn::make('row')
                ->label('#')
                ->rowIndex()
                ->sortable(['id']),

            TextColumn::make('ip_address')
                ->alignCenter()
                ->copyable()
                ->copyMessage(__('vendra-authify-log::messages.ip_address_copied'))
                ->copyMessageDuration(1500)
                ->extraCellAttributes(['dir' => 'ltr'])
                ->formatStateUsing(fn (string $state, AuthifyLog $record): HtmlString => new HtmlString(
                    '<span class="flex items-center space-x-2">'
                    .'<img src="'.e(asset('vendor/blade-country-flags/4x3-'.Str::lower($record->ip_country).'.svg')).'" alt="'.e($record->ip_country).'" title="'.e($record->ip_country).'" class="w-4 inline-block" />'
                    .'<span>'.e($state).'</span>'
                    .'</span>',
                ))
                ->label(__('vendra-authify-log::attributes.ip_address'))
                ->searchable(),

            TextColumn::make('created_at')
                ->extraCellAttributes(['dir' => 'ltr'])
                ->label(__('vendra-authify-log::attributes.created_at'))
                ->sinceTooltip()
                ->when(
                    app()->isLocale('fa'),
                    fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                    fn (TextColumn $column) => $column->dateTime('Y-m-d H:i')
                ),

            TextColumn::make('updated_at')
                ->extraCellAttributes(['dir' => 'ltr'])
                ->label(__('vendra-authify-log::attributes.updated_at'))
                ->sinceTooltip()
                ->when(
                    app()->isLocale('fa'),
                    fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                    fn (TextColumn $column) => $column->dateTime('Y-m-d H:i')
                ),
        ];

        return $table
            ->columns($columns)
            ->description(__('vendra-authify-log::tables.description.authify_logs'))
            ->emptyStateHeading(__('vendra-authify-log::tables.empty_state.heading.authify_logs'))
            ->emptyStateDescription(__('vendra-authify-log::tables.empty_state.description.authify_logs'))
            ->emptyStateIcon(Heroicon::OutlinedShieldCheck)
            ->filters(
                [
                    QueryBuilder::make()
                        ->constraints([
                            RelationshipConstraint::make('user')
                                ->selectable(
                                    IsRelatedToOperator::make()
                                        ->preload()
                                        ->searchable()
                                        ->titleAttribute('username'),
                                ),
                            SelectConstraint::make('action')
                                ->options(AuthifyLogActionEnum::class)
                                ->multiple(),
                            TextConstraint::make('ip_address'),
                            DateConstraint::make('created_at'),
                            DateConstraint::make('updated_at'),
                        ]),
                ],
                layout: FiltersLayout::AboveContentCollapsible,
            )
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                ]),
            ])
            ->defaultSort(column: 'id', direction: 'desc');
    }
}
