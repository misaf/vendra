<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter\Filament\Clusters\Resources\Newsletters\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Misaf\VendraSupport\Filament\Infolists\Components\CreatedAtEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\DateTimeEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\UpdatedAtEntry;

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
                DateTimeEntry::make('scheduled_at')
                    ->label(__('vendra-newsletter::attributes.scheduled_at')),
                DateTimeEntry::make('sent_at')
                    ->label(__('vendra-newsletter::attributes.sent_at')),
                CreatedAtEntry::make(),
                UpdatedAtEntry::make(),
            ])
            ->columns(2);
    }
}
