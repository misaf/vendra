<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Config;
use Misaf\VendraAffiliate\Actions\ProcessAffiliatePayoutAction;
use Misaf\VendraAffiliate\Enums\AffiliatePayoutPolicyEnum;
use Misaf\VendraAffiliate\Models\Affiliate;

final class ProcessPayoutAction
{
    public static function make(): Action
    {
        return Action::make('processPayout')
            ->authorize(fn (): bool => (bool) auth()->user()?->can(AffiliatePayoutPolicyEnum::Process->value))
            ->color('success')
            ->disabled(fn (Affiliate $record): bool => $record->pendingBalance() < Config::integer('vendra-affiliate.payout.minimum', 0))
            ->icon(Heroicon::OutlinedBanknotes)
            ->label(__('vendra-affiliate::messages.process_payout'))
            ->requiresConfirmation()
            ->modalDescription(fn (Affiliate $record): string => __('vendra-affiliate::messages.process_payout_description', [
                'amount' => $record->pendingBalance(),
            ]))
            ->action(function (Affiliate $record): void {
                resolve(ProcessAffiliatePayoutAction::class)->onQueue()->execute($record);

                Notification::make()
                    ->success()
                    ->title(__('vendra-affiliate::messages.process_payout_queued'))
                    ->send();
            });
    }
}
