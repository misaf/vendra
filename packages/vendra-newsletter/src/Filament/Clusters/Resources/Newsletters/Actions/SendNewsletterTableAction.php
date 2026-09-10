<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter\Filament\Clusters\Resources\Newsletters\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;
use Misaf\VendraNewsletter\Actions\SendNewsletterAction;
use Misaf\VendraNewsletter\Enums\NewsletterStatusEnum;
use Misaf\VendraNewsletter\Models\Newsletter;

final class SendNewsletterTableAction
{
    public static function make(): Action
    {
        return Action::make('send')
            ->label(__('vendra-newsletter::actions.send'))
            ->icon(Heroicon::OutlinedPaperAirplane)
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading(__('vendra-newsletter::actions.send'))
            ->modalDescription(__('vendra-newsletter::actions.send_confirmation'))
            ->authorize(fn (Newsletter $record): bool => Gate::allows('send', $record))
            ->visible(fn (Newsletter $record): bool => $record->status !== NewsletterStatusEnum::Sent)
            ->action(function (Newsletter $record): void {
                $recipients = app(SendNewsletterAction::class)->execute($record);

                Notification::make()
                    ->title(__('vendra-newsletter::actions.send_success', ['count' => $recipients]))
                    ->success()
                    ->send();
            });
    }
}
