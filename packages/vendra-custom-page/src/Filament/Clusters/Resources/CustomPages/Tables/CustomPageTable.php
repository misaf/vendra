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
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint\Operators\IsRelatedToOperator;
use Filament\Tables\Table;
use Livewire\Component as Livewire;
use Misaf\VendraCustomPage\Models\CustomPage;
use Misaf\VendraCustomPage\Models\CustomPageCategory;
use Misaf\VendraMultimedia\Filament\Tables\Columns\ModelImageColumn;
use Misaf\VendraSupport\Filament\Concerns\HasDefaultAvatarImageUrl;
use Misaf\VendraSupport\Filament\Concerns\InteractsWithTranslatedTableRecords;
use Misaf\VendraSupport\Filament\Tables\Columns\CreatedAtColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\DescriptionColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\IsActiveToggleColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\NameColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\RowIndexColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\SlugColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\UpdatedAtColumn;
use Misaf\VendraSupport\Filament\Tables\Filters\QueryBuilder\Constraints\IsActiveConstraint;
use Misaf\VendraSupport\Filament\Tables\Filters\QueryBuilder\Constraints\PositionConstraint;

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
            RowIndexColumn::make(),

            ModelImageColumn::make()
                ->collection(CustomPage::MEDIA_COLLECTION)
                ->defaultImageUrl(fn (CustomPage $record, Livewire $livewire): string => self::defaultAvatarImageUrl(self::translatedAttribute($record, 'name', $livewire))),

            NameColumn::make(),

            DescriptionColumn::make()
                ->state(fn (CustomPage $record, Livewire $livewire): string => self::translatedAttribute($record, 'description', $livewire)),

            SlugColumn::make(),

            IsActiveToggleColumn::make(),

            CreatedAtColumn::make(),

            UpdatedAtColumn::make(),
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
                                        ->getOptionLabelFromRecordUsing(fn (CustomPageCategory $record, Livewire $livewire) => self::translatedAttribute($record, 'name', $livewire))
                                        ->preload()
                                        ->searchable()
                                        ->titleAttribute('name'),
                                ),

                            IsActiveConstraint::make(),

                            PositionConstraint::make(),
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
