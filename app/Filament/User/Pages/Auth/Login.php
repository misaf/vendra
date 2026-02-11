<?php

declare(strict_types=1);

namespace App\Filament\User\Pages\Auth;

use App\Forms\Components\Turnstile;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;

final class Login extends \Filament\Auth\Pages\Login
{
    #[Locked]
    public bool $isTurnstileValidated = true;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getUsernameFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
                // $this->getTurnstileFormComponent(),
            ]);
    }

    #[On('turnstileStateUpdated')]
    public function setTurnstileValidated(): void
    {
        $this->isTurnstileValidated = true;
    }

    protected function getUsernameFormComponent(): Component
    {
        return TextInput::make('username')
            ->autocomplete()
            ->autofocus()
            ->extraAttributes(['dir' => 'ltr'])
            ->extraInputAttributes(['tabindex' => 1])
            ->label(__('user::attributes.username'))
            ->maxLength(12)
            ->minLength(3)
            ->required()
            ->string();
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.username' => __('filament-panels::auth/pages/login.messages.failed'),
        ]);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'username' => (string) $data['username'],
            'password' => (string) $data['password'],
        ];
    }

    private function getTurnstileFormComponent(): Turnstile
    {
        return Turnstile::make('turnstileToken');
    }

    protected function getAuthenticateFormAction(): Action
    {
        return Action::make('authenticate')
            ->disabled(fn() => ! $this->isTurnstileValidated)
            ->label(__('filament-panels::auth/pages/login.form.actions.authenticate.label'))
            ->submit('authenticate');
    }
}
