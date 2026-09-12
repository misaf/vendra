<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Str;
use Misaf\VendraStore\Contracts\StoreResellerResolver;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraSupport\Context\ContextKeys;
use Misaf\VendraSupport\Context\RequestJobContext;
use Misaf\VendraSupport\Tenancy\Events\TenantProvisioned;
use Misaf\VendraTenant\Enums\TenantProvisioningStatus;
use Misaf\VendraTenant\Jobs\CacheTenantRoutesJob;
use Spatie\Multitenancy\Jobs\NotTenantAware;
use Throwable;

/**
 * Finishes provisioning one store off the request lifecycle: seeding, route
 * caching, and the switch to active.
 *
 * It is the application-orchestration half of store creation, so it lives
 * beside the domain rather than inside it — the action decides a store should
 * exist, this makes the slow parts happen and records whether they did.
 */
#[Timeout(60)]
#[Tries(5)]
final class CompleteStoreProvisioningJob implements NotTenantAware, ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $tenantId) {}

    /**
     * @return list<WithoutOverlapping>
     */
    public function middleware(): array
    {
        return [
            new WithoutOverlapping("store-provisioning:{$this->tenantId}")
                ->releaseAfter(5)
                ->expireAfter(120),
        ];
    }

    public function handle(): void
    {
        $store = Store::query()->findOrFail($this->tenantId);

        $this->context($store)->scope(fn () => $this->provision($store));
    }

    private function provision(Store $store): void
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

            $billingSuspendedAt = $this->shouldStartBillingSuspended($store)
                ? now()
                : null;

            $store->forceFill([
                'active' => true,
                'billing_suspended_at' => $billingSuspendedAt,
                'provisioning_status' => TenantProvisioningStatus::Ready,
                'provisioned_at' => now(),
                'provisioning_failed_at' => null,
                'provisioning_error' => null,
            ])->save();
        } catch (Throwable $exception) {
            $this->markFailed($exception);

            throw $exception;
        }
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [5, 30, 120, 300];
    }

    public function failed(?Throwable $exception): void
    {
        $store = Store::query()->find($this->tenantId);

        $this->context($store)->scope(fn () => $this->markFailed($exception));
    }

    /**
     * A store whose billing reseller is not paying starts suspended.
     *
     * A store created straight from the console has no reseller, so there is
     * nobody to suspend for and it starts active. The reseller is looked up through
     * {@see StoreResellerResolver} so this package never names the reseller domain;
     * a reseller key that resolves to nothing fails closed.
     */
    private function shouldStartBillingSuspended(Store $store): bool
    {
        if ($store->reseller_id === null) {
            return false;
        }

        $reseller = resolve(StoreResellerResolver::class)->find($store->reseller_id);

        return $reseller === null
            || ! $reseller->isSubscriptionActive()
            || $reseller->activeSubscription() === null;
    }

    private function markFailed(?Throwable $exception): void
    {
        Store::query()
            ->whereKey($this->tenantId)
            ->where('provisioning_status', '!=', TenantProvisioningStatus::Ready->value)
            ->update([
                'active' => false,
                'provisioning_status' => TenantProvisioningStatus::Failed->value,
                'provisioning_failed_at' => now(),
                'provisioning_error' => $exception === null
                    ? 'Store provisioning failed.'
                    : Str::limit($exception->getMessage(), 2000, ''),
            ]);
    }

    private function context(?Store $store): RequestJobContext
    {
        return new RequestJobContext(
            traceId: RequestJobContext::resolveTraceId(),
            operation: 'store_provisioning',
            tenantId: $this->tenantId,
            metadata: [ContextKeys::RESELLER_ID => $store?->reseller_id],
        );
    }
}
