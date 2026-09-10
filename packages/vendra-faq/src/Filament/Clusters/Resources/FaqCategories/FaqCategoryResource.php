<?php

declare(strict_types=1);

namespace Misaf\VendraFaq\Filament\Clusters\Resources\FaqCategories;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;
use Misaf\VendraFaq\Filament\Clusters\Resources\FaqCategories\Pages\CreateFaqCategory;
use Misaf\VendraFaq\Filament\Clusters\Resources\FaqCategories\Pages\EditFaqCategory;
use Misaf\VendraFaq\Filament\Clusters\Resources\FaqCategories\Pages\ListFaqCategories;
use Misaf\VendraFaq\Filament\Clusters\Resources\FaqCategories\Pages\ViewFaqCategory;
use Misaf\VendraFaq\Filament\Clusters\Resources\FaqCategories\Schemas\FaqCategoryForm;
use Misaf\VendraFaq\Filament\Clusters\Resources\FaqCategories\Schemas\FaqCategoryInfolist;
use Misaf\VendraFaq\Filament\Clusters\Resources\FaqCategories\Tables\FaqCategoryTable;
use Misaf\VendraFaq\Filament\Clusters\Resources\Faqs\RelationManagers\FaqRelationManager;
use Misaf\VendraFaq\Models\FaqCategory;
use Misaf\VendraSupport\Filament\Clusters\ContentCluster;
use Misaf\VendraSupport\Filament\Concerns\InteractsWithTranslatedGlobalSearch;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;

final class FaqCategoryResource extends Resource
{
    use InteractsWithTranslatedGlobalSearch;
    use Translatable;

    protected static ?string $model = FaqCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;

    protected static ?int $navigationSort = NavigationPriority::FaqCategories->value;

    protected static ?string $slug = 'faq-categories';

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
        return __('vendra-faq::navigation.faq_category');
    }

    public static function getModelLabel(): string
    {
        return __('vendra-faq::navigation.faq_category');
    }

    public static function getNavigationLabel(): string
    {
        return __('vendra-faq::navigation.faq_categories');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-faq::navigation.faq_categories');
    }

    public static function getRelations(): array
    {
        return [
            FaqRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFaqCategories::route('/'),
            'create' => CreateFaqCategory::route('/create'),
            'view' => ViewFaqCategory::route('/{record}'),
            'edit' => EditFaqCategory::route('/{record}/edit'),
        ];
    }

    public static function getDefaultTranslatableLocale(): string
    {
        return app()->getLocale();
    }

    public static function form(Schema $schema): Schema
    {
        return FaqCategoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FaqCategoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FaqCategoryTable::configure($table);
    }
}
