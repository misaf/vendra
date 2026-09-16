<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Filament\Clusters\Resources\LanguageLines\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Misaf\VendraSupport\Filament\Infolists\Components\CreatedAtEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\UpdatedAtEntry;

final class LanguageLineInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('namespace')->label(__('vendra-language::attributes.namespace')),
                TextEntry::make('group')->label(__('vendra-language::attributes.group')),
                TextEntry::make('key')->label(__('vendra-language::attributes.key')),
                KeyValueEntry::make('text')
                    ->columnSpanFull()
                    ->label(__('vendra-language::attributes.text')),
                CreatedAtEntry::make(),
                UpdatedAtEntry::make(),
            ])
            ->columns(2);
    }
}
