<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Misaf\VendraCart\Actions\AddCartItemAction;
use Misaf\VendraCart\Models\Cart;
use Misaf\VendraCart\Models\CartItem;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('adds a sellable to the cart', function (): void {
    $cart = Cart::factory()->create();
    $sellable = new class extends Model
    {
        use HasFactory;
    };
    $sellable->setAttribute('id', 7);

    $item = (new AddCartItemAction)->execute($cart, $sellable, quantity: 2, metadata: ['color' => 'red']);

    expect($item->exists)->toBeTrue()
        ->and($item->cart_id)->toBe($cart->id)
        ->and($item->sellable_type)->toBe($sellable->getMorphClass())
        ->and($item->sellable_id)->toBe(7)
        ->and($item->quantity)->toBe(2)
        ->and($item->metadata)->toBe(['color' => 'red']);
});

it('merges quantities when the sellable is already in the cart', function (): void {
    $cart = Cart::factory()->create();
    $sellable = new class extends Model
    {
        use HasFactory;
    };
    $sellable->setAttribute('id', 7);
    $action = new AddCartItemAction;

    $action->execute($cart, $sellable, quantity: 2);
    $item = $action->execute($cart, $sellable, quantity: 3);

    expect($cart->items()->count())->toBe(1)
        ->and($item->quantity)->toBe(5);
});

it('prunes expired carts together with their items', function (): void {
    $expired = Cart::factory()->create(['expires_at' => now()->subMinute()]);
    CartItem::factory()->forCart($expired)->create();

    $active = Cart::factory()->create(['expires_at' => now()->addDay()]);
    $everlasting = Cart::factory()->create(['expires_at' => null]);

    $this->artisan('vendra-cart:prune-expired')->assertSuccessful();

    expect(Cart::query()->orderBy('id')->pluck('id')->all())->toBe([$active->id, $everlasting->id])
        ->and(CartItem::query()->where('cart_id', $expired->id)->count())->toBe(0);
});

it('defaults the cart expiry from configuration', function (): void {
    $this->freezeTime();
    config()->set('vendra-cart.expires_after_days', 3);

    $cart = Cart::query()->create(['token' => (string) Str::uuid()]);

    expect($cart->expires_at?->toIso8601String())->toBe(now()->addDays(3)->toIso8601String());
});

it('honours an explicit or disabled expiry', function (): void {
    $everlasting = Cart::query()->create([
        'token' => (string) Str::uuid(),
        'expires_at' => null,
    ]);

    config()->set('vendra-cart.expires_after_days', null);
    $unset = Cart::query()->create(['token' => (string) Str::uuid()]);

    expect($everlasting->expires_at)->toBeNull()
        ->and($unset->expires_at)->toBeNull();
});

it('schedules expired cart pruning daily', function (): void {
    $scheduled = collect(resolve(Schedule::class)->events())->filter(
        fn (object $event): bool => str_contains((string) $event->command, 'vendra-cart:prune-expired'),
    );

    expect($scheduled)->toHaveCount(1)
        ->and($scheduled->first()->expression)->toBe('0 0 * * *');
});
