<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter\Filament\Clusters\Resources\NewsletterSubscribers\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Misaf\VendraNewsletter\Models\NewsletterSubscriber;
use Misaf\VendraSupport\Filament\Infolists\Components\CreatedAtEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\DateTimeEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\NameEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\UpdatedAtEntry;

final class NewsletterSubscriberInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('email')
                    ->copyable()
                    ->label(__('vendra-newsletter::attributes.email')),
                NameEntry::make(),
                IconEntry::make('subscribed')
                    ->boolean()
                    ->label(__('vendra-newsletter::attributes.active'))
                    ->state(fn (NewsletterSubscriber $record): bool => $record->isSubscribed()),
                DateTimeEntry::make('subscribed_at')
                    ->label(__('vendra-newsletter::attributes.subscribed_at')),
                DateTimeEntry::make('unsubscribed_at')
                    ->label(__('vendra-newsletter::attributes.unsubscribed_at')),
                CreatedAtEntry::make(),
                UpdatedAtEntry::make(),
            ])
            ->columns(2);
    }
}
