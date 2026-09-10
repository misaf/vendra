<?php

declare(strict_types=1);

use Illuminate\Contracts\Auth\Access\Authorizable;
use Misaf\VendraCart\Enums\CartItemPolicyEnum;
use Misaf\VendraCart\Enums\CartPolicyEnum;
use Misaf\VendraCart\Models\Cart;
use Misaf\VendraCart\Models\CartItem;
use Misaf\VendraCart\Policies\CartItemPolicy;
use Misaf\VendraCart\Policies\CartPolicy;

it('authorizes cart abilities through permissions', function (string $method, CartPolicyEnum $permission): void {
    $user = Mockery::mock(Authorizable::class);
    $user->shouldReceive('can')->once()->with($permission->value)->andReturnTrue();

    $arguments = in_array($method, ['view', 'delete'], true)
        ? [$user, new Cart]
        : [$user];

    expect((new CartPolicy)->{$method}(...$arguments))->toBeTrue();
})->with([
    ['view', CartPolicyEnum::View],
    ['viewAny', CartPolicyEnum::ViewAny],
    ['delete', CartPolicyEnum::Delete],
    ['deleteAny', CartPolicyEnum::DeleteAny],
]);

it('authorizes cart item abilities through permissions', function (string $method, CartItemPolicyEnum $permission): void {
    $user = Mockery::mock(Authorizable::class);
    $user->shouldReceive('can')->once()->with($permission->value)->andReturnTrue();

    $arguments = in_array($method, ['view', 'delete'], true)
        ? [$user, new CartItem]
        : [$user];

    expect((new CartItemPolicy)->{$method}(...$arguments))->toBeTrue();
})->with([
    ['view', CartItemPolicyEnum::View],
    ['viewAny', CartItemPolicyEnum::ViewAny],
    ['delete', CartItemPolicyEnum::Delete],
    ['deleteAny', CartItemPolicyEnum::DeleteAny],
]);

it('does not allow carts or items to be created or updated through administration', function (): void {
    $user = Mockery::mock(Authorizable::class);

    expect((new CartPolicy)->create($user))->toBeFalse()
        ->and((new CartPolicy)->update($user, new Cart))->toBeFalse()
        ->and((new CartItemPolicy)->create($user))->toBeFalse()
        ->and((new CartItemPolicy)->update($user, new CartItem))->toBeFalse();
});
