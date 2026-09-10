<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter\Filament\Clusters\Resources\Newsletters\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

final class NewsletterInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('subject')->label(__('vendra-newsletter::attributes.subject')),
                TextEntry::make('status')
                    ->badge()
                    ->label(__('vendra-newsletter::attributes.status')),
                TextEntry::make('content')
                    ->columnSpanFull()
                    ->html()
                    ->label(__('vendra-newsletter::attributes.content')),
                self::dateEntry('scheduled_at'),
                self::dateEntry('sent_at'),
                self::dateEntry('created_at'),
                self::dateEntry('updated_at'),
            ])
            ->columns(2);
    }

    private static function dateEntry(string $name): TextEntry
    {
        return TextEntry::make($name)
            ->label(__("vendra-newsletter::attributes.{$name}"))
            ->when(
                app()->isLocale('fa'),
                fn (TextEntry $entry): TextEntry => $entry->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                fn (TextEntry $entry): TextEntry => $entry->dateTime('Y-m-d H:i'),
            );
    }
}
