<?php

declare(strict_types=1);

namespace Misaf\VendraInquiry\Filament\Clusters\Resources\Inquiries\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Misaf\VendraInquiry\Enums\InquiryStatusEnum;
use Misaf\VendraInquiry\Models\Inquiry;

final class AnswerInquiryAction
{
    public static function make(): Action
    {
        return Action::make('answer')
            ->authorize(fn (Inquiry $record): bool => auth()->user()?->can('update', $record) ?? false)
            ->color('success')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->label(__('vendra-inquiry::messages.answer'))
            ->requiresConfirmation()
            ->visible(fn (Inquiry $record): bool => $record->status !== InquiryStatusEnum::Answered)
            ->action(function (Inquiry $record): void {
                $record->markAnswered();

                Notification::make()
                    ->success()
                    ->title(__('vendra-inquiry::messages.inquiry_answered'))
                    ->send();
            });
    }
}
