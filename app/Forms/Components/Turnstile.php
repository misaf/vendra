<?php

declare(strict_types=1);

namespace App\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

final class Turnstile extends Field
{
    protected string $size = 'flexible';

    protected string $view = 'forms.components.turnstile';

    protected function setUp(): void
    {
        parent::setUp();

        $this->hiddenLabel()
            ->dehydrated(false)
            ->afterStateUpdated(function (Component $livewire): void {
                $livewire->dispatch('turnstileStateUpdated');
            })
            ->required()
            ->rules([
                function (Component $livewire): Closure {
                    return $this->turnstileValidation($livewire);
                },
            ]);
    }

    public function getSize(): string
    {
        return $this->evaluate($this->size);
    }

    private function turnstileValidation(Component $livewire): Closure
    {
        return function (string $attribute, $value, Closure $fail) use ($livewire): void {
            $response = Http::asJson()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret'   => config('services.turnstile.secret'),
                'response' => $value,
            ]);

            $responseData = $response->json();

            if ( ! $responseData['success']) {
                $fail(__('متأسفانه کد امنیتی نتوانست شما رو شناسایی کند. لطفاً دوباره امتحان کنید.'));
            }

            $livewire->dispatch('resetTurnstile');
        };
    }
}
