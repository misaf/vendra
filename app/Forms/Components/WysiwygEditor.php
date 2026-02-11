<?php

declare(strict_types=1);

namespace App\Forms\Components;

use Filament\Forms\Components\RichEditor;
use Misaf\Coinpayments\Settings\CoinPaymentsSettings;

final class WysiwygEditor
{
    public static function make(string $name): RichEditor
    {
        return RichEditor::make($name)
            ->columnSpanFull()
            ->helperText(function (): ?string {
                // $a = app(CoinPaymentsSettings::class)->withdrawal_limit_count;

                // if (1 !== $a) {
                //     return null;
                // }

                return __('توضیح کوتاهی از محتوا.');
            })
            ->label(__('form.description'))
            ->toolbarButtons([
                'attachFiles',
                'blockquote',
                'bold',
                'bulletList',
                'codeBlock',
                'h2',
                'h3',
                'italic',
                'link',
                'orderedList',
                'redo',
                'strike',
                'underline',
                'undo',
            ]);
    }
}
