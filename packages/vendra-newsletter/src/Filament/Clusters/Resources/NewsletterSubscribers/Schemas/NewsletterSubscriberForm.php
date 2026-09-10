<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter\Filament\Clusters\Resources\NewsletterSubscribers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;
use Livewire\Component as Livewire;
use Misaf\VendraSupport\Tenancy\TenantAwareness;

final class NewsletterSubscriberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.email'))
                    ->autofocus()
                    ->columnSpan(['lg' => 1])
                    ->email()
                    ->label(__('vendra-newsletter::attributes.email'))
                    ->live(onBlur: true)
                    ->maxLength(255)
                    ->required()
                    ->unique(
                        modifyRuleUsing: fn (Unique $rule): Unique => TenantAwareness::constrainUniqueRule($rule)
                            ->withoutTrashed(),
                    ),

                TextInput::make('name')
                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.name'))
                    ->columnSpan(['lg' => 1])
                    ->label(__('vendra-newsletter::attributes.name'))
                    ->live(onBlur: true)
                    ->maxLength(255),
            ]);
    }
}
