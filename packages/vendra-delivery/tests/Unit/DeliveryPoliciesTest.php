<?php

declare(strict_types=1);

use Illuminate\Contracts\Auth\Access\Authorizable;
use Misaf\VendraDelivery\Enums\DeliveryPolicyEnum;
use Misaf\VendraDelivery\Enums\DeliverySlotPolicyEnum;
use Misaf\VendraDelivery\Enums\DeliveryZonePolicyEnum;
use Misaf\VendraDelivery\Models\Delivery;
use Misaf\VendraDelivery\Models\DeliverySlot;
use Misaf\VendraDelivery\Models\DeliveryZone;
use Misaf\VendraDelivery\Policies\DeliveryPolicy;
use Misaf\VendraDelivery\Policies\DeliverySlotPolicy;
use Misaf\VendraDelivery\Policies\DeliveryZonePolicy;

it('authorizes delivery zone abilities through permissions', function (string $method, DeliveryZonePolicyEnum $permission): void {
    $user = Mockery::mock(Authorizable::class);
    $user->shouldReceive('can')->once()->with($permission->value)->andReturnTrue();

    $arguments = in_array($method, ['view', 'update', 'delete'], true)
        ? [$user, new DeliveryZone]
        : [$user];

    expect((new DeliveryZonePolicy)->{$method}(...$arguments))->toBeTrue();
})->with([
    ['view', DeliveryZonePolicyEnum::View],
    ['viewAny', DeliveryZonePolicyEnum::ViewAny],
    ['create', DeliveryZonePolicyEnum::Create],
    ['update', DeliveryZonePolicyEnum::Update],
    ['delete', DeliveryZonePolicyEnum::Delete],
]);

it('authorizes delivery slot abilities through permissions', function (string $method, DeliverySlotPolicyEnum $permission): void {
    $user = Mockery::mock(Authorizable::class);
    $user->shouldReceive('can')->once()->with($permission->value)->andReturnTrue();

    $arguments = in_array($method, ['view', 'update', 'delete'], true)
        ? [$user, new DeliverySlot]
        : [$user];

    expect((new DeliverySlotPolicy)->{$method}(...$arguments))->toBeTrue();
})->with([
    ['view', DeliverySlotPolicyEnum::View],
    ['viewAny', DeliverySlotPolicyEnum::ViewAny],
    ['create', DeliverySlotPolicyEnum::Create],
    ['update', DeliverySlotPolicyEnum::Update],
]);

it('authorizes delivery abilities through permissions', function (string $method, DeliveryPolicyEnum $permission): void {
    $user = Mockery::mock(Authorizable::class);
    $user->shouldReceive('can')->once()->with($permission->value)->andReturnTrue();

    $arguments = in_array($method, ['view', 'update', 'delete'], true)
        ? [$user, new Delivery]
        : [$user];

    expect((new DeliveryPolicy)->{$method}(...$arguments))->toBeTrue();
})->with([
    ['view', DeliveryPolicyEnum::View],
    ['viewAny', DeliveryPolicyEnum::ViewAny],
    ['update', DeliveryPolicyEnum::Update],
    ['delete', DeliveryPolicyEnum::Delete],
]);

it('does not allow deliveries to be created by hand', function (): void {
    expect((new DeliveryPolicy)->create(Mockery::mock(Authorizable::class)))->toBeFalse();
});
