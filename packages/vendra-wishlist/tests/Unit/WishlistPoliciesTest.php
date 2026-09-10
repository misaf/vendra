<?php

declare(strict_types=1);

use Illuminate\Contracts\Auth\Access\Authorizable;
use Misaf\VendraWishlist\Enums\WishlistItemPolicyEnum;
use Misaf\VendraWishlist\Enums\WishlistPolicyEnum;
use Misaf\VendraWishlist\Models\Wishlist;
use Misaf\VendraWishlist\Models\WishlistItem;
use Misaf\VendraWishlist\Policies\WishlistItemPolicy;
use Misaf\VendraWishlist\Policies\WishlistPolicy;

it('authorizes wishlist abilities through permissions', function (string $method, WishlistPolicyEnum $permission): void {
    $user = Mockery::mock(Authorizable::class);
    $user->shouldReceive('can')->once()->with($permission->value)->andReturnTrue();

    $arguments = in_array($method, ['view', 'delete'], true)
        ? [$user, new Wishlist]
        : [$user];

    expect((new WishlistPolicy)->{$method}(...$arguments))->toBeTrue();
})->with([
    ['view', WishlistPolicyEnum::View],
    ['viewAny', WishlistPolicyEnum::ViewAny],
    ['delete', WishlistPolicyEnum::Delete],
    ['deleteAny', WishlistPolicyEnum::DeleteAny],
]);

it('authorizes saved item abilities through permissions', function (string $method, WishlistItemPolicyEnum $permission): void {
    $user = Mockery::mock(Authorizable::class);
    $user->shouldReceive('can')->once()->with($permission->value)->andReturnTrue();

    $arguments = in_array($method, ['view', 'delete'], true)
        ? [$user, new WishlistItem]
        : [$user];

    expect((new WishlistItemPolicy)->{$method}(...$arguments))->toBeTrue();
})->with([
    ['view', WishlistItemPolicyEnum::View],
    ['viewAny', WishlistItemPolicyEnum::ViewAny],
    ['delete', WishlistItemPolicyEnum::Delete],
    ['deleteAny', WishlistItemPolicyEnum::DeleteAny],
]);

it('never writes a customer list from administration', function (): void {
    $user = Mockery::mock(Authorizable::class);

    expect((new WishlistPolicy)->create($user))->toBeFalse()
        ->and((new WishlistPolicy)->update($user, new Wishlist))->toBeFalse()
        ->and((new WishlistItemPolicy)->create($user))->toBeFalse()
        ->and((new WishlistItemPolicy)->update($user, new WishlistItem))->toBeFalse();
});
