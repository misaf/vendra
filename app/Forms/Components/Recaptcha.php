<?php

declare(strict_types=1);

namespace App\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\Http;
use Livewire\Component as Livewire;

final class Recaptcha extends Field
{
    protected string $view = 'forms.components.recaptcha';

    protected function setUp(): void
    {
        parent::setUp();

        $this->viewData([
            'siteKey' => config('services.recaptcha.key'),
        ]);

        $this->hiddenLabel()
            ->dehydrated(false)
            ->afterStateUpdated(fn(Livewire $livewire) => $livewire->dispatch('recaptchaStateUpdated'))
            ->rules([
                fn(Get $get): Closure => $this->recaptchaValidation(),
            ]);
    }

    private function recaptchaValidation(): Closure
    {
        return function (string $attribute, $value, Closure $fail): void {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret'   => config('services.recaptcha.secret'),
                'response' => $value,
            ]);

            $responseData = $response->json();

            if ( ! $responseData['success'] || $responseData['score'] < 0.5) {
                $fail(__('متأسفانه کد امنیتی گوگل نتونستیم شما رو شناسایی کنیم. لطفاً دوباره امتحان کنید.'));
            }
        };
    }
}
