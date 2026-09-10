<?php

declare(strict_types=1);

namespace Misaf\VendraUserProfile\Filament\Clusters\Resources\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

final class UserProfileInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.username')->label(__('vendra-user-profile::attributes.user')),
                TextEntry::make('name')->label(__('vendra-user-profile::attributes.name')),
                TextEntry::make('slug')->label(__('vendra-user-profile::attributes.slug')),
                TextEntry::make('description')
                    ->columnSpanFull()
                    ->label(__('vendra-user-profile::attributes.description')),
                IconEntry::make('is_default')
                    ->boolean()
                    ->label(__('vendra-user-profile::attributes.is_default')),
                IconEntry::make('active')
                    ->boolean()
                    ->label(__('vendra-user-profile::attributes.active')),
                self::dateEntry('created_at'),
                self::dateEntry('updated_at'),
            ])
            ->columns(2);
    }

    private static function dateEntry(string $name): TextEntry
    {
        return TextEntry::make($name)
            ->label(__("vendra-user-profile::attributes.{$name}"))
            ->when(
                app()->isLocale('fa'),
                fn (TextEntry $entry): TextEntry => $entry->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                fn (TextEntry $entry): TextEntry => $entry->dateTime('Y-m-d H:i'),
            );
    }
}
