<?php

declare(strict_types=1);

namespace Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPages;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;
use Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPages\Pages\CreateCustomPage;
use Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPages\Pages\EditCustomPage;
use Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPages\Pages\ListCustomPages;
use Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPages\Pages\ViewCustomPage;
use Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPages\Schemas\CustomPageForm;
use Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPages\Schemas\CustomPageInfolist;
use Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPages\Tables\CustomPageTable;
use Misaf\VendraCustomPage\Models\CustomPage;
use Misaf\VendraSupport\Filament\Clusters\ContentCluster;
use Misaf\VendraSupport\Filament\Concerns\InteractsWithTranslatedGlobalSearch;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;

final class CustomPageResource extends Resource
{
    use InteractsWithTranslatedGlobalSearch;
    use Translatable;

    protected static ?string $model = CustomPage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocument;

    protected static ?int $navigationSort = NavigationPriority::CustomPages->value;

    protected static ?string $slug = 'custom-pages';

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
        return __('vendra-custom-page::navigation.custom_page');
    }

    public static function getModelLabel(): string
    {
        return __('vendra-custom-page::navigation.custom_page');
    }

    public static function getNavigationLabel(): string
    {
        return __('vendra-custom-page::navigation.custom_pages');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-custom-page::navigation.custom_pages');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomPages::route('/'),
            'create' => CreateCustomPage::route('/create'),
            'view' => ViewCustomPage::route('/{record}'),
            'edit' => EditCustomPage::route('/{record}/edit'),
        ];
    }

    public static function getDefaultTranslatableLocale(): string
    {
        return app()->getLocale();
    }

    public static function form(Schema $schema): Schema
    {
        return CustomPageForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomPageInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomPageTable::configure($table);
    }
}
