<?php

declare(strict_types=1);

namespace Misaf\VendraInquiry\Filament\Clusters\Resources\Inquiries\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Misaf\VendraSupport\Filament\Infolists\Components\CreatedAtEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\DateTimeEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\NameEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\UpdatedAtEntry;

final class InquiryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                NameEntry::make(),
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
                DateTimeEntry::make('answered_at')
                    ->label(__('vendra-inquiry::attributes.answered_at'))
                    ->placeholder('—'),
                CreatedAtEntry::make(),
                UpdatedAtEntry::make(),
            ])
            ->columns(2);
    }
}
