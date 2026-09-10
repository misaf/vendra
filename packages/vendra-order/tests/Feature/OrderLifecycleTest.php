<?php

declare(strict_types=1);

use Misaf\VendraOrder\Database\Factories\OrderFactory;
use Misaf\VendraOrder\States\Cancelled;
use Misaf\VendraOrder\States\Completed;
use Misaf\VendraOrder\States\Confirmed;
use Misaf\VendraOrder\States\Pending;
use Spatie\ModelStates\Exceptions\TransitionNotFound;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('places new orders in the pending state', function (): void {
    expect(OrderFactory::new()->createOne()->status)->toBeInstanceOf(Pending::class);
});

it('moves an order from pending through confirmed to completed', function (): void {
    $order = OrderFactory::new()->createOne();

    $order->confirm();

    expect($order->status)->toBeInstanceOf(Confirmed::class);

    $order->complete();

    expect($order->status)->toBeInstanceOf(Completed::class)
        ->and($order->status->isFinal())->toBeTrue();
});

it('cancels an order from either open state', function (string $state): void {
    $order = OrderFactory::new()->withStatus($state)->createOne();

    $order->cancel();

    expect($order->status)->toBeInstanceOf(Cancelled::class);
})->with([
    'pending' => [Pending::class],
    'confirmed' => [Confirmed::class],
]);

it('refuses to complete an order that was never confirmed', function (): void {
    $order = OrderFactory::new()->createOne();

    expect(fn (): mixed => $order->complete())->toThrow(TransitionNotFound::class);
});

it('refuses to reopen a cancelled order', function (): void {
    $order = OrderFactory::new()->withStatus(Cancelled::class)->createOne();

    expect(fn (): mixed => $order->confirm())->toThrow(TransitionNotFound::class)
        ->and($order->status->isFinal())->toBeTrue();
});
