<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Contracts;

/**
 * The platform's side of every charge: the tax it adds and the seller named on invoices.
 */
interface BillingProfile
{
    /**
     * The tax rate in basis points, so 1900 is 19%.
     */
    public function taxRate(): int;

    public function taxLabel(): string;

    /**
     * @return array{name: string, address: string|null, tax_id: string|null}
     */
    public function seller(): array;
}
