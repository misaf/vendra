<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Date;
use Misaf\VendraSubscription\Enums\PeriodUnit;
use Misaf\VendraSubscription\Exceptions\PlanInUseException;
use Misaf\VendraSubscription\Models\Plan;
use Misaf\VendraSubscription\Models\Subscription;

it('reports whether it is free', function (): void {
    expect(Plan::factory()->priced(0)->create()->isFree())->toBeTrue()
        ->and(Plan::factory()->priced(1900)->create()->isFree())->toBeFalse();
});

it('reports whether it offers a trial', function (): void {
    expect(Plan::factory()->trialDays(14)->create()->hasTrial())->toBeTrue()
        ->and(Plan::factory()->trialDays(0)->create()->hasTrial())->toBeFalse();
});

it('grants only its listed feature entitlements', function (): void {
    $plan = Plan::factory()->withFeatures(['custom_domain'])->create();

    expect($plan->allows('custom_domain'))->toBeTrue()
        ->and($plan->allows('priority_support'))->toBeFalse();
});

it('resolves the period end and the grace-adjusted suspend date', function (): void {
    $plan = Plan::factory()->period(PeriodUnit::Month, 2)->graceDays(7)->create();
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
    $plan = Plan::factory()->create();
    $subscription = Subscription::factory()->for($plan)->create();

    expect(fn (): bool => $plan->delete())->toThrow(PlanInUseException::class);

    $subscription->delete();

    expect($plan->isInUse())->toBeTrue()
        ->and(fn (): bool => $plan->delete())->toThrow(PlanInUseException::class);
});

it('formats the price using the plan currency', function (): void {
    $plan = Plan::factory()->priced(2900, 'USD')->create();

    expect($plan->formattedPrice())->toBe('$29.00');
});

it('falls back to a plain number and code for unknown currencies', function (): void {
    $plan = Plan::factory()->priced(2900, 'ZZZ')->create();

    expect($plan->formattedPrice())->toBe('2,900 ZZZ');
});

it('marks the first enabled plan as the default', function (): void {
    $plan = Plan::factory()->create();

    expect($plan->refresh()->is_default)->toBeTrue();
});

it('never marks a disabled plan as the default', function (): void {
    $plan = Plan::factory()->create(['active' => false, 'is_default' => true]);

    expect($plan->refresh()->is_default)->toBeFalse()
        ->and(Plan::query()->default()->exists())->toBeFalse();
});

it('keeps only one default plan when a new default is set', function (): void {
    $first = Plan::factory()->default()->create();
    $second = Plan::factory()->create();

    $second->update(['is_default' => true]);

    expect(Plan::query()->default()->pluck('id')->all())->toBe([$second->id])
        ->and($first->refresh()->is_default)->toBeFalse();
});

it('promotes another enabled plan when the default is deleted', function (): void {
    $default = Plan::factory()->default()->create();
    $other = Plan::factory()->create();

    $default->delete();

    expect($other->refresh()->is_default)->toBeTrue();
});
