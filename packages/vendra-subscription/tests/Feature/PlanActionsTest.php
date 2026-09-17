<?php

declare(strict_types=1);

use Misaf\VendraSubscription\Actions\CreatePlanAction;
use Misaf\VendraSubscription\Actions\DeletePlanAction;
use Misaf\VendraSubscription\Actions\RestorePlanAction;
use Misaf\VendraSubscription\Actions\UpdatePlanAction;
use Misaf\VendraSubscription\Exceptions\PlanInUseException;
use Misaf\VendraSubscription\Models\Plan;
use Misaf\VendraSubscription\Models\Subscription;

it('moves the default to another active plan when the default is deactivated', function (): void {
    $first = resolve(CreatePlanAction::class)->execute(Plan::factory()->raw(['active' => true, 'is_default' => false]));
    $second = resolve(CreatePlanAction::class)->execute(Plan::factory()->raw(['active' => true, 'is_default' => false]));

    expect($first->refresh()->is_default)->toBeTrue();

    resolve(UpdatePlanAction::class)->execute($first, ['active' => false]);

    expect($first->refresh()->is_default)->toBeFalse()
        ->and($second->refresh()->is_default)->toBeTrue();
});

it('refuses to delete a plan a subscription references', function (): void {
    $plan = Plan::factory()->create();
    Subscription::factory()->for($plan)->create();

    expect(fn () => resolve(DeletePlanAction::class)->execute($plan))->toThrow(PlanInUseException::class)
        ->and($plan->refresh()->trashed())->toBeFalse();
});

it('soft deletes an unused plan', function (): void {
    $plan = Plan::factory()->create();

    resolve(DeletePlanAction::class)->execute($plan);

    expect($plan->refresh()->trashed())->toBeTrue();
});

it('restores a deleted default plan without taking the default back', function (): void {
    $deleted = resolve(CreatePlanAction::class)->execute(Plan::factory()->raw(['active' => true, 'is_default' => false]));
    $other = resolve(CreatePlanAction::class)->execute(Plan::factory()->raw(['active' => true, 'is_default' => false]));

    resolve(DeletePlanAction::class)->execute($deleted);

    expect($other->refresh()->is_default)->toBeTrue();

    resolve(RestorePlanAction::class)->execute($deleted);

    expect($deleted->refresh()->trashed())->toBeFalse()
        ->and($deleted->is_default)->toBeFalse()
        ->and($other->refresh()->is_default)->toBeTrue();
});
