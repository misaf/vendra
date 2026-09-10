<?php

declare(strict_types=1);

namespace Misaf\VendraInquiry\Filament\Clusters\Resources\Inquiries\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

final class InquiryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label(__('vendra-inquiry::attributes.name')),
                TextEntry::make('email')
                    ->copyable()
                    ->label(__('vendra-inquiry::attributes.email')),
                TextEntry::make('phone')
                    ->copyable()
                    ->label(__('vendra-inquiry::attributes.phone'))
                    ->placeholder('—'),
                TextEntry::make('occasion')
                    ->label(__('vendra-inquiry::attributes.occasion'))
                    ->placeholder('—'),
                TextEntry::make('status')
                    ->badge()
                    ->label(__('vendra-inquiry::attributes.status')),
                TextEntry::make('source')
                    ->label(__('vendra-inquiry::attributes.source'))
                    ->placeholder('—'),
                TextEntry::make('message')
                    ->columnSpanFull()
                    ->label(__('vendra-inquiry::attributes.message')),
                self::dateEntry('answered_at'),
                self::dateEntry('created_at'),
                self::dateEntry('updated_at'),
            ])
            ->columns(2);
    }

    private static function dateEntry(string $name): TextEntry
    {
        return TextEntry::make($name)
            ->label(__("vendra-inquiry::attributes.{$name}"))
            ->placeholder('—')
            ->when(
                app()->isLocale('fa'),
                fn (TextEntry $entry): TextEntry => $entry->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                fn (TextEntry $entry): TextEntry => $entry->dateTime('Y-m-d H:i'),
            );
    }
}
