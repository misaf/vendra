<?php

declare(strict_types=1);

namespace Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPages\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\Layout\Component as LayoutComponent;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\BooleanConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint\Operators\IsRelatedToOperator;
use Filament\Tables\Table;
use Livewire\Component as Livewire;
use Misaf\VendraCustomPage\Models\CustomPage;
use Misaf\VendraCustomPage\Models\CustomPageCategory;
use Misaf\VendraSupport\Filament\Concerns\HasDefaultAvatarImageUrl;
use Misaf\VendraSupport\Filament\Concerns\InteractsWithTranslatedTableRecords;

final class CustomPageTable
{
    use HasDefaultAvatarImageUrl;
    use InteractsWithTranslatedTableRecords;

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

            SpatieMediaLibraryImageColumn::make('image')
                ->alignCenter()
                ->collection(CustomPage::MEDIA_COLLECTION)
                ->conversion('thumb-table')
                ->defaultImageUrl(fn(CustomPage $record, Livewire $livewire): string => static::defaultAvatarImageUrl(static::translatedAttribute($record, 'name', $livewire)))
                ->extraImgAttributes(['class' => 'saturate-50', 'loading' => 'lazy'])
                ->label(__('vendra-custom-page::attributes.image'))
                ->stacked(),

            TextColumn::make('name')
                ->alignStart()
                ->label(__('vendra-custom-page::attributes.name'))
                ->icon(Heroicon::Tag),

            TextColumn::make('description')
                ->label(__('vendra-custom-page::attributes.description'))
                ->icon(Heroicon::DocumentText)
                ->state(fn (CustomPage $record, Livewire $livewire): string => self::translatedAttribute($record, 'description', $livewire))
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('slug')
                ->alignStart()
                ->label(__('vendra-custom-page::attributes.slug'))
                ->icon(Heroicon::Link)
                ->toggleable(isToggledHiddenByDefault: true),

            ToggleColumn::make('active')
                ->label(__('vendra-custom-page::attributes.active'))
                ->onIcon(Heroicon::Bolt),

            TextColumn::make('created_at')
                ->extraCellAttributes(['dir' => 'ltr'])
                ->label(__('vendra-custom-page::attributes.created_at'))
                ->sinceTooltip()
                ->when(
                    app()->isLocale('fa'),
                    fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                    fn (TextColumn $column) => $column->dateTime('Y-m-d H:i')
                ),

            TextColumn::make('updated_at')
                ->extraCellAttributes(['dir' => 'ltr'])
                ->label(__('vendra-custom-page::attributes.updated_at'))
                ->sinceTooltip()
                ->when(
                    app()->isLocale('fa'),
                    fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                    fn (TextColumn $column) => $column->dateTime('Y-m-d H:i')
                ),
        ];

        return $table
            ->columns($columns)
            ->description(__('vendra-custom-page::tables.description.custom_pages'))
            ->emptyStateHeading(__('vendra-custom-page::tables.empty_state.heading.custom_pages'))
            ->emptyStateDescription(__('vendra-custom-page::tables.empty_state.description.custom_pages'))
            ->emptyStateIcon(Heroicon::OutlinedDocument)
            ->filters(
                [
                    QueryBuilder::make()
                        ->constraints([
                            RelationshipConstraint::make('customPageCategory')
                                ->label(__('vendra-custom-page::navigation.custom_page_category'))
                                ->selectable(
                                    IsRelatedToOperator::make()
                                        ->getOptionLabelFromRecordUsing(fn(CustomPageCategory $record, Livewire $livewire) => static::translatedAttribute($record, 'name', $livewire))
                                        ->preload()
                                        ->searchable()
                                        ->titleAttribute('name'),
                                ),

                            BooleanConstraint::make('active')
                                ->label(__('vendra-custom-page::attributes.active')),

                            NumberConstraint::make('position'),
                        ]),
                ],
                layout: FiltersLayout::AboveContentCollapsible,
            )
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
            ->defaultSort(column: 'id', direction: 'desc')
            ->reorderable(column: 'position', direction: 'desc');
    }
}
