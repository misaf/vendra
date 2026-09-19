<?php

declare(strict_types=1);

namespace Misaf\VendraInquiry\Filament\Clusters\Resources\Inquiries\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Misaf\VendraInquiry\Actions\ReopenInquiryAction;
use Misaf\VendraInquiry\Models\Inquiry;
use Misaf\VendraInquiry\States\Open;

final class ReopenInquiryTableAction
{
    public static function make(): Action
    {
        return Action::make('reopen')
            ->authorize(fn (Inquiry $record): bool => auth()->user()?->can('update', $record) ?? false)
            ->color('warning')
            ->icon(Heroicon::OutlinedArrowPath)
            ->label(__('vendra-inquiry::messages.reopen'))
            ->requiresConfirmation()
            ->visible(fn (Inquiry $record): bool => $record->status->canTransitionTo(Open::class))
            ->action(function (Inquiry $record): void {
                resolve(ReopenInquiryAction::class)->execute($record);

                Notification::make()
                    ->success()
                    ->title(__('vendra-inquiry::messages.inquiry_reopened'))
                    ->send();
            });
    }
}
