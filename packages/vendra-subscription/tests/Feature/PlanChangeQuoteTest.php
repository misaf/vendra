<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Date;
use Misaf\VendraSubscription\Models\Plan;
use Misaf\VendraSubscription\Models\Subscription;
use Misaf\VendraSubscription\Support\PlanChangeQuote;

beforeEach(function (): void {
    $this->travelTo(Date::parse('2026-04-11 00:00:00'));
});

function runningSubscriptionOn(Plan $plan, array $attributes = []): Subscription
{
    return Subscription::factory()->make([
        'price' => $plan->price,
        'currency_code' => $plan->currency_code,
        'starts_at' => Date::parse('2026-04-01 00:00:00'),
        'ends_at' => Date::parse('2026-05-01 00:00:00'),
        ...$attributes,
    ])->setRelation('plan', $plan);
}

it('prorates an upgrade from a paid running period onto its end date', function (): void {
    $current = runningSubscriptionOn(Plan::factory()->priced(3_000)->maxUnits(1)->make());

    $quote = PlanChangeQuote::for($current, Plan::factory()->priced(6_000)->maxUnits(5)->make());

    expect($quote->appliesNow)->toBeTrue()
        ->and($quote->isProrated())->toBeTrue()
        ->and($quote->endsAt?->equalTo($current->ends_at))->toBeTrue()
        ->and($quote->amount)->toBe(2_000);
});

it('defers a cheaper plan to the end of the period', function (): void {
    $quote = PlanChangeQuote::for(
        runningSubscriptionOn(Plan::factory()->priced(6_000)->make()),
        Plan::factory()->priced(3_000)->make(),
    );

    expect($quote->appliesNow)->toBeFalse();
});

it('treats the same price with more units as an upgrade', function (): void {
    $quote = PlanChangeQuote::for(
        runningSubscriptionOn(Plan::factory()->priced(3_000)->maxUnits(1)->make()),
        Plan::factory()->priced(3_000)->maxUnits(3)->make(),
    );

    expect($quote->appliesNow)->toBeTrue();
});

it('starts a full period when nothing paid is running', function (?Subscription $current): void {
    $quote = PlanChangeQuote::for($current, Plan::factory()->priced(6_000)->make());

    expect($quote->appliesNow)->toBeTrue()
        ->and($quote->isProrated())->toBeFalse();
})->with([
    'no subscription' => fn (): ?Subscription => null,
    'free plan' => fn (): Subscription => runningSubscriptionOn(Plan::factory()->make()),
    'trial' => fn (): Subscription => runningSubscriptionOn(Plan::factory()->priced(3_000)->make(), ['trial_ends_at' => Date::parse('2026-04-20 00:00:00')]),
]);
