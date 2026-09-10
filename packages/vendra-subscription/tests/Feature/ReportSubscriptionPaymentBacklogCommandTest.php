<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Misaf\VendraSubscription\Enums\SubscriptionPaymentStatus;
use Misaf\VendraSubscription\Enums\SubscriptionStatus;
use Misaf\VendraSubscription\Models\Subscription;
use Misaf\VendraSubscription\Models\SubscriptionPayment;

it('warns and reports counts when a payment backlog exists', function (): void {
    Log::spy();

    SubscriptionPayment::factory()->create(['status' => SubscriptionPaymentStatus::NeedsReconciliation]);
    SubscriptionPayment::factory()->create([
        'status' => SubscriptionPaymentStatus::Processing,
        'processing_at' => now()->subHour(),
    ]);
    $paid = SubscriptionPayment::factory()
        ->for(Subscription::factory()->state(['status' => SubscriptionStatus::PendingPayment]))
        ->create(['status' => SubscriptionPaymentStatus::Paid]);

    $this->artisan('vendra-subscription:report-payment-backlog')
        ->assertSuccessful();

    Log::shouldHaveReceived('warning')
        ->once()
        ->withArgs(fn (string $message, array $context): bool => 3 === Arr::get($context, 'needs_reconciliation') + Arr::get($context, 'stalled_processing') + Arr::get($context, 'activation_gap'));

    expect($paid->refresh()->status)->toBe(SubscriptionPaymentStatus::Paid);
});

it('does not warn when there is no backlog', function (): void {
    Log::spy();

    SubscriptionPayment::factory()->create(['status' => SubscriptionPaymentStatus::Paid]);
    SubscriptionPayment::factory()->create([
        'status' => SubscriptionPaymentStatus::Processing,
        'processing_at' => now(),
    ]);

    $this->artisan('vendra-subscription:report-payment-backlog')
        ->assertSuccessful();

    Log::shouldNotHaveReceived('warning');
});

it('respects the stale-minutes threshold for stalled processing payments', function (): void {
    Log::spy();

    SubscriptionPayment::factory()->create([
        'status' => SubscriptionPaymentStatus::Processing,
        'processing_at' => now()->subMinutes(10),
    ]);

    $this->artisan('vendra-subscription:report-payment-backlog', ['--stale-minutes' => 30])
        ->assertSuccessful();

    Log::shouldNotHaveReceived('warning');
});
