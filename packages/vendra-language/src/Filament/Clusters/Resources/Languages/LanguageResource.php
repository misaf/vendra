<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Filament\Clusters\Resources\Languages;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Misaf\VendraLanguage\Filament\Clusters\Resources\Languages\Pages\CreateLanguage;
use Misaf\VendraLanguage\Filament\Clusters\Resources\Languages\Pages\EditLanguage;
use Misaf\VendraLanguage\Filament\Clusters\Resources\Languages\Pages\ListLanguages;
use Misaf\VendraLanguage\Filament\Clusters\Resources\Languages\Pages\ViewLanguage;
use Misaf\VendraLanguage\Filament\Clusters\Resources\Languages\Schemas\LanguageForm;
use Misaf\VendraLanguage\Filament\Clusters\Resources\Languages\Schemas\LanguageInfolist;
use Misaf\VendraLanguage\Filament\Clusters\Resources\Languages\Tables\LanguageTable;
use Misaf\VendraLanguage\Models\Language;
use Misaf\VendraSupport\Filament\Clusters\LocalizationCluster;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;

final class LanguageResource extends Resource
{
    protected static ?string $model = Language::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLanguage;

    protected static ?int $navigationSort = NavigationPriority::Languages->value;

    protected static ?string $recordTitleAttribute = 'locale';

    protected static ?string $slug = 'languages';

    protected static ?string $cluster = LocalizationCluster::class;

    public static function getBreadcrumb(): string
    {
        return __('vendra-language::navigation.language');
    }

    public static function getModelLabel(): string
    {
        return __('vendra-language::navigation.language');
    }

    public static function getNavigationLabel(): string
    {
        return __('vendra-language::navigation.languages');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-language::navigation.languages');
    }

    /**
     * @return array<int, string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['locale'];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLanguages::route('/'),
            'create' => CreateLanguage::route('/create'),
            'view' => ViewLanguage::route('/{record}'),
            'edit' => EditLanguage::route('/{record}/edit'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return LanguageForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LanguageInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LanguageTable::configure($table);
    }
}
