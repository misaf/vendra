<?php

declare(strict_types=1);

namespace Misaf\VendraPermission\Filament\Clusters\Resources\Roles\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

final class RoleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')->label(__('vendra-permission::attributes.name')),
                TextEntry::make('guard_name')
                    ->badge()
                    ->label(__('vendra-permission::attributes.guard_name')),
                TextEntry::make('permissions.name')
                    ->badge()
                    ->columnSpanFull()
                    ->label(__('vendra-permission::navigation.permissions')),
                TextEntry::make('description')
                    ->columnSpanFull()
                    ->label(__('vendra-permission::attributes.description')),
                self::dateEntry('created_at'),
                self::dateEntry('updated_at'),
            ])
            ->columns(2);
    }

    private static function dateEntry(string $name): TextEntry
    {
        return TextEntry::make($name)
            ->label(__("vendra-permission::attributes.{$name}"))
            ->when(
                app()->isLocale('fa'),
                fn (TextEntry $entry): TextEntry => $entry->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                fn (TextEntry $entry): TextEntry => $entry->dateTime('Y-m-d H:i'),
            );
    }
}
