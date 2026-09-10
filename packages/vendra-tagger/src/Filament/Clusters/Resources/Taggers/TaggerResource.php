<?php

declare(strict_types=1);

namespace Misaf\VendraTagger\Filament\Clusters\Resources\Taggers;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;
use Misaf\VendraSupport\Filament\Clusters\ContentCluster;
use Misaf\VendraSupport\Filament\Concerns\InteractsWithTranslatedGlobalSearch;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;
use Misaf\VendraTagger\Filament\Clusters\Resources\Taggers\Pages\CreateTagger;
use Misaf\VendraTagger\Filament\Clusters\Resources\Taggers\Pages\EditTagger;
use Misaf\VendraTagger\Filament\Clusters\Resources\Taggers\Pages\ListTaggers;
use Misaf\VendraTagger\Filament\Clusters\Resources\Taggers\Pages\ViewTagger;
use Misaf\VendraTagger\Filament\Clusters\Resources\Taggers\Schemas\TaggerForm;
use Misaf\VendraTagger\Filament\Clusters\Resources\Taggers\Schemas\TaggerInfolist;
use Misaf\VendraTagger\Filament\Clusters\Resources\Taggers\Tables\TaggerTable;
use Misaf\VendraTagger\Models\Tagger;

final class TaggerResource extends Resource
{
    use InteractsWithTranslatedGlobalSearch;
    use Translatable;

    protected static ?string $model = Tagger::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHashtag;

    protected static ?int $navigationSort = NavigationPriority::Tags->value;

    protected static ?string $slug = 'taggers';

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
        return __('vendra-tagger::navigation.tagger');
    }

    public static function getModelLabel(): string
    {
        return __('vendra-tagger::navigation.tagger');
    }

    public static function getNavigationLabel(): string
    {
        return __('vendra-tagger::navigation.taggers');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-tagger::navigation.taggers');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTaggers::route('/'),
            'create' => CreateTagger::route('/create'),
            'view' => ViewTagger::route('/{record}'),
            'edit' => EditTagger::route('/{record}/edit'),
        ];
    }

    public static function getDefaultTranslatableLocale(): string
    {
        return app()->getLocale();
    }

    public static function form(Schema $schema): Schema
    {
        return TaggerForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TaggerInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TaggerTable::configure($table);
    }
}
