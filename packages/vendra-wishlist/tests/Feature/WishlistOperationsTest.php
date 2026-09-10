<?php

declare(strict_types=1);

use Misaf\VendraWishlist\Actions\AddWishlistItemAction;
use Misaf\VendraWishlist\Actions\RemoveWishlistItemAction;
use Misaf\VendraWishlist\Database\Factories\WishlistFactory;
use Misaf\VendraWishlist\Models\Wishlist;
use Misaf\VendraWishlist\Models\WishlistItem;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('creates the default list for an owner on first use', function (): void {
    $user = createTestUser();

    $first = Wishlist::defaultFor($user);
    $second = Wishlist::defaultFor($user);

    expect($second->getKey())->toBe($first->getKey())
        ->and($first->is_default)->toBeTrue()
        ->and($first->token)->not->toBeEmpty()
        ->and($first->name)->toBe('Favourites')
        ->and(Wishlist::query()->count())->toBe(1);
});

it('keeps separate default lists for separate owners', function (): void {
    Wishlist::defaultFor(createTestUser());
    Wishlist::defaultFor(createTestUser());

    expect(Wishlist::query()->count())->toBe(2);
});

it('saves a sellable only once however often the heart is tapped', function (): void {
    $user = createTestUser();
    $wishlist = Wishlist::defaultFor($user);
    $action = resolve(AddWishlistItemAction::class);

    $first = $action->execute($wishlist, $user);
    $second = $action->execute($wishlist, $user);

    expect($second->getKey())->toBe($first->getKey())
        ->and(WishlistItem::query()->count())->toBe(1)
        ->and($wishlist->has($user))->toBeTrue();
});

it('keeps selection metadata on a saved item', function (): void {
    $user = createTestUser();
    $wishlist = Wishlist::defaultFor($user);

    $item = resolve(AddWishlistItemAction::class)->execute($wishlist, $user, ['size' => 'large']);

    expect($item->metadata)->toBe(['size' => 'large']);
});

it('removes a saved sellable and tolerates removing it twice', function (): void {
    $user = createTestUser();
    $wishlist = Wishlist::defaultFor($user);
    $remove = resolve(RemoveWishlistItemAction::class);

    resolve(AddWishlistItemAction::class)->execute($wishlist, $user);

    expect($remove->execute($wishlist, $user))->toBeTrue()
        ->and($remove->execute($wishlist, $user))->toBeFalse()
        ->and($wishlist->has($user))->toBeFalse();
});

it('cascades saved items when a list is deleted', function (): void {
    $user = createTestUser();
    $wishlist = WishlistFactory::new()->forOwner($user)->createOne();

    resolve(AddWishlistItemAction::class)->execute($wishlist, $user);

    $wishlist->delete();

    expect(WishlistItem::query()->count())->toBe(0);
});
