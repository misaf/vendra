<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Filament\Clusters\Resources\LanguageLines\Tables;

use Illuminate\Support\Arr;
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
use Misaf\VendraLanguage\Models\LanguageLine;
use Misaf\VendraLanguage\Support\TranslationProgress;

final class LanguageLineTable
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

            TextColumn::make('namespace')
                ->alignStart()
                ->badge()
                ->label(__('vendra-language::attributes.namespace'))
                ->icon(Heroicon::Cube)
                ->placeholder('—')
                ->searchable()
                ->sortable(),

            TextColumn::make('group')
                ->alignStart()
                ->badge()
                ->label(__('vendra-language::attributes.group'))
                ->icon(Heroicon::Folder)
                ->searchable()
                ->sortable(),

            TextColumn::make('key')
                ->alignStart()
                ->label(__('vendra-language::attributes.key'))
                ->searchable()
                ->sortable(),

            TextColumn::make('text')
                ->alignStart()
                ->label(__('vendra-language::attributes.text'))
                ->state(fn (LanguageLine $record): ?string => $record->getTranslation(app()->getLocale())),

            TextColumn::make('translation_progress')
                ->badge()
                ->color(function (LanguageLine $record, TranslationProgress $progress): string {
                    $coverage = $progress->forLanguageLine($record);

                    return static::progressColor(Arr::get($coverage, 'percentage'), Arr::get($coverage, 'total'));
                })
                ->description(function (LanguageLine $record, TranslationProgress $progress): string {
                    $coverage = $progress->forLanguageLine($record);

                    return __('vendra-language::messages.coverage_summary', [
                        'percentage' => Arr::get($coverage, 'percentage'),
                        'remaining' => Arr::get($coverage, 'remaining'),
                    ]);
                })
                ->label(__('vendra-language::attributes.translation_progress'))
                ->state(function (LanguageLine $record, TranslationProgress $progress): string {
                    $coverage = $progress->forLanguageLine($record);

                    return "{Arr::get($coverage, 'translated')} / {Arr::get($coverage, 'total')}";
                })
                ->tooltip(function (LanguageLine $record, TranslationProgress $progress): ?string {
                    $missingLocales = Arr::get($progress->forLanguageLine($record), 'missing_locales');

                    if ($missingLocales === []) {
                        return null;
                    }

                    return __('vendra-language::messages.missing_locales', [
                        'locales' => implode(', ', $missingLocales),
                    ]);
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
                            TextConstraint::make('namespace')
                                ->label(__('vendra-language::attributes.namespace')),

                            TextConstraint::make('group')
                                ->label(__('vendra-language::attributes.group')),

                            TextConstraint::make('key')
                                ->label(__('vendra-language::attributes.key')),
                        ]),
                ],
                layout: FiltersLayout::AboveContentCollapsible,
            )
            ->description(__('vendra-language::tables.description.language_lines'))
            ->emptyStateHeading(__('vendra-language::tables.empty_state.heading.language_lines'))
            ->emptyStateDescription(__('vendra-language::tables.empty_state.description.language_lines'))
            ->emptyStateIcon(Heroicon::OutlinedChatBubbleBottomCenterText)
            ->recordActions([
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
