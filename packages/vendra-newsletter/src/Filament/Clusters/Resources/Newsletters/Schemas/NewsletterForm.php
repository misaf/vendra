<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter\Filament\Clusters\Resources\Newsletters\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Livewire\Component as Livewire;
use Misaf\VendraNewsletter\Enums\NewsletterStatusEnum;

final class NewsletterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('subject')
                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.subject'))
                    ->autofocus()
                    ->columnSpanFull()
                    ->label(__('vendra-newsletter::attributes.subject'))
                    ->live(onBlur: true)
                    ->maxLength(255)
                    ->required(),

                Select::make('status')
                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.status'))
                    ->columnSpan(['lg' => 1])
                    ->default(NewsletterStatusEnum::Draft)
                    ->label(__('vendra-newsletter::attributes.status'))
                    ->live()
                    ->native(false)
                    ->options(NewsletterStatusEnum::class)
                    ->disableOptionWhen(fn (string $value): bool => NewsletterStatusEnum::Sent->value === $value)
                    ->required()
                    ->selectablePlaceholder(false),

                DateTimePicker::make('scheduled_at')
                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.scheduled_at'))
                    ->columnSpan(['lg' => 1])
                    ->label(__('vendra-newsletter::attributes.scheduled_at'))
                    ->live()
                    ->minDate(now())
                    ->required(fn (Get $get): bool => $get('status') === NewsletterStatusEnum::Scheduled)
                    ->seconds(false)
                    ->visible(fn (Get $get): bool => $get('status') === NewsletterStatusEnum::Scheduled),

                RichEditor::make('content')
                    ->columnSpanFull()
                    ->label(__('vendra-newsletter::attributes.content'))
                    ->required(),
            ]);
    }
}
