<?php

declare(strict_types=1);

use Misaf\VendraTransaction\Actions\SetDefaultTransactionGatewayAction;
use Misaf\VendraTransaction\Database\Factories\TransactionGatewayFactory;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('makes the first active gateway the default', function (): void {
    $gateway = TransactionGatewayFactory::new()->createOne();

    expect($gateway->is_default)->toBeTrue();
});

it('does not allow the only default gateway to be unset', function (): void {
    $gateway = TransactionGatewayFactory::new()->default()->createOne();

    $gateway->update(['is_default' => false]);

    expect($gateway->refresh()->is_default)->toBeTrue();
});

it('promotes the first ordered gateway when the default is deleted', function (): void {
    $default = TransactionGatewayFactory::new()->default()->createOne();
    $next = TransactionGatewayFactory::new()->createOne();

    $default->delete();

    expect($default->refresh()->is_default)->toBeFalse()
        ->and($next->refresh()->is_default)->toBeTrue();
});

it('promotes the first active gateway when the default becomes inactive', function (): void {
    $default = TransactionGatewayFactory::new()->default()->createOne();
    $next = TransactionGatewayFactory::new()->createOne();

    $default->update(['active' => false]);

    expect($default->refresh()->active)->toBeFalse()
        ->and($default->is_default)->toBeFalse()
        ->and($next->refresh()->is_default)->toBeTrue();
});

it('clears the default flag when creating an inactive gateway', function (): void {
    $gateway = TransactionGatewayFactory::new()->inactive()->default()->createOne();

    expect($gateway->refresh()->is_default)->toBeFalse();
});

it('switches the default gateway through the domain action', function (): void {
    $first = TransactionGatewayFactory::new()->default()->createOne();
    $second = TransactionGatewayFactory::new()->createOne();

    (new SetDefaultTransactionGatewayAction)->execute($second);

    expect($first->refresh()->is_default)->toBeFalse()
        ->and($second->refresh()->is_default)->toBeTrue();
});

it('activates an inactive gateway when it becomes the default', function (): void {
    TransactionGatewayFactory::new()->default()->createOne();
    $gateway = TransactionGatewayFactory::new()->inactive()->createOne();

    (new SetDefaultTransactionGatewayAction)->execute($gateway);

    expect($gateway->refresh()->active)->toBeTrue()
        ->and($gateway->is_default)->toBeTrue();
});
