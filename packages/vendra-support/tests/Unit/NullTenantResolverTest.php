<?php

declare(strict_types=1);

use Misaf\VendraSupport\Tenancy\NullTenantResolver;

it('executes the callback directly when tenancy is disabled', function (): void {
    $resolver = new NullTenantResolver;

    expect($resolver->execute(1, fn (): string => 'ran'))->toBe('ran');
});

it('runs the callback once for every tenant when tenancy is disabled', function (): void {
    $resolver = new NullTenantResolver;
    $runs = 0;

    $resolver->eachTenant(function () use (&$runs): void {
        $runs++;
    });

    expect($runs)->toBe(1);
});
