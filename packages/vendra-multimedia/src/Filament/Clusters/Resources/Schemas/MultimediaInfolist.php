<?php

declare(strict_types=1);

namespace Misaf\VendraMultimedia\Filament\Clusters\Resources\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Misaf\VendraMultimedia\Models\Multimedia;

final class MultimediaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ImageEntry::make('preview')
                    ->columnSpanFull()
                    ->label(__('vendra-multimedia::attributes.name'))
                    ->state(fn (Multimedia $record): string => $record->getUrl())
                    ->visible(fn (Multimedia $record): bool => Str::startsWith((string) $record->mime_type, 'image/')),

                TextEntry::make('model_type')
                    ->label(__('vendra-multimedia::attributes.model_type')),

                TextEntry::make('model_id')
                    ->label(__('vendra-multimedia::attributes.model_id')),

                TextEntry::make('uuid')
                    ->copyable()
                    ->label(__('vendra-multimedia::attributes.uuid')),

                TextEntry::make('collection_name')
                    ->label(__('vendra-multimedia::attributes.collection_name')),

                TextEntry::make('name')
                    ->label(__('vendra-multimedia::attributes.name')),

                TextEntry::make('file_name')
                    ->label(__('vendra-multimedia::attributes.file_name')),

                TextEntry::make('mime_type')
                    ->label(__('vendra-multimedia::attributes.mime_type')),

                TextEntry::make('disk')
                    ->label(__('vendra-multimedia::attributes.disk')),

                TextEntry::make('size')
                    ->formatStateUsing(fn (int $state): string => Number::fileSize($state))
                    ->label(__('vendra-multimedia::attributes.size')),

                TextEntry::make('order_column')
                    ->label(__('vendra-multimedia::attributes.order_column')),

                self::dateEntry('created_at'),
                self::dateEntry('updated_at'),
            ])
            ->columns(2);
    }

    private static function dateEntry(string $name): TextEntry
    {
        return TextEntry::make($name)
            ->label(__("vendra-multimedia::attributes.{$name}"))
            ->when(
                app()->isLocale('fa'),
                fn (TextEntry $entry): TextEntry => $entry->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                fn (TextEntry $entry): TextEntry => $entry->dateTime('Y-m-d H:i'),
            );
    }
}
