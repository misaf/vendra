<?php

declare(strict_types=1);

namespace App\Forms\Components;

use Filament\Forms\Components\TextInput;
use Illuminate\Validation\Rules\Unique;
use Livewire\Component as Livewire;
use Misaf\Tenant\Models\Tenant;

final class SlugTextInput
{
    public static function make(string $name): TextInput
    {
        return TextInput::make($name)
            ->afterStateUpdated(function (Livewire $livewire) use ($name): void {
                $livewire->validateOnly("data.{$name}");
            })
            ->columnSpan(['lg' => 1])
            ->helperText(__('شناسه یکتای URL, نیازی به وارد کردن این قسمت نمی باشد به صورت خودکار بعد از وارد کردن فیلد نام پر می گردد.'))
            ->label(__('form.slug'))
            ->required()
            ->unique(
                ignoreRecord: true,
                modifyRuleUsing: function (Unique $rule): void {
                    $rule->where('tenant_id', Tenant::current()->id)
                        ->withoutTrashed();
                },
            );
    }
}
