<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Misaf\VendraCart\Database\Factories\CartFactory;
use Misaf\VendraCart\Models\Cart;
use Misaf\VendraCart\Models\CartItem;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;

it('keeps cart ownership and sellable references polymorphic', function (): void {
    $cart = new Cart;
    $item = new CartItem;

    expect($cart->owner())->toBeInstanceOf(MorphTo::class)
        ->and($cart->items())->toBeInstanceOf(HasMany::class)
        ->and($item->cart())->toBeInstanceOf(BelongsTo::class)
        ->and($item->sellable())->toBeInstanceOf(MorphTo::class);
});

it('makes carts tenant aware without coupling items to tenant records', function (): void {
    expect(class_uses_recursive(Cart::class))->toContain(BelongsToTenant::class)
        ->and(class_uses_recursive(CartItem::class))->not->toContain(BelongsToTenant::class);
});

it('defaults new cart items to one unit', function (): void {
    expect((new CartItem)->quantity)->toBe(1);
});

it('can build an owned cart without depending on a concrete user model', function (): void {
    $owner = new class extends Model
    {
        use HasFactory;
    };
    $owner->setAttribute('id', 42);

    $attributes = CartFactory::new()
        ->forOwner($owner)
        ->raw();

    expect($attributes)
        ->owner_type->toBe($owner->getMorphClass())
        ->owner_id->toBe(42);
});

it('resolves a human-readable owner label', function (): void {
    $owner = new class extends Model
    {
        use HasFactory;
    };
    $owner->setAttribute('id', 42);
    $owner->setAttribute('username', 'cart-owner');

    $cart = new Cart;
    $cart->setRelation('owner', $owner);

    expect($cart->owner_label)->toBe('cart-owner');
});
