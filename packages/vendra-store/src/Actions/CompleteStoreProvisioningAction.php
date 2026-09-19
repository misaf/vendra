<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Misaf\VendraStore\Contracts\StoreResellerResolver;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraSupport\Tenancy\Events\TenantProvisioned;
use Misaf\VendraTenant\Enums\TenantProvisioningStatus;
use Misaf\VendraTenant\Jobs\CacheTenantRoutesJob;
use Throwable;

/**
 * Each step saves its own checkpoint instead of sharing a transaction, so a
 * retry resumes after the last finished step. Seeding and route caching are
 * not rolled back by a database transaction anyway.
 */
final readonly class CompleteStoreProvisioningAction
{
    public function __construct(
        private StoreResellerResolver $resellerResolver,
        private FailStoreProvisioningAction $failStoreProvisioningAction,
    ) {}

    /**
     * @throws Throwable
     */
    public function execute(Store $store): void
    {
        if ($store->provisioning_status === TenantProvisioningStatus::Ready) {
            return;
        }

        $store->forceFill([
            'active' => false,
            'provisioning_status' => TenantProvisioningStatus::Processing,
            'provisioning_failed_at' => null,
            'provisioning_error' => null,
        ])->save();

        try {
            if ($store->provisioning_should_seed && $store->provisioning_seeded_at === null) {
                event(new TenantProvisioned($store, shouldSeed: true));

                $store->forceFill(['provisioning_seeded_at' => now()])->save();
            }

            if ($store->routes_cached_at === null) {
                dispatch_sync(new CacheTenantRoutesJob($store->id));

                $store->forceFill(['routes_cached_at' => now()])->save();
            }

            $store->forceFill([
                'active' => true,
                'billing_suspended_at' => $this->shouldStartBillingSuspended($store) ? now() : null,
                'provisioning_status' => TenantProvisioningStatus::Ready,
                'provisioned_at' => now(),
                'provisioning_failed_at' => null,
                'provisioning_error' => null,
            ])->save();
        } catch (Throwable $exception) {
            $this->failStoreProvisioningAction->execute($store->id, $exception);

            throw $exception;
        }
    }

    /**
     * Determine if the store should start suspended because its reseller is not paying.
     *
     * A store without a reseller starts active; an unresolvable reseller fails closed.
     */
    private function shouldStartBillingSuspended(Store $store): bool
    {
        if ($store->reseller_id === null) {
            return false;
        }

        $reseller = $this->resellerResolver->find($store->reseller_id);

        return $reseller === null
            || ! $reseller->canHoldUnits()
            || $reseller->activeSubscription() === null;
    }
}
