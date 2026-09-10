<?php

declare(strict_types=1);

namespace Misaf\VendraInquiry\Filament\Clusters\Resources\Inquiries\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Misaf\VendraInquiry\Enums\InquiryStatusEnum;
use Misaf\VendraInquiry\Models\Inquiry;

final class CloseInquiryAction
{
    public static function make(): Action
    {
        return Action::make('close')
            ->authorize(fn (Inquiry $record): bool => auth()->user()?->can('update', $record) ?? false)
            ->color('gray')
            ->icon(Heroicon::OutlinedArchiveBox)
            ->label(__('vendra-inquiry::messages.close'))
            ->requiresConfirmation()
            ->visible(fn (Inquiry $record): bool => $record->status !== InquiryStatusEnum::Closed)
            ->action(function (Inquiry $record): void {
                $record->close();

                Notification::make()
                    ->success()
                    ->title(__('vendra-inquiry::messages.inquiry_closed'))
                    ->send();
            });
    }
}
