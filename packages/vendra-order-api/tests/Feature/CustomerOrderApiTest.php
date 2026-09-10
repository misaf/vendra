<?php

declare(strict_types=1);

use Misaf\VendraCart\Database\Factories\CartFactory;
use Misaf\VendraCart\Database\Factories\CartItemFactory;
use Misaf\VendraDelivery\Database\Factories\DeliverySlotFactory;
use Misaf\VendraDelivery\Database\Factories\DeliveryZoneFactory;
use Misaf\VendraDelivery\Models\Delivery;
use Misaf\VendraOrder\Database\Factories\OrderFactory;
use Misaf\VendraOrder\Database\Factories\OrderLineFactory;
use Misaf\VendraOrder\Models\Order;
use Misaf\VendraProduct\Database\Factories\ProductFactory;
use Misaf\VendraProduct\Database\Factories\ProductPriceFactory;
use Misaf\VendraProduct\Models\Product;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

function orderApiProduct(int $price = 4800, int $quantity = 10): Product
{
    $product = ProductFactory::new()->createOne([
        'in_stock' => true,
        'quantity' => $quantity,
    ]);

    ProductPriceFactory::new()->forProduct($product)->createOne([
        'currency_code' => 'USD',
        'price' => $price,
    ]);

    return $product;
}

it('requires authentication and exposes only orders placed by the user', function (): void {
    $user = createTestUser();
    $order = OrderFactory::new()->forCustomer($user)->createOne();
    $line = OrderLineFactory::new()->forOrder($order)->createOne();
    $hidden = OrderFactory::new()->forCustomer(createTestUser())->createOne();

    $this->getJson('/api/sales/orders')->assertUnauthorized();

    $this->actingAs($user)
        ->getJson('/api/sales/orders', ['Accept' => 'application/ld+json'])
        ->assertOk()
        ->assertJsonPath('totalItems', 1)
        ->assertJsonPath('member.0.id', $order->id)
        ->assertJsonPath('member.0.number', $order->number)
        ->assertJsonPath('member.0.status', 'pending')
        ->assertJsonPath('member.0.lines.0.id', $line->id)
        ->assertJsonMissing(['id' => $hidden->id]);
});

it('denies access to an order placed by another user', function (): void {
    $order = OrderFactory::new()->forCustomer(createTestUser())->createOne();

    $this->actingAs(createTestUser())
        ->getJson("/api/sales/orders/{$order->id}", ['Accept' => 'application/ld+json'])
        ->assertNotFound();
});

it('converts the caller\'s cart into an order priced from the catalog', function (): void {
    $user = createTestUser();
    $product = orderApiProduct(price: 4800);
    $cart = CartFactory::new()->forOwner($user)->createOne();
    CartItemFactory::new()->forCart($cart)->forSellable($product)->createOne(['quantity' => 2]);

    $this->actingAs($user)
        ->postJson('/api/sales/checkout', [
            'cartToken' => $cart->token,
            'currencyCode' => 'USD',
            'paymentReference' => 'TRF-8891',
            'cardMessage' => 'Happy birthday.',
        ])
        ->assertCreated()
        ->assertJsonPath('status', 'pending')
        ->assertJsonPath('itemsAmount', 9600)
        ->assertJsonPath('deliveryAmount', 0)
        ->assertJsonPath('totalAmount', 9600)
        ->assertJsonPath('paymentReference', 'TRF-8891')
        ->assertJsonPath('lines.0.unitAmount', 4800)
        ->assertJsonPath('lines.0.quantity', 2)
        ->assertJsonPath('lines.0.name', $product->getTranslation('name', 'en'));

    expect(Order::query()->count())->toBe(1)
        ->and($cart->items()->count())->toBe(0);
});

it('rejects checkout for a cart the caller does not own', function (): void {
    $cart = CartFactory::new()->forOwner(createTestUser())->createOne();
    CartItemFactory::new()->forCart($cart)->forSellable(orderApiProduct())->createOne();

    $this->actingAs(createTestUser())
        ->postJson('/api/sales/checkout', ['cartToken' => $cart->token])
        ->assertUnprocessable();

    expect(Order::query()->count())->toBe(0);
});

