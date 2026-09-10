<?php

declare(strict_types=1);

namespace Misaf\VendraActivityLog\Filament\Clusters\Resources;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Misaf\VendraActivityLog\Filament\Clusters\Resources\Pages\ListActivityLogs;
use Misaf\VendraActivityLog\Filament\Clusters\Resources\Pages\ViewActivityLog;
use Misaf\VendraActivityLog\Filament\Clusters\Resources\Schemas\ActivityLogInfolist;
use Misaf\VendraActivityLog\Filament\Clusters\Resources\Tables\ActivityLogTable;
use Misaf\VendraActivityLog\Models\ActivityLog;
use Misaf\VendraSupport\Filament\Clusters\SystemCluster;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;

final class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?int $navigationSort = NavigationPriority::ActivityLogs->value;

    protected static ?string $recordTitleAttribute = 'log_name';

    protected static ?string $slug = 'activity-logs';

    protected static ?string $cluster = SystemCluster::class;

    public static function getBreadcrumb(): string
    {
        return __('vendra-activity-log::navigation.activity_log');
    }

    public static function getModelLabel(): string
    {
        return __('vendra-activity-log::navigation.activity_log');
    }

    public static function getNavigationLabel(): string
    {
        return __('vendra-activity-log::navigation.activity_logs');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-activity-log::navigation.activity_logs');
    }

    /**
     * @return array<int, string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['log_name', 'event'];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivityLogs::route('/'),
            'view' => ViewActivityLog::route('/{record}'),
        ];
    }

    public static function infolist(Schema $schema): Schema
    {
        return ActivityLogInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActivityLogTable::configure($table);
    }
}
