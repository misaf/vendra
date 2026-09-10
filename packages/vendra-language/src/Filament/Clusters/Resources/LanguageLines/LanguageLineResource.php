<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Filament\Clusters\Resources\LanguageLines;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Misaf\VendraLanguage\Filament\Clusters\Resources\LanguageLines\Pages\CreateLanguageLine;
use Misaf\VendraLanguage\Filament\Clusters\Resources\LanguageLines\Pages\EditLanguageLine;
use Misaf\VendraLanguage\Filament\Clusters\Resources\LanguageLines\Pages\ListLanguageLines;
use Misaf\VendraLanguage\Filament\Clusters\Resources\LanguageLines\Pages\ViewLanguageLine;
use Misaf\VendraLanguage\Filament\Clusters\Resources\LanguageLines\Schemas\LanguageLineForm;
use Misaf\VendraLanguage\Filament\Clusters\Resources\LanguageLines\Schemas\LanguageLineInfolist;
use Misaf\VendraLanguage\Filament\Clusters\Resources\LanguageLines\Tables\LanguageLineTable;
use Misaf\VendraLanguage\Models\LanguageLine;
use Misaf\VendraSupport\Filament\Clusters\LocalizationCluster;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;

final class LanguageLineResource extends Resource
{
    protected static ?string $model = LanguageLine::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleBottomCenterText;

    protected static ?int $navigationSort = NavigationPriority::LanguageLines->value;

    protected static ?string $recordTitleAttribute = 'key';

    protected static ?string $slug = 'language-lines';

    protected static ?string $cluster = LocalizationCluster::class;

    public static function getBreadcrumb(): string
    {
        return __('vendra-language::navigation.language_line');
    }

    public static function getModelLabel(): string
    {
        return __('vendra-language::navigation.language_line');
    }

    public static function getNavigationLabel(): string
    {
        return __('vendra-language::navigation.language_lines');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-language::navigation.language_lines');
    }

    /**
     * @return array<int, string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['namespace', 'group', 'key'];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLanguageLines::route('/'),
            'create' => CreateLanguageLine::route('/create'),
            'view' => ViewLanguageLine::route('/{record}'),
            'edit' => EditLanguageLine::route('/{record}/edit'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return LanguageLineForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LanguageLineInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LanguageLineTable::configure($table);
    }
}
