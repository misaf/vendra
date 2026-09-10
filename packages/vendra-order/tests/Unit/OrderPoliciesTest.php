<?php

declare(strict_types=1);

use Illuminate\Contracts\Auth\Access\Authorizable;
use Misaf\VendraOrder\Enums\OrderLinePolicyEnum;
use Misaf\VendraOrder\Enums\OrderPolicyEnum;
use Misaf\VendraOrder\Models\Order;
use Misaf\VendraOrder\Models\OrderLine;
use Misaf\VendraOrder\Policies\OrderLinePolicy;
use Misaf\VendraOrder\Policies\OrderPolicy;

it('authorizes order abilities through permissions', function (string $method, OrderPolicyEnum $permission): void {
    $user = Mockery::mock(Authorizable::class);
    $user->shouldReceive('can')->once()->with($permission->value)->andReturnTrue();

    $arguments = in_array($method, ['view', 'update', 'delete'], true)
        ? [$user, new Order]
        : [$user];

    expect((new OrderPolicy)->{$method}(...$arguments))->toBeTrue();
})->with([
    ['view', OrderPolicyEnum::View],
    ['viewAny', OrderPolicyEnum::ViewAny],
    ['update', OrderPolicyEnum::Update],
    ['delete', OrderPolicyEnum::Delete],
    ['deleteAny', OrderPolicyEnum::DeleteAny],
]);

it('authorizes order line abilities through permissions', function (string $method, OrderLinePolicyEnum $permission): void {
    $user = Mockery::mock(Authorizable::class);
    $user->shouldReceive('can')->once()->with($permission->value)->andReturnTrue();

    $arguments = $method === 'view'
        ? [$user, new OrderLine]
        : [$user];

    expect((new OrderLinePolicy)->{$method}(...$arguments))->toBeTrue();
})->with([
    ['view', OrderLinePolicyEnum::View],
    ['viewAny', OrderLinePolicyEnum::ViewAny],
]);

it('keeps order lines immutable through administration', function (): void {
    $user = Mockery::mock(Authorizable::class);
    $policy = new OrderLinePolicy;

    expect($policy->create($user))->toBeFalse()
        ->and($policy->update($user, new OrderLine))->toBeFalse()
        ->and($policy->delete($user, new OrderLine))->toBeFalse();
});
