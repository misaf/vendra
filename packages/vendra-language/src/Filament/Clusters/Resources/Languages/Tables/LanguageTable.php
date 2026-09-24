<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Filament\Clusters\Resources\Languages\Tables;

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
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Table;
use Misaf\VendraLanguage\Filament\Clusters\Resources\Languages\Actions\SetDefaultLanguageTableAction;
use Misaf\VendraLanguage\Filament\Clusters\Resources\Languages\LanguageResource;
use Misaf\VendraLanguage\Models\Language;
use Misaf\VendraLanguage\Support\Locales;
use Misaf\VendraLanguage\Support\TranslationProgress;
use Misaf\VendraSupport\Filament\Tables\Columns\CreatedAtColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\IsActiveToggleColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\IsDefaultIconColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\NameColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\RowIndexColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\UpdatedAtColumn;
use Misaf\VendraSupport\Filament\Tables\Filters\QueryBuilder\Constraints\IsActiveConstraint;
use Misaf\VendraSupport\Filament\Tables\Filters\QueryBuilder\Constraints\IsDefaultConstraint;
use Misaf\VendraSupport\Filament\Tables\Filters\QueryBuilder\Constraints\PositionConstraint;

final class LanguageTable
{
    public static function configure(Table $table): Table
    {
        /**
         * @var array<int, Column> $columns
         */
        $columns = [
            RowIndexColumn::make(),

            TextColumn::make('locale')
                ->badge()
                ->label(__('vendra-language::attributes.locale'))
                ->icon(Heroicon::GlobeAlt)
                ->searchable()
                ->sortable(),

            NameColumn::make()
                ->state(fn (Language $record): string => Locales::name($record->locale)),

            IsDefaultIconColumn::make(),

            IsActiveToggleColumn::make()
                ->disabled(fn (Language $record): bool => ! LanguageResource::canEdit($record)),

            TextColumn::make('translation_coverage')
                ->badge()
                ->color(function (Language $record, TranslationProgress $progress): string {
                    ['percentage' => $percentage, 'total' => $total] = $progress->forLocale($record->locale);

                    return static::progressColor($percentage, $total);
                })
                ->description(function (Language $record, TranslationProgress $progress): string {
                    ['percentage' => $percentage, 'remaining' => $remaining] = $progress->forLocale($record->locale);

                    return __('vendra-language::messages.coverage_summary', [
                        'percentage' => $percentage,
                        'remaining' => $remaining,
                    ]);
                })
                ->label(__('vendra-language::attributes.translation_coverage'))
                ->state(function (Language $record, TranslationProgress $progress): string {
                    ['translated' => $translated, 'total' => $total] = $progress->forLocale($record->locale);

                    return "{$translated} / {$total}";
                }),

            CreatedAtColumn::make(),

            UpdatedAtColumn::make(),
        ];

        return $table
            ->columns($columns)
            ->filters(
                [
                    QueryBuilder::make()
                        ->constraints([
                            IsActiveConstraint::make(),

                            IsDefaultConstraint::make(),

                            TextConstraint::make('locale')
                                ->label(__('vendra-language::attributes.locale')),

                            PositionConstraint::make(),
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