it('rejects checkout when the product has no stock left', function (): void {
    $user = createTestUser();
    $product = orderApiProduct(quantity: 1);
    $cart = CartFactory::new()->forOwner($user)->createOne();
    CartItemFactory::new()->forCart($cart)->forSellable($product)->createOne(['quantity' => 3]);

    $this->actingAs($user)
        ->postJson('/api/sales/checkout', ['cartToken' => $cart->token])
        ->assertUnprocessable();

    expect(Order::query()->count())->toBe(0);
});

it('rejects checkout when the cart is empty', function (): void {
    $user = createTestUser();
    $cart = CartFactory::new()->forOwner($user)->createOne();

    $this->actingAs($user)
        ->postJson('/api/sales/checkout', ['cartToken' => $cart->token])
        ->assertUnprocessable();

    expect(Order::query()->count())->toBe(0);
});

it('prices delivery from the dropped pin and schedules it', function (): void {
    $user = createTestUser();
    $product = orderApiProduct(price: 4800);
    $cart = CartFactory::new()->forOwner($user)->createOne();
    CartItemFactory::new()->forCart($cart)->forSellable($product)->createOne(['quantity' => 1]);

    DeliveryZoneFactory::new()->chargingWithin(30, 1500)->createOne([
        'name' => ['en' => 'Outside the free zone'],
        'origin_latitude' => 35.6892,
        'origin_longitude' => 51.3890,
        'position' => 1,
    ]);
    $slot = DeliverySlotFactory::new()->window('Afternoon', '12:00:00', '17:00:00')->createOne();
    $date = now()->addDay()->toDateString();

    $this->actingAs($user)
        ->postJson('/api/sales/checkout', [
            'cartToken' => $cart->token,
            'currencyCode' => 'USD',
            'latitude' => 35.7219,
            'longitude' => 51.2334,
            'deliveryDate' => $date,
            'deliverySlotId' => $slot->id,
            'recipientName' => 'Nasrin K.',
        ])
        ->assertCreated()
        ->assertJsonPath('itemsAmount', 4800)
        ->assertJsonPath('deliveryAmount', 1500)
        ->assertJsonPath('totalAmount', 6300);

    $delivery = Delivery::query()->firstOrFail();

    expect($delivery->delivery_slot_id)->toBe($slot->id)
        ->and($delivery->scheduled_for?->toDateString())->toBe($date)
        ->and($delivery->recipient_name)->toBe('Nasrin K.')
        ->and($delivery->fee_amount->getAmount())->toBe('1500');
});

it('refuses checkout to an address beyond every delivery band', function (): void {
    $user = createTestUser();
    $product = orderApiProduct();
    $cart = CartFactory::new()->forOwner($user)->createOne();
    CartItemFactory::new()->forCart($cart)->forSellable($product)->createOne();

    DeliveryZoneFactory::new()->freeWithin(12)->createOne([
        'origin_latitude' => 35.6892,
        'origin_longitude' => 51.3890,
        'position' => 1,
    ]);

    $this->actingAs($user)
        ->postJson('/api/sales/checkout', [
            'cartToken' => $cart->token,
            'latitude' => 32.6546,
            'longitude' => 51.6680,
        ])
        ->assertUnprocessable();

    expect(Order::query()->count())->toBe(0)
        ->and(Delivery::query()->count())->toBe(0);
});

it('places an order without delivery when no pin is dropped', function (): void {
    $user = createTestUser();
    $product = orderApiProduct();
    $cart = CartFactory::new()->forOwner($user)->createOne();
    CartItemFactory::new()->forCart($cart)->forSellable($product)->createOne();

    $this->actingAs($user)
        ->postJson('/api/sales/checkout', ['cartToken' => $cart->token])
        ->assertCreated()
        ->assertJsonPath('deliveryAmount', 0);

    expect(Delivery::query()->count())->toBe(0);
});
