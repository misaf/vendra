<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Misaf\VendraOrder\Database\Factories\OrderFactory;
use Misaf\VendraOrder\Database\Factories\OrderLineFactory;
use Misaf\VendraOrder\Events\OrderCancelled;
use Misaf\VendraOrder\States\Cancelled;
use Misaf\VendraOrder\States\Pending;
use Misaf\VendraProduct\Database\Factories\ProductFactory;
use Spatie\ModelStates\Exceptions\TransitionNotFound;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('restores each product once when a deducted order is cancelled', function (): void {
    $product = ProductFactory::new()->createOne(['quantity' => 1, 'in_stock' => false]);
    $deleted = ProductFactory::new()->createOne(['quantity' => 0]);
    $order = OrderFactory::new()->createOne(['stock_deducted' => true]);
    OrderLineFactory::new()->forOrder($order)->forSellable($product)->createOne(['quantity' => 2]);
    OrderLineFactory::new()->forOrder($order)->forSellable($product)->createOne(['quantity' => 3]);
    OrderLineFactory::new()->forOrder($order)->forSellable($deleted)->createOne(['quantity' => 4]);
    OrderLineFactory::new()->forOrder($order)->createOne(['sellable_type' => 'unrelated', 'sellable_id' => $product->id, 'quantity' => 9]);
    $deleted->delete();
    $staleOrder = $order->fresh();

    $order->cancel();

    expect($order->fresh())->status->toBeInstanceOf(Cancelled::class)->stock_deducted->toBeFalse()
        ->and($product->fresh())->quantity->toBe(6)->in_stock->toBeFalse()
        ->and($deleted->fresh()?->quantity)->toBe(4)
        ->and(fn () => $staleOrder->cancel())->toThrow(TransitionNotFound::class)
        ->and($product->fresh()?->quantity)->toBe(6);
});

it('rolls back restored stock when cancellation fails later in its transaction', function (): void {
    $product = ProductFactory::new()->createOne(['quantity' => 1]);
    $order = OrderFactory::new()->createOne(['stock_deducted' => true]);
    OrderLineFactory::new()->forOrder($order)->forSellable($product)->createOne(['quantity' => 2]);
    Event::listen(OrderCancelled::class, function () use ($product): void {
        expect($product->fresh()?->quantity)->toBe(3);

        throw new RuntimeException('Cancellation failed.');
    });

    expect(fn () => $order->cancel())->toThrow(RuntimeException::class, 'Cancellation failed.')
        ->and($order->fresh())->status->toBeInstanceOf(Pending::class)->stock_deducted->toBeTrue()
        ->and($product->fresh()?->quantity)->toBe(1);
});

it('does not restore products belonging to another tenant', function (): void {
    $tenant = currentTestTenant();
    $otherTenant = createTestTenant();
    switchToTestTenant($otherTenant);
    $otherProduct = ProductFactory::new()->createOne(['quantity' => 1]);
    switchToTestTenant($tenant);
    $order = OrderFactory::new()->createOne(['stock_deducted' => true]);
    OrderLineFactory::new()->forOrder($order)->forSellable($otherProduct)->createOne(['quantity' => 2]);

    $order->cancel();

    switchToTestTenant($otherTenant);
    expect($otherProduct->fresh()?->quantity)->toBe(1);
    switchToTestTenant($tenant);
});
