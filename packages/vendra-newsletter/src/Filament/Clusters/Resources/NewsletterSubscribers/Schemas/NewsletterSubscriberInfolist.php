<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter\Filament\Clusters\Resources\NewsletterSubscribers\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Misaf\VendraNewsletter\Models\NewsletterSubscriber;

final class NewsletterSubscriberInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('email')
                    ->copyable()
                    ->label(__('vendra-newsletter::attributes.email')),
                TextEntry::make('name')->label(__('vendra-newsletter::attributes.name')),
                IconEntry::make('subscribed')
                    ->boolean()
                    ->label(__('vendra-newsletter::attributes.active'))
                    ->state(fn (NewsletterSubscriber $record): bool => $record->isSubscribed()),
                self::dateEntry('subscribed_at'),
                self::dateEntry('unsubscribed_at'),
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
