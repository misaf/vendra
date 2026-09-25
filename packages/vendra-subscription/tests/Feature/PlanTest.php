<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Date;
use Misaf\VendraSubscription\Enums\PeriodUnit;
use Misaf\VendraSubscription\Exceptions\PlanInUseException;
use Misaf\VendraSubscription\Models\Plan;
use Misaf\VendraSubscription\Models\Subscription;

it('reports whether it is free', function (): void {
    expect(Plan::factory()->active()->priced(0)->create()->isFree())->toBeTrue()
        ->and(Plan::factory()->active()->priced(1900)->create()->isFree())->toBeFalse();
});

it('reports whether it offers a trial', function (): void {
    expect(Plan::factory()->active()->trialDays(14)->create()->hasTrial())->toBeTrue()
        ->and(Plan::factory()->active()->trialDays(0)->create()->hasTrial())->toBeFalse();
});

it('grants only its listed feature entitlements', function (): void {
    $plan = Plan::factory()->active()->withFeatures(['custom_domain'])->create();

    expect($plan->allows('custom_domain'))->toBeTrue()
        ->and($plan->allows('priority_support'))->toBeFalse();
});

it('reads its limits and leaves a missing limit unlimited', function (): void {
    $plan = Plan::factory()->active()->withLimits(['products_per_store' => 50])->create();

    expect($plan->refresh()->limit('products_per_store'))->toBe(50)
        ->and($plan->limit('domains_per_store'))->toBeNull()
        ->and(Plan::factory()->create()->limit('products_per_store'))->toBeNull();
});

it('resolves the period end and the grace-adjusted suspend date', function (): void {
    $plan = Plan::factory()->active()->period(PeriodUnit::Month, 2)->graceDays(7)->create();
    $start = Date::parse('2026-01-01');

    expect($plan->resolveEndDate($start)->toDateString())->toBe('2026-03-01')
        ->and($plan->resolveSuspendDate(Date::parse('2026-03-01'))->toDateString())->toBe('2026-03-08');
});

it('filters by active state', function (): void {
    $active = Plan::factory()->create(['active' => true]);
    $inactive = Plan::factory()->create(['active' => false]);

    expect(Plan::query()->active()->pluck('id'))->toContain($active->id)->not->toContain($inactive->id)
        ->and(Plan::query()->inactive()->pluck('id'))->toContain($inactive->id)->not->toContain($active->id);
});

it('blocks deletion while any subscription references it, even a trashed one', function (): void {
    $plan = Plan::factory()->active()->create();
    $subscription = Subscription::factory()->for($plan)->create();

    expect(fn (): bool => $plan->delete())->toThrow(PlanInUseException::class);

    $subscription->delete();

    expect($plan->isInUse())->toBeTrue()
        ->and(fn (): bool => $plan->delete())->toThrow(PlanInUseException::class);
});

it('formats the price using the plan currency', function (): void {
    $plan = Plan::factory()->active()->priced(2900, 'USD')->create();

    expect($plan->formattedPrice())->toBe('$29.00');
});

it('falls back to a plain number and code for unknown currencies', function (): void {
    $plan = Plan::factory()->active()->priced(2900, 'ZZZ')->create();

    expect($plan->formattedPrice())->toBe('2,900 ZZZ');
});

it('marks the first enabled plan as the default', function (): void {
    $plan = Plan::factory()->active()->create();

    expect($plan->refresh()->is_default)->toBeTrue();
});

it('never marks a disabled plan as the default', function (): void {
    $plan = Plan::factory()->create(['active' => false, 'is_default' => true]);

    expect($plan->refresh()->is_default)->toBeFalse()
        ->and(Plan::query()->default()->exists())->toBeFalse();
});

it('keeps only one default plan when a new default is set', function (): void {
    $first = Plan::factory()->active()->default()->create();
    $second = Plan::factory()->active()->create();

    $second->update(['is_default' => true]);

    expect(Plan::query()->default()->pluck('id')->all())->toBe([$second->id])
        ->and($first->refresh()->is_default)->toBeFalse();
});

it('promotes another enabled plan when the default is deleted', function (): void {
    $default = Plan::factory()->active()->default()->create();
    $other = Plan::factory()->active()->create();

    $default->delete();

    expect($other->refresh()->is_default)->toBeTrue();
});

it('clears the flag on a deleted default so restoring it leaves the new default alone', function (): void {
    $default = Plan::factory()->active()->default()->create();
    $other = Plan::factory()->active()->create();

    $default->delete();
    $default->restore();

    expect($default->refresh()->is_default)->toBeFalse()
        ->and($other->refresh()->is_default)->toBeTrue();
});
