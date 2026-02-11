<?php

declare(strict_types=1);

namespace App\Filament\User\Pages\Auth;

use App\Forms\Components\Turnstile;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rules\Unique;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component as Livewire;
use Misaf\Affiliate\Models\Affiliate;
use Misaf\Tenant\Models\Tenant;
use Misaf\VendraUser\Models\User;
use Misaf\VendraUser\Rules\EmailValidation;

final class Register extends \Filament\Auth\Pages\Register
{
    #[Locked]
    public ?string $affiliate = null;

    #[Locked]
    public bool $isTurnstileValidated = false;

    protected function beforeFill(): void
    {
        $this->affiliate = $affiliate ?? request()->query('affiliate');
    }

    #[On('turnstileStateUpdated')]
    public function setTurnstileValidated(): void
    {
        $this->isTurnstileValidated = true;
    }

    protected function afterRegister(): void
    {
        if ($this->affiliate) {
            $affiliate = Affiliate::query()
                ->where('slug', $this->affiliate)
                ->where('status', true)
                ->first();

            if ( ! $affiliate) {
                return;
            }

            $affiliate->affiliateUsers()->create([
                'user_id' => $this->form->getRecord()->id,
            ]);
        }
    }

    /**
     * @return array<int|string, string|Schema>
     */
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->components([
                        $this->getUsernameFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                        $this->getTurnstileFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getUsernameFormComponent(): \Filament\Schemas\Components\Component
    {
        return TextInput::make('username')
            ->afterStateUpdated(fn(Livewire $livewire) => $livewire->validateOnly('data.username'))
            ->autocomplete()
            ->autofocus()
            ->extraAttributes(['dir' => 'ltr'])
            ->hint('Letters, dashes, and underscores are allowed')
            ->label(__('form.username'))
            ->live(onBlur: true)
            ->maxLength(12)
            ->minLength(3)
            ->required()
            ->rules('alpha_dash:ascii')
            ->unique(
                table: User::class,
                modifyRuleUsing: fn(Unique $rule) => $rule->withoutTrashed(),
            );
    }

    protected function getEmailFormComponent(): \Filament\Schemas\Components\Component
    {
        return TextInput::make('email')
            ->afterStateUpdated(fn(Livewire $livewire) => $livewire->validateOnly('data.email'))
            ->extraAttributes(['dir' => 'ltr'])
            ->label(__('form.email'))
            ->live(onBlur: true)
            ->maxLength(255)
            ->required()
            ->rules(['bail', 'email:rfc,strict,spoof,filter,filter_unicode', new EmailValidation(app()->isProduction())])
            ->unique(
                table: User::class,
                modifyRuleUsing: function (Unique $rule): void {
                    $rule->where('tenant_id', Tenant::current()->id)
                        ->withoutTrashed();
                },
            );
    }

    protected function getPasswordFormComponent(): \Filament\Schemas\Components\Component
    {
        return TextInput::make('password')
            ->afterStateUpdated(fn(Livewire $livewire) => $livewire->validateOnly('data.password'))
            ->autocomplete()
            ->dehydrateStateUsing(fn($state) => Hash::make($state))
            ->extraAttributes(['dir' => 'ltr'])
            ->label(__('filament-panels::pages/auth/register.form.password.label'))
            ->live(onBlur: true)
            ->password()
            ->required()
            ->revealable(filament()->arePasswordsRevealable())
            ->rule(Password::default())
            ->same('passwordConfirmation')
            ->validationAttribute(__('filament-panels::pages/auth/register.form.password.validation_attribute'));
    }

    protected function getPasswordConfirmationFormComponent(): \Filament\Schemas\Components\Component
    {
        return TextInput::make('passwordConfirmation')
            ->autocomplete()
            ->dehydrated(false)
            ->extraAttributes(['dir' => 'ltr'])
            ->label(__('filament-panels::pages/auth/register.form.password_confirmation.label'))
            ->password()
            ->required()
            ->revealable(filament()->arePasswordsRevealable());
    }

    private function getTurnstileFormComponent(): Turnstile
    {
        return Turnstile::make('turnstileToken');
    }

    public function getRegisterFormAction(): Action
    {
        return Action::make('register')
            ->disabled(fn() => ! $this->isTurnstileValidated)
            ->label(__('filament-panels::pages/auth/register.form.actions.register.label'))
            ->submit('register');
    }
}
