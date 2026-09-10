<?php

declare(strict_types=1);

namespace App\Filament\Admin\Pages\Auth;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
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
            ->label(__('vendra-user::attributes.username'))
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

    /**
     * @param  array{username: string, password: string}  $data
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'username' => Arr::get($data, 'username'),
            'password' => Arr::get($data, 'password'),
        ];
    }

    protected function getAuthenticateFormAction(): Action
    {
        return Action::make('authenticate')
            ->disabled(fn () => ! $this->isTurnstileValidated)
            ->label(__('filament-panels::auth/pages/login.form.actions.authenticate.label'))
            ->submit('authenticate');
    }
}
