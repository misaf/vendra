<?php

declare(strict_types=1);

namespace App\Filament\User\Pages\Auth\PasswordReset;

use App\Forms\Components\Turnstile;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component as Livewire;
use Misaf\VendraUser\Rules\EmailValidation;

final class RequestPasswordReset extends \Filament\Auth\Pages\PasswordReset\RequestPasswordReset
{
    #[Locked]
    public bool $isTurnstileValidated = false;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getEmailFormComponent(),
                $this->getTurnstileFormComponent(),
            ]);
    }

    #[On('turnstileStateUpdated')]
    public function setTurnstileValidated(): void
    {
        $this->isTurnstileValidated = true;
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->afterStateUpdated(fn(Livewire $livewire) => $livewire->validateOnly('data.email'))
            ->autocomplete()
            ->autofocus()
            ->extraAttributes(['dir' => 'ltr'])
            ->label(__('filament-panels::auth/pages/password-reset/request-password-reset.form.email.label'))
            ->live(onBlur: true)
            ->maxLength(255)
            ->required()
            ->rules(['bail', 'email:rfc,strict,spoof,filter,filter_unicode', new EmailValidation(app()->isProduction())]);
    }

    private function getTurnstileFormComponent(): Turnstile
    {
        return Turnstile::make('turnstileToken');
    }

    protected function getRequestFormAction(): Action
    {
        return Action::make('request')
            ->disabled(fn() => ! $this->isTurnstileValidated)
            ->label(__('filament-panels::auth/pages/password-reset/request-password-reset.form.actions.request.label'))
            ->submit('request');
    }
}
