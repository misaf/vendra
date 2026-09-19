<?php

declare(strict_types=1);

namespace Misaf\VendraInquiry\Filament\Clusters\Resources\Inquiries\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Misaf\VendraInquiry\States\InquiryState;

final class InquiryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('vendra-inquiry::navigation.inquiry'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('vendra-inquiry::attributes.name')),

                        TextInput::make('email')
                            ->email()
                            ->label(__('vendra-inquiry::attributes.email')),

                        TextInput::make('phone')
                            ->label(__('vendra-inquiry::attributes.phone')),

                        TextInput::make('occasion')
                            ->label(__('vendra-inquiry::attributes.occasion')),

                        Select::make('status')
                            ->label(__('vendra-inquiry::attributes.status'))
                            ->options(InquiryState::options()),

                        Textarea::make('message')
                            ->columnSpanFull()
                            ->label(__('vendra-inquiry::attributes.message'))
                            ->rows(6),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
