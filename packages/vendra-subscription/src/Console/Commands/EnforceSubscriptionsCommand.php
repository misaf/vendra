<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Support\Arr;
use Illuminate\Console\Command;
use Misaf\VendraSubscription\Actions\EnforceSubscriptionsAction;
use Misaf\VendraSupport\Context\RequestJobContext;

#[Description('Expire lapsed subscriptions and suspend units past their grace period')]
#[Signature('vendra-subscription:enforce-subscriptions')]
final class EnforceSubscriptionsCommand extends Command
{
    public function handle(EnforceSubscriptionsAction $enforceSubscriptionsAction): int
    {
        new RequestJobContext(
            traceId: RequestJobContext::resolveTraceId(),
            operation: 'subscription_enforcement',
        )->scope(function () use ($enforceSubscriptionsAction): void {
            $result = $enforceSubscriptionsAction->execute();

            $this->info('Subscriptions enforced.');
            $this->table(['Metric', 'Count'], [
                ['Expired subscriptions', Arr::get($result, 'expired')],
                ['Expiry reminders sent', Arr::get($result, 'reminded')],
                ['Subscribers past grace', Arr::get($result, 'grace_expired')],
            ]);
        });

        return self::SUCCESS;
    }
}
