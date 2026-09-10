<?php

declare(strict_types=1);

use Misaf\VendraProduct\Database\Factories\ProductFactory;
use Misaf\VendraWishlist\Database\Factories\WishlistFactory;
use Misaf\VendraWishlist\Database\Factories\WishlistItemFactory;
use Misaf\VendraWishlist\Models\Wishlist;
use Misaf\VendraWishlist\Models\WishlistItem;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('requires authentication and exposes only lists owned by the caller', function (): void {
    $user = createTestUser();
    $wishlist = WishlistFactory::new()->forOwner($user)->createOne();
    $item = WishlistItemFactory::new()->forWishlist($wishlist)->createOne();
    $hidden = WishlistFactory::new()->forOwner(createTestUser())->createOne();

    $this->getJson('/api/customers/wishlists')->assertUnauthorized();

    $this->actingAs($user)
        ->getJson('/api/customers/wishlists', ['Accept' => 'application/ld+json'])
        ->assertOk()
        ->assertJsonPath('totalItems', 1)
        ->assertJsonPath('member.0.id', $wishlist->id)
        ->assertJsonPath('member.0.isDefault', true)
        ->assertJsonPath('member.0.items.0.id', $item->id)
        ->assertJsonMissing(['id' => $hidden->id]);
});

it('denies access to a list owned by another customer', function (): void {
    $wishlist = WishlistFactory::new()->forOwner(createTestUser())->createOne();

    $this->actingAs(createTestUser())
        ->getJson("/api/customers/wishlists/{$wishlist->id}", ['Accept' => 'application/ld+json'])
        ->assertNotFound();
});

it('saves a product to the default list and answers with the whole list', function (): void {
    $user = createTestUser();
    $product = ProductFactory::new()->createOne();

    $this->actingAs($user)
        ->postJson('/api/customers/saved-items', [
            'sellableType' => 'product',
            'sellableId' => $product->id,
            'metadata' => ['size' => 'large'],
        ])
        ->assertOk()
        ->assertJsonPath('isDefault', true)
        ->assertJsonPath('items.0.sellableType', 'product')
        ->assertJsonPath('items.0.sellableId', $product->id)
        ->assertJsonPath('items.0.metadata.size', 'large');

    expect(Wishlist::query()->count())->toBe(1)
        ->and(WishlistItem::query()->count())->toBe(1);
});

it('keeps one row when the same product is saved twice', function (): void {
    $user = createTestUser();
    $product = ProductFactory::new()->createOne();
    $payload = ['sellableType' => 'product', 'sellableId' => $product->id];

    $this->actingAs($user)->postJson('/api/customers/saved-items', $payload)->assertOk();
    $this->actingAs($user)->postJson('/api/customers/saved-items', $payload)->assertOk();

    expect(WishlistItem::query()->count())->toBe(1);
});

it('rejects saving something the catalog does not have', function (): void {
    $this->actingAs(createTestUser())
        ->postJson('/api/customers/saved-items', ['sellableType' => 'product', 'sellableId' => 9999])
        ->assertUnprocessable();

    expect(WishlistItem::query()->count())->toBe(0);
});

it('forgets a saved item', function (): void {
    $user = createTestUser();
    $wishlist = WishlistFactory::new()->forOwner($user)->createOne();
    $item = WishlistItemFactory::new()->forWishlist($wishlist)->createOne();

    $this->actingAs($user)
        ->deleteJson("/api/customers/saved-items/{$item->id}")
        ->assertNoContent();

    expect(WishlistItem::query()->count())->toBe(0);
});

it('will not forget an item saved by another customer', function (): void {
    $wishlist = WishlistFactory::new()->forOwner(createTestUser())->createOne();
    $item = WishlistItemFactory::new()->forWishlist($wishlist)->createOne();

    $this->actingAs(createTestUser())
        ->deleteJson("/api/customers/saved-items/{$item->id}")
        ->assertNotFound();

    expect(WishlistItem::query()->count())->toBe(1);
});
