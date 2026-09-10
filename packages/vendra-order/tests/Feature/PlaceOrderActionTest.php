<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Misaf\VendraCart\Database\Factories\CartFactory;
use Misaf\VendraCart\Database\Factories\CartItemFactory;
use Misaf\VendraOrder\Actions\PlaceOrderAction;
use Misaf\VendraOrder\Data\OrderLineDraft;
use Misaf\VendraOrder\Models\Order;
use Misaf\VendraOrder\States\Pending;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('converts a cart into a pending order with snapshot lines', function (): void {
    $customer = createTestUser();
    $cart = CartFactory::new()->forOwner($customer)->create();
    CartItemFactory::new()->forCart($cart)->create(['quantity' => 2]);

    $order = app(PlaceOrderAction::class)->execute(
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

    app(PlaceOrderAction::class)->execute(
        cart: $cart,
        currencyCode: 'USD',
        lines: [new OrderLineDraft($customer, ['en' => 'Bazaar Bunch'], 3800)],
        customer: $customer,
    );

    expect($cart->items()->count())->toBe(0)
        ->and($cart->fresh())->not->toBeNull();
});

it('refuses to place an order without lines', function (): void {
    $cart = CartFactory::new()->create();

    expect(fn (): Order => app(PlaceOrderAction::class)->execute(
        cart: $cart,
        currencyCode: 'USD',
        lines: [],
    ))->toThrow(ValidationException::class);

    expect(Order::query()->count())->toBe(0);
});

it('refuses a line with a quantity below one', function (): void {
    $customer = createTestUser();
    $cart = CartFactory::new()->forOwner($customer)->create();

    expect(fn (): Order => app(PlaceOrderAction::class)->execute(
        cart: $cart,
        currencyCode: 'USD',
        lines: [new OrderLineDraft($customer, ['en' => 'Winter Wheat'], 4400, 0)],
    ))->toThrow(ValidationException::class);

    expect(Order::query()->count())->toBe(0)
        ->and(Validator::make([], [])->passes())->toBeTrue();
});
