<?php

declare(strict_types=1);

namespace Misaf\VendraBlog\Filament\Clusters\Resources\BlogPosts\Tables;

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
use Filament\Tables\Columns\SpatieTagsColumn;
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
use Misaf\VendraBlog\Models\BlogPost;
use Misaf\VendraBlog\Models\BlogPostCategory;
use Misaf\VendraSupport\Capabilities\TagIntegration;
use Misaf\VendraSupport\Filament\Concerns\HasDefaultAvatarImageUrl;
use Misaf\VendraSupport\Filament\Concerns\InteractsWithTranslatedTableRecords;

final class BlogPostTable
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
                ->collection(BlogPost::MEDIA_COLLECTION)
                ->conversion('thumb-table')
                ->defaultImageUrl(fn(BlogPost $record, Livewire $livewire): string => static::defaultAvatarImageUrl(static::translatedAttribute($record, 'name', $livewire)))
                ->extraImgAttributes(['class' => 'saturate-50', 'loading' => 'lazy'])
                ->label(__('vendra-blog::attributes.image'))
                ->stacked(),

            TextColumn::make('name')
                ->alignStart()
                ->label(__('vendra-blog::attributes.name'))
                ->icon(Heroicon::Tag),

            TextColumn::make('description')
                ->label(__('vendra-blog::attributes.description'))
                ->icon(Heroicon::DocumentText)
                ->state(fn (BlogPost $record, Livewire $livewire): string => self::translatedAttribute($record, 'description', $livewire))
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('slug')
                ->alignStart()
                ->label(__('vendra-blog::attributes.slug'))
                ->icon(Heroicon::Link)
                ->toggleable(isToggledHiddenByDefault: true),

            ToggleColumn::make('active')
                ->label(__('vendra-blog::attributes.active'))
                ->onIcon(Heroicon::Bolt),

            TextColumn::make('created_at')
                ->extraCellAttributes(['dir' => 'ltr'])
                ->label(__('vendra-blog::attributes.created_at'))
                ->sinceTooltip()
                ->when(
                    app()->isLocale('fa'),
                    fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                    fn (TextColumn $column) => $column->dateTime('Y-m-d H:i')
                ),

            TextColumn::make('updated_at')
                ->extraCellAttributes(['dir' => 'ltr'])
                ->label(__('vendra-blog::attributes.updated_at'))
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
                ->type(BlogPost::TAG_TYPE)
                ->toggleable();
        }

        return $table
            ->description(__('vendra-blog::tables.description.blog_posts'))
            ->emptyStateHeading(__('vendra-blog::tables.empty_state.heading.blog_posts'))
            ->emptyStateDescription(__('vendra-blog::tables.empty_state.description.blog_posts'))
            ->emptyStateIcon(Heroicon::OutlinedDocumentText)
            ->columns($columns)
            ->filters(
                [
                    QueryBuilder::make()
                        ->constraints([
                            RelationshipConstraint::make('blogPostCategory')
                                ->label(__('vendra-blog::navigation.blog_post_category'))
                                ->selectable(
                                    IsRelatedToOperator::make()
                                        ->getOptionLabelFromRecordUsing(fn(BlogPostCategory $record, Livewire $livewire) => static::translatedAttribute($record, 'name', $livewire))
                                        ->preload()
                                        ->searchable()
                                        ->titleAttribute('name'),
                                ),

                            BooleanConstraint::make('active')
                                ->label(__('vendra-blog::attributes.active')),

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
