<?php

declare(strict_types=1);

namespace Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPageCategories;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;
use Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPageCategories\Pages\CreateCustomPageCategory;
use Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPageCategories\Pages\EditCustomPageCategory;
use Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPageCategories\Pages\ListCustomPageCategories;
use Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPageCategories\Pages\ViewCustomPageCategory;
use Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPageCategories\Schemas\CustomPageCategoryForm;
use Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPageCategories\Schemas\CustomPageCategoryInfolist;
use Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPageCategories\Tables\CustomPageCategoryTable;
use Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPages\RelationManagers\CustomPageRelationManager;
use Misaf\VendraCustomPage\Models\CustomPageCategory;
use Misaf\VendraSupport\Filament\Clusters\ContentCluster;
use Misaf\VendraSupport\Filament\Concerns\InteractsWithTranslatedGlobalSearch;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;

final class CustomPageCategoryResource extends Resource
{
    use InteractsWithTranslatedGlobalSearch;
    use Translatable;

    protected static ?string $model = CustomPageCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;

    protected static ?int $navigationSort = NavigationPriority::CustomPageCategories->value;

    protected static ?string $slug = 'custom-page-categories';

    protected static ?string $cluster = ContentCluster::class;

    /**
     * @return array<int, string>
     */
    protected static function translatableGlobalSearchAttributes(): array
    {
        return ['name', 'slug'];
    }

    public static function getBreadcrumb(): string
    {
        return __('vendra-custom-page::navigation.custom_page_category');
    }

    public static function getModelLabel(): string
    {
        return __('vendra-custom-page::navigation.custom_page_category');
    }

    public static function getNavigationLabel(): string
    {
        return __('vendra-custom-page::navigation.custom_page_categories');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-custom-page::navigation.custom_page_categories');
    }

    public static function getRelations(): array
    {
        return [
            CustomPageRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomPageCategories::route('/'),
            'create' => CreateCustomPageCategory::route('/create'),
            'view' => ViewCustomPageCategory::route('/{record}'),
            'edit' => EditCustomPageCategory::route('/{record}/edit'),
        ];
    }

    public static function getDefaultTranslatableLocale(): string
    {
        return app()->getLocale();
    }

    public static function form(Schema $schema): Schema
    {
        return CustomPageCategoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomPageCategoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomPageCategoryTable::configure($table);
    }
}
