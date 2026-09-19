<?php

declare(strict_types=1);

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Misaf\VendraAddress\Database\Factories\AddressFactory;
use Misaf\VendraCart\Database\Factories\CartFactory;
use Misaf\VendraCart\Database\Factories\CartItemFactory;
use Misaf\VendraCart\Models\Cart;
use Misaf\VendraDelivery\Database\Factories\DeliverySlotFactory;
use Misaf\VendraDelivery\Database\Factories\DeliveryZoneFactory;
use Misaf\VendraDelivery\Models\Delivery;
use Misaf\VendraOrder\Actions\CancelOrderAction;
use Misaf\VendraOrder\Database\Factories\OrderFactory;
use Misaf\VendraOrder\Database\Factories\OrderLineFactory;
use Misaf\VendraOrder\Models\Order;
use Misaf\VendraProduct\Database\Factories\ProductCategoryFactory;
use Misaf\VendraProduct\Database\Factories\ProductFactory;
use Misaf\VendraProduct\Database\Factories\ProductPriceFactory;
use Misaf\VendraProduct\Models\Product;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

function orderApiProduct(int $price = 4800, int $quantity = 10): Product
{
    $product = ProductFactory::new()->forCategory(ProductCategoryFactory::new()->active()->createOne())->createOne([
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

it("converts the caller's cart into an order priced from the catalog", function (): void {
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

it('rejects a malformed checkout payload before any order is written', function (array $payload): void {
    $user = createTestUser();
    $cart = CartFactory::new()->forOwner($user)->createOne();
    CartItemFactory::new()->forCart($cart)->forSellable(orderApiProduct())->createOne();

    $this->actingAs($user)
        ->postJson('/api/sales/checkout', ['cartToken' => $cart->token, ...$payload])
        ->assertUnprocessable();

    expect(Order::query()->count())->toBe(0)
        ->and(Delivery::query()->count())->toBe(0);
})->with([
    'currency code not three letters' => [['currencyCode' => 'US']],
    'payment reference too long' => [['paymentReference' => str_repeat('x', 256)]],
    'delivery date in the wrong format' => [['deliveryDate' => '13/09/2026']],
    'recipient name too long' => [['recipientName' => str_repeat('x', 256)]],
]);

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

    DeliveryZoneFactory::new()->active()->chargingWithin(30, 1500)->createOne([
        'name' => ['en' => 'Outside the free zone'],
        'origin_latitude' => 35.6892,
        'origin_longitude' => 51.3890,
        'position' => 1,
    ]);
    $slot = DeliverySlotFactory::new()->active()->window('Afternoon', '12:00:00', '17:00:00')->createOne();
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

    DeliveryZoneFactory::new()->active()->freeWithin(12)->createOne([
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

it('keeps the cart and writes no order when delivery details are rejected', function (string $invalidField): void {
    $this->freezeTime();
    $user = createTestUser();
    $cart = CartFactory::new()->forOwner($user)->createOne();
    $item = CartItemFactory::new()->forCart($cart)->forSellable(orderApiProduct())->createOne();
    DeliveryZoneFactory::new()->active()->freeWithin(30)->createOne([
        'origin_latitude' => 35.6892,
        'origin_longitude' => 51.3890,
    ]);
    $invalidValue = match ($invalidField) {
        'deliverySlotId' => DeliverySlotFactory::new()->createOne(['active' => false])->id,
        'addressId' => AddressFactory::new()->createOne()->id,
        'deliveryDate' => now()->subDay()->toDateString(),
    };

    $this->actingAs($user)->postJson('/api/sales/checkout', [
        'cartToken' => $cart->token,
        'currencyCode' => 'USD',
        'latitude' => 35.6892,
        'longitude' => 51.3890,
        $invalidField => $invalidValue,
    ])->assertUnprocessable();

    $this->assertDatabaseCount('orders', 0);
    $this->assertDatabaseCount('order_lines', 0);
    $this->assertDatabaseCount('deliveries', 0);
    $this->assertModelExists($item);
})->with(['deliverySlotId', 'addressId', 'deliveryDate']);

it('rejects products in a category disabled after they entered the cart', function (): void {
    $user = createTestUser();
    $product = orderApiProduct();
    $cart = CartFactory::new()->forOwner($user)->createOne();
    $item = CartItemFactory::new()->forCart($cart)->forSellable($product)->createOne();
    $product->productCategory->update(['active' => false]);

    $this->actingAs($user)->postJson('/api/sales/checkout', [
        'cartToken' => $cart->token,
        'currencyCode' => 'USD',
    ])->assertUnprocessable();

    $this->assertDatabaseCount('orders', 0);
    $this->assertModelExists($item);
});

it('rolls back the order and restores the cart when writing the delivery fails', function (): void {
    $user = createTestUser();
    $cart = CartFactory::new()->forOwner($user)->createOne();
    $item = CartItemFactory::new()->forCart($cart)->forSellable(orderApiProduct())->createOne();
    DeliveryZoneFactory::new()->active()->freeWithin(30)->createOne([
        'origin_latitude' => 35.6892,
        'origin_longitude' => 51.3890,
    ]);
    Delivery::creating(function (): never {
        throw new RuntimeException('Delivery persistence failed.');
    });

    $this->actingAs($user)->postJson('/api/sales/checkout', [
        'cartToken' => $cart->token,
        'currencyCode' => 'USD',
        'latitude' => 35.6892,
        'longitude' => 51.3890,
    ])->assertServerError();

    $this->assertDatabaseCount('orders', 0);
    $this->assertDatabaseCount('order_lines', 0);
    $this->assertDatabaseCount('deliveries', 0);
    $this->assertModelExists($item);
});

it('names the cart item that cannot be bought', function (Closure $fillCart, Closure $expectedMessage): void {
    $user = createTestUser();
    $cart = CartFactory::new()->forOwner($user)->createOne();
    $context = $fillCart($cart);

    $this->actingAs($user)->postJson('/api/sales/checkout', [
        'cartToken' => $cart->token,
        'currencyCode' => 'USD',
    ])
        ->assertUnprocessable()
        ->assertJsonFragment(['propertyPath' => 'cartToken', 'message' => $expectedMessage($context)]);

    $this->assertDatabaseCount('orders', 0);
})->with([
    'an item that is not a product' => [
        function (Cart $cart): null {
            CartItemFactory::new()->forCart($cart)->forSellable(orderApiProduct())->createOne(['quantity' => 1]);
            CartItemFactory::new()->forCart($cart)->createOne(['sellable_type' => 'gift-card', 'sellable_id' => 1]);

            return null;
        },
        fn (): string => __('vendra-order-api::messages.sellable_unsupported', ['type' => 'gift-card']),
    ],
    'a product without enough stock' => [
        function (Cart $cart): Product {
            $product = orderApiProduct(quantity: 1);
            CartItemFactory::new()->forCart($cart)->forSellable(orderApiProduct())->createOne(['quantity' => 1]);
            CartItemFactory::new()->forCart($cart)->forSellable($product)->createOne(['quantity' => 2]);

            return $product;
        },
        fn (Product $product): string => __('vendra-order-api::messages.out_of_stock', ['product' => $product->id]),
    ],
    'a product without a price in the currency' => [
        function (Cart $cart): Product {
            $product = orderApiProduct();
            $product->productPrices()->delete();
            CartItemFactory::new()->forCart($cart)->forSellable($product)->createOne(['quantity' => 1]);

            return $product;
        },
        fn (Product $product): string => __('vendra-order-api::messages.price_missing', ['product' => $product->id]),
    ],
]);

it('prices and locks a cart of several products with one query each', function (): void {
    $user = createTestUser();
    $cart = CartFactory::new()->forOwner($user)->createOne();
    CartItemFactory::new()->forCart($cart)->forSellable(orderApiProduct(price: 1000))->createOne(['quantity' => 1]);
    CartItemFactory::new()->forCart($cart)->forSellable(orderApiProduct(price: 2500))->createOne(['quantity' => 2]);
    CartItemFactory::new()->forCart($cart)->forSellable(orderApiProduct(price: 300))->createOne(['quantity' => 3]);
    $productQueries = 0;
    DB::listen(function (QueryExecuted $query) use (&$productQueries): void {
        if (preg_match('/^select .* from ["`]products["`]/i', $query->sql) === 1) {
            $productQueries++;
        }
    });

    $this->actingAs($user)->postJson('/api/sales/checkout', [
        'cartToken' => $cart->token,
        'currencyCode' => 'USD',
    ])
        ->assertCreated()
        ->assertJsonPath('itemsAmount', 6900);

    expect($productQueries)->toBe(2);
});

it('takes the ordered stock and refuses a second checkout for the last unit', function (): void {
    $product = orderApiProduct(quantity: 3);
    $firstBuyer = createTestUser();
    $firstCart = CartFactory::new()->forOwner($firstBuyer)->createOne();
    CartItemFactory::new()->forCart($firstCart)->forSellable($product)->createOne(['quantity' => 3]);
    $secondBuyer = createTestUser();
    $secondCart = CartFactory::new()->forOwner($secondBuyer)->createOne();
    $secondItem = CartItemFactory::new()->forCart($secondCart)->forSellable($product)->createOne(['quantity' => 1]);

    $this->actingAs($firstBuyer)
        ->postJson('/api/sales/checkout', ['cartToken' => $firstCart->token, 'currencyCode' => 'USD'])
        ->assertCreated();

    expect($product->fresh()?->quantity)->toBe(0);

    $this->actingAs($secondBuyer)
        ->postJson('/api/sales/checkout', ['cartToken' => $secondCart->token, 'currencyCode' => 'USD'])
        ->assertUnprocessable();

    expect(Order::query()->count())->toBe(1)
        ->and($product->fresh()?->quantity)->toBe(0);
    $this->assertModelExists($secondItem);
});

it('returns the stock when a placed order is cancelled', function (): void {
    $user = createTestUser();
    $product = orderApiProduct(quantity: 5);
    $cart = CartFactory::new()->forOwner($user)->createOne();
    CartItemFactory::new()->forCart($cart)->forSellable($product)->createOne(['quantity' => 2]);

    $this->actingAs($user)
        ->postJson('/api/sales/checkout', ['cartToken' => $cart->token, 'currencyCode' => 'USD'])
        ->assertCreated();

    expect($product->fresh()?->quantity)->toBe(3);

    resolve(CancelOrderAction::class)->execute(Order::query()->sole());

    expect($product->fresh()?->quantity)->toBe(5);
});
