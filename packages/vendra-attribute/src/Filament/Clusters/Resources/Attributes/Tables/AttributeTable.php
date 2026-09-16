<?php

declare(strict_types=1);

namespace Misaf\VendraAttribute\Filament\Clusters\Resources\Attributes\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SpatieTagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Table;
use Misaf\VendraAttribute\Models\Attribute;
use Misaf\VendraSupport\Capabilities\TagIntegration;
use Misaf\VendraSupport\Filament\Tables\Columns\CreatedAtColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\IsActiveToggleColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\RowIndexColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\UpdatedAtColumn;
use Misaf\VendraSupport\Filament\Tables\Filters\QueryBuilder\Constraints\IsActiveConstraint;
use Misaf\VendraTagger\Filament\Tables\Columns\ModelTagsColumn;

final class AttributeTable
{
    public static function configure(Table $table): Table
    {
        /**
         * @var array<int, TextColumn|ToggleColumn|SpatieTagsColumn> $columns
         */
        $columns = [
            RowIndexColumn::make(),

            TextColumn::make('name')
                ->label(__('vendra-attribute::attributes.name'))
                ->icon(Heroicon::Tag)
                ->searchable()
                ->sortable(),

            TextColumn::make('description')
                ->label(__('vendra-attribute::attributes.description'))
                ->icon(Heroicon::DocumentText)
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('unit')
                ->badge()
                ->label(__('vendra-attribute::attributes.unit')),

            TextColumn::make('values_count')
                ->badge()
                ->counts('values')
                ->label(__('vendra-attribute::attributes.values')),

            IsActiveToggleColumn::make(),

            CreatedAtColumn::make(),

            UpdatedAtColumn::make(),
        ];

        if (TagIntegration::isAvailable()) {
            $columns[] = ModelTagsColumn::make()
                ->type(Attribute::TAG_TYPE);
        }

        return $table
            ->columns($columns)
            ->description(__('vendra-attribute::tables.description.attributes'))
            ->emptyStateHeading(__('vendra-attribute::tables.empty_state.heading.attributes'))
            ->emptyStateDescription(__('vendra-attribute::tables.empty_state.description.attributes'))
            ->emptyStateIcon(Heroicon::OutlinedAdjustmentsHorizontal)
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
            ->filters([
                QueryBuilder::make()
                    ->constraints([
                        TextConstraint::make('name'),
                        TextConstraint::make('unit'),
                        IsActiveConstraint::make(),
                        NumberConstraint::make('position'),
                    ]),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->reorderable('position', direction: 'desc');
    }
}
