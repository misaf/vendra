<?php

declare(strict_types=1);

namespace Misaf\VendraActivityLog\Filament\Clusters\Resources\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Misaf\VendraActivityLog\Models\ActivityLog;
use Misaf\VendraSupport\Filament\Infolists\Components\CreatedAtEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\UpdatedAtEntry;

final class ActivityLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('event')
                    ->badge()
                    ->label(__('vendra-activity-log::attributes.event')),

                TextEntry::make('log_name')
                    ->label(__('vendra-activity-log::attributes.log_name')),

                TextEntry::make('description')
                    ->columnSpanFull(),

                TextEntry::make('subject_type')
                    ->label(__('vendra-activity-log::attributes.subject_type')),

                TextEntry::make('subject_id')
                    ->label(__('vendra-activity-log::attributes.subject_id')),

                TextEntry::make('causer_type')
                    ->label(__('vendra-activity-log::attributes.causer_type')),

                TextEntry::make('causer_id')
                    ->label(__('vendra-activity-log::attributes.causer_id')),

                KeyValueEntry::make('attribute_changes')
                    ->label(__('vendra-activity-log::attributes.attribute_changes'))
                    ->columnSpanFull()
                    ->state(fn (ActivityLog $record): array => $record->attribute_changes?->all() ?? []),

                KeyValueEntry::make('properties')
                    ->columnSpanFull()
                    ->state(fn (ActivityLog $record): array => $record->properties?->all() ?? []),

                CreatedAtEntry::make(),
                UpdatedAtEntry::make(),
            ])
            ->columns(2);
    }
}
