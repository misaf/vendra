<?php

declare(strict_types=1);

namespace Misaf\VendraAuthifyLog\Filament\Clusters\Resources\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Misaf\VendraSupport\Filament\Infolists\Components\CreatedAtEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\UpdatedAtEntry;

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

                CreatedAtEntry::make(),
                UpdatedAtEntry::make(),
            ])
            ->columns(2);
    }
}
