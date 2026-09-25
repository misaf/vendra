<?php

declare(strict_types=1);

namespace Misaf\VendraConsole\Filament\Resources\Resellers\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Misaf\VendraConsole\Filament\Resources\Resellers\Actions\Concerns\InteractsWithResellerRecord;
use Misaf\VendraReseller\Models\Reseller;
use Misaf\VendraSubscription\Actions\RenewSubscriptionAction;
use Misaf\VendraSubscription\Exceptions\SubscriptionLimitException;
use Misaf\VendraSubscription\Exceptions\SubscriptionPaymentException;
use Misaf\VendraSubscription\Models\Subscription;

final class RenewSubscriptionTableAction extends Action
{
    use InteractsWithResellerRecord;

    public static function getDefaultName(): string
    {
        return 'renew';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('vendra-console::actions.renew'))->icon(Heroicon::OutlinedArrowPath)
            ->hidden(fn (Reseller $record): bool => $record->trashed() || ! $record->renewableSubscription() instanceof Subscription)
            ->requiresConfirmation()
            ->modalDescription(fn (Reseller $record): ?string => $record->renewableSubscription()?->plan?->formattedPrice())
            ->action(function (Reseller $record): void {
                $subscription = $record->renewableSubscription();

                if (! $subscription instanceof Subscription) {
                    Notification::make()->danger()->title(__('vendra-console::attributes.no_active_subscription'))->send();

                    return;
                }

                try {
                    resolve(RenewSubscriptionAction::class)->execute($subscription);
                } catch (SubscriptionLimitException|SubscriptionPaymentException $exception) {
                    Notification::make()->danger()->title(__('vendra-console::messages.renewal_blocked'))->body($exception->getMessage())->send();

                    return;
                }

                self::notifySuccess(__('vendra-console::messages.subscription_renewed'));
            });
    }
}
