<?php

declare(strict_types=1);

use Misaf\VendraOrder\Actions\CancelOrderAction;
use Misaf\VendraOrder\Actions\CompleteOrderAction;
use Misaf\VendraOrder\Actions\ConfirmOrderAction;
use Misaf\VendraOrder\Database\Factories\OrderFactory;
use Misaf\VendraOrder\States\Cancelled;
use Misaf\VendraOrder\States\Completed;
use Misaf\VendraOrder\States\Confirmed;
use Misaf\VendraOrder\States\Pending;
use Spatie\ModelStates\Exceptions\TransitionNotFound;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('confirms a pending order through the domain action', function (): void {
    $order = OrderFactory::new()->createOne();

    resolve(ConfirmOrderAction::class)->execute($order);

    expect($order->fresh()?->status)->toBeInstanceOf(Confirmed::class);
});

it('completes a confirmed order through the domain action', function (): void {
    $order = OrderFactory::new()->withStatus(Confirmed::class)->createOne();

    resolve(CompleteOrderAction::class)->execute($order);

    expect($order->fresh()?->status)->toBeInstanceOf(Completed::class);
});

it('cancels an order from either open state through the domain action', function (string $state): void {
    $order = OrderFactory::new()->withStatus($state)->createOne();

    resolve(CancelOrderAction::class)->execute($order);

    expect($order->fresh()?->status)->toBeInstanceOf(Cancelled::class);
})->with([
    'pending' => [Pending::class],
    'confirmed' => [Confirmed::class],
]);

it('refuses to complete an order that was never confirmed', function (): void {
    $order = OrderFactory::new()->createOne();

    expect(fn (): mixed => resolve(CompleteOrderAction::class)->execute($order))
        ->toThrow(TransitionNotFound::class);
});
