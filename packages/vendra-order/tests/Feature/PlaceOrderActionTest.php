<?php

declare(strict_types=1);

use Misaf\VendraCart\Database\Factories\CartFactory;
use Misaf\VendraCart\Database\Factories\CartItemFactory;
use Misaf\VendraOrder\Actions\PlaceOrderAction;
use Misaf\VendraOrder\Data\OrderLineDraft;
use Misaf\VendraOrder\States\Pending;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('converts a cart into a pending order with snapshot lines', function (): void {
    $customer = createTestUser();
    $cart = CartFactory::new()->forOwner($customer)->create();
    CartItemFactory::new()->forCart($cart)->create(['quantity' => 2]);

    $order = resolve(PlaceOrderAction::class)->execute(
        cart: $cart,
        currencyCode: 'USD',
        lines: [
            new OrderLineDraft(
                sellable: $customer,
                name: ['en' => 'Marigold Morning'],
                unitAmount: 4800,
                quantity: 2,
            ),
            new OrderLineDraft(
                sellable: $customer,
                name: ['en' => 'Quiet Sage'],
                unitAmount: 4200,
                quantity: 1,
            ),
        ],
        customer: $customer,
        deliveryAmount: 1500,
        cardMessage: 'Happy birthday.',
        paymentReference: 'TRF-8891',
    );

    expect($order->status)->toBeInstanceOf(Pending::class)
        ->and($order->number)->toStartWith('ORD-')
        ->and($order->items_amount->getAmount())->toBe('13800')
        ->and($order->delivery_amount->getAmount())->toBe('1500')
        ->and($order->total_amount->getAmount())->toBe('15300')
        ->and($order->payment_reference)->toBe('TRF-8891')
        ->and($order->card_message)->toBe('Happy birthday.')
        ->and($order->placed_at)->not->toBeNull()
        ->and($order->cart_id)->toBe($cart->id)
        ->and($order->lines()->count())->toBe(2)
        ->and($order->lines()->first()->line_amount->getAmount())->toBe('9600');
});

it('clears the converted cart items but keeps the cart', function (): void {
    $customer = createTestUser();
    $cart = CartFactory::new()->forOwner($customer)->create();
    CartItemFactory::new()->forCart($cart)->create();

    resolve(PlaceOrderAction::class)->execute(
        cart: $cart,
        currencyCode: 'USD',
        lines: [new OrderLineDraft($customer, ['en' => 'Bazaar Bunch'], 3800)],
        customer: $customer,
    );

    expect($cart->items()->count())->toBe(0)
        ->and($cart->fresh())->not->toBeNull();
});

it('rejects a stale cart after its contents have already been checked out', function (): void {
    $customer = createTestUser();
    $cart = CartFactory::new()->forOwner($customer)->createOne();
    CartItemFactory::new()->forCart($cart)->createOne();
    $staleCart = $cart->fresh()->load('items');
    $lines = [new OrderLineDraft($customer, ['en' => 'Bouquet'], 3800)];
    $action = resolve(PlaceOrderAction::class);
    $action->execute($cart, 'USD', $lines, $customer);

    expect(fn () => $action->execute($staleCart, 'USD', $lines, $customer))
        ->toThrow(RuntimeException::class, 'Cannot place an order from an empty or already checked out cart.');

    $this->assertDatabaseCount('orders', 1);
    $this->assertDatabaseCount('order_lines', 1);

    CartItemFactory::new()->forCart($cart)->createOne();
    $action->execute($cart, 'USD', $lines, $customer);

    $this->assertDatabaseCount('orders', 2);
});
