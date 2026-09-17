<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Lang;
use Misaf\VendraSubscription\Enums\PeriodUnit;
use Misaf\VendraSubscription\Enums\SubscriptionStatus;

it('translates every subscription status and period unit label in each locale', function (string $locale): void {
    $prefixedEnums = [
        'subscription_status_' => SubscriptionStatus::cases(),
        'period_unit_' => PeriodUnit::cases(),
    ];

    $missing = [];

    foreach ($prefixedEnums as $prefix => $cases) {
        foreach ($cases as $case) {
            if (! Lang::has("vendra-subscription::enums.{$prefix}{$case->value}", $locale, false)) {
                $missing[] = "{$prefix}{$case->value}";
            }
        }
    }

    expect($missing)->toBeEmpty();
})->with(['en', 'fa', 'de']);

it('colors subscription statuses by whether the subscription is in good standing', function (): void {
    expect(SubscriptionStatus::Active->getColor())->toBe('success')
        ->and(SubscriptionStatus::PendingPayment->getColor())->toBe('warning')
        ->and(SubscriptionStatus::PastDue->getColor())->toBe('danger')
        ->and(SubscriptionStatus::Expired->getColor())->toBe('danger')
        ->and(SubscriptionStatus::Cancelled->getColor())->toBe('gray');
});
