<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Contracts;

use Misaf\VendraStore\Support\StorefrontObservation;
use Misaf\VendraStore\Support\StorefrontProvisionRequest;
use Misaf\VendraStore\Support\StorefrontProvisionResult;
use Misaf\VendraStore\Support\StorefrontReference;

interface StorefrontProvisioner
{
    /**
     * Provision the storefront, replacing any predecessor.
     *
     * Must be idempotent, since reconciliation and retries depend on it.
     */
    public function provision(StorefrontProvisionRequest $request): StorefrontProvisionResult;

    /**
     * Start a deployed storefront; one already running is a success.
     */
    public function start(StorefrontReference $storefront): void;

    /**
     * Stop a storefront without removing it; one already stopped is a success.
     */
    public function stop(StorefrontReference $storefront): void;

    public function restart(StorefrontReference $storefront): void;

    /**
     * Remove the storefront; one already absent is a success.
     */
    public function destroy(StorefrontReference $storefront): void;

    /**
     * Observe what is currently running for the storefront.
     *
     * Throw rather than report "absent" for an unreachable runtime, or
     * reconciliation will rebuild a healthy storefront.
     */
    public function observe(StorefrontReference $storefront): StorefrontObservation;

    public function logs(StorefrontReference $storefront, int $lines = 200): string;
}
