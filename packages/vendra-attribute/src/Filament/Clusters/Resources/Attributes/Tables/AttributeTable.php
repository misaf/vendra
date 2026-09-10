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
use Filament\Tables\Filters\QueryBuilder\Constraints\BooleanConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Table;
use Misaf\VendraAttribute\Models\Attribute;
use Misaf\VendraSupport\Capabilities\TagIntegration;

final class AttributeTable
{
    public static function configure(Table $table): Table
    {
        /**
         * @var array<int, TextColumn|ToggleColumn|SpatieTagsColumn> $columns
         */
        $columns = [
            TextColumn::make('row')
                ->label('#')
                ->rowIndex()
                ->sortable(['id']),

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

            ToggleColumn::make('active')
                ->label(__('vendra-attribute::attributes.active'))
                ->onIcon(Heroicon::Bolt),

            TextColumn::make('created_at')
                ->extraCellAttributes(['dir' => 'ltr'])
                ->label(__('vendra-attribute::attributes.created_at'))
                ->sinceTooltip()
                ->when(
                    app()->isLocale('fa'),
                    fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                    fn (TextColumn $column) => $column->dateTime('Y-m-d H:i')
                ),

            TextColumn::make('updated_at')
                ->extraCellAttributes(['dir' => 'ltr'])
                ->label(__('vendra-attribute::attributes.updated_at'))
                ->sinceTooltip()
                ->when(
                    app()->isLocale('fa'),
                    fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                    fn (TextColumn $column) => $column->dateTime('Y-m-d H:i')
                ),
        ];

        if (TagIntegration::isAvailable()) {
            $columns[] = SpatieTagsColumn::make('tags')
                ->label(__('vendra-support::attributes.tags'))
                ->type(Attribute::TAG_TYPE)
                ->toggleable();
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
                        BooleanConstraint::make('active'),
                        NumberConstraint::make('position'),
                    ]),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->reorderable('position', direction: 'desc');
    }
}
