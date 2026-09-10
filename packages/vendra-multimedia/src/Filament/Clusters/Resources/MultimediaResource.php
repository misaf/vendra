<?php

declare(strict_types=1);

namespace Misaf\VendraMultimedia\Filament\Clusters\Resources;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Misaf\VendraMultimedia\Filament\Clusters\Resources\Pages\ListMultimedia;
use Misaf\VendraMultimedia\Filament\Clusters\Resources\Pages\ViewMultimedia;
use Misaf\VendraMultimedia\Filament\Clusters\Resources\Schemas\MultimediaInfolist;
use Misaf\VendraMultimedia\Filament\Clusters\Resources\Tables\MultimediaTable;
use Misaf\VendraMultimedia\Models\Multimedia;
use Misaf\VendraSupport\Filament\Clusters\ContentCluster;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;

final class MultimediaResource extends Resource
{
    protected static ?string $model = Multimedia::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?int $navigationSort = NavigationPriority::Multimedia->value;

    protected static ?string $recordTitleAttribute = 'file_name';

    protected static ?string $slug = 'multimedia';

    protected static ?string $cluster = ContentCluster::class;

    public static function getBreadcrumb(): string
    {
        return __('vendra-multimedia::navigation.multimedia');
    }

    public static function getModelLabel(): string
    {
        return __('vendra-multimedia::navigation.media_item');
    }

    public static function getNavigationLabel(): string
    {
        return __('vendra-multimedia::navigation.media_items');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-multimedia::navigation.media_items');
    }

    /**
     * @return array<int, string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['file_name', 'collection_name', 'mime_type'];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMultimedia::route('/'),
            'view' => ViewMultimedia::route('/{record}'),
        ];
    }

    public static function infolist(Schema $schema): Schema
    {
        return MultimediaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MultimediaTable::configure($table);
    }
}
