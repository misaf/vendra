<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Filament\Clusters\Resources\Languages\Tables;

use Illuminate\Support\Arr;
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
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Table;
use Misaf\VendraLanguage\Filament\Clusters\Resources\Languages\Actions\SetDefaultLanguageTableAction;
use Misaf\VendraLanguage\Filament\Clusters\Resources\Languages\LanguageResource;
use Misaf\VendraLanguage\Models\Language;
use Misaf\VendraLanguage\Support\Locales;
use Misaf\VendraLanguage\Support\TranslationProgress;

final class LanguageTable
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

            TextColumn::make('locale')
                ->badge()
                ->label(__('vendra-language::attributes.locale'))
                ->icon(Heroicon::GlobeAlt)
                ->searchable()
                ->sortable(),

            BadgeableColumn::make('name')
                ->label(__('vendra-language::attributes.name'))
                ->icon(Heroicon::Tag)
                ->state(fn (Language $record): string => Locales::name($record->locale))
                ->prefixBadges([
                    Badge::make('is_default')
                        ->label(__('vendra-language::attributes.is_default'))
                        ->color('success')
                        ->size(Size::ExtraSmall)
                        ->hidden(fn (Language $record): bool => ! $record->is_default),
                ]),

            ToggleColumn::make('active')
                ->label(__('vendra-language::attributes.active'))
                ->onIcon(Heroicon::Bolt)
                ->disabled(fn (Language $record): bool => ! LanguageResource::canEdit($record)),

            TextColumn::make('translation_coverage')
                ->badge()
                ->color(function (Language $record, TranslationProgress $progress): string {
                    $coverage = $progress->forLocale($record->locale);

                    return static::progressColor(Arr::get($coverage, 'percentage'), Arr::get($coverage, 'total'));
                })
                ->description(function (Language $record, TranslationProgress $progress): string {
                    $coverage = $progress->forLocale($record->locale);

                    return __('vendra-language::messages.coverage_summary', [
                        'percentage' => Arr::get($coverage, 'percentage'),
                        'remaining' => Arr::get($coverage, 'remaining'),
                    ]);
                })
                ->label(__('vendra-language::attributes.translation_coverage'))
                ->state(function (Language $record, TranslationProgress $progress): string {
                    $coverage = $progress->forLocale($record->locale);

                    return "{Arr::get($coverage, 'translated')} / {Arr::get($coverage, 'total')}";
                }),

            TextColumn::make('created_at')
                ->extraCellAttributes(['dir' => 'ltr'])
                ->label(__('vendra-language::attributes.created_at'))
                ->sinceTooltip()
                ->when(
                    app()->isLocale('fa'),
                    fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                    fn (TextColumn $column) => $column->dateTime('Y-m-d H:i')
                ),

            TextColumn::make('updated_at')
                ->extraCellAttributes(['dir' => 'ltr'])
                ->label(__('vendra-language::attributes.updated_at'))
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
                            BooleanConstraint::make('active')
                                ->label(__('vendra-language::attributes.active')),

                            BooleanConstraint::make('is_default')
                                ->label(__('vendra-language::attributes.is_default')),

                            TextConstraint::make('locale')
                                ->label(__('vendra-language::attributes.locale')),

                            NumberConstraint::make('position')
                                ->label(__('vendra-language::attributes.position')),
                        ]),
                ],
                layout: FiltersLayout::AboveContentCollapsible,
            )
            ->description(__('vendra-language::tables.description.languages'))
            ->emptyStateHeading(__('vendra-language::tables.empty_state.heading.languages'))
            ->emptyStateDescription(__('vendra-language::tables.empty_state.description.languages'))
            ->emptyStateIcon(Heroicon::OutlinedLanguage)
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),

                    EditAction::make(),

                    SetDefaultLanguageTableAction::make(),

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

    private static function progressColor(int $percentage, int $total): string
    {
        return match (true) {
            $total === 0 => 'gray',
            $percentage === 100 => 'success',
            $percentage > 0 => 'warning',
            default => 'danger',
        };
    }
}
