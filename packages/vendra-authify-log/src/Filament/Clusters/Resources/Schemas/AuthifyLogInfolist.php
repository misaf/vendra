<?php

declare(strict_types=1);

namespace Misaf\VendraAuthifyLog\Filament\Clusters\Resources\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

final class AuthifyLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.username')
                    ->label(__('vendra-authify-log::attributes.name')),

                TextEntry::make('action')
                    ->badge()
                    ->label(__('vendra-authify-log::attributes.action')),

                TextEntry::make('ip_address')
                    ->copyable()
                    ->label(__('vendra-authify-log::attributes.ip_address')),

                TextEntry::make('ip_country'),

                TextEntry::make('user_agent')
                    ->columnSpanFull(),

                self::dateEntry('created_at'),
                self::dateEntry('updated_at'),
            ])
            ->columns(2);
    }

    private static function dateEntry(string $name): TextEntry
    {
        return TextEntry::make($name)
            ->label(__("vendra-authify-log::attributes.{$name}"))
            ->when(
                app()->isLocale('fa'),
                fn (TextEntry $entry): TextEntry => $entry->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                fn (TextEntry $entry): TextEntry => $entry->dateTime('Y-m-d H:i'),
            );
    }
}
