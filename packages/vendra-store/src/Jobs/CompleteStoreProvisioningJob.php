<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Misaf\VendraStore\Actions\CompleteStoreProvisioningAction;
use Misaf\VendraStore\Actions\FailStoreProvisioningAction;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraSupport\Context\ContextKeys;
use Misaf\VendraSupport\Context\RequestJobContext;
use Spatie\Multitenancy\Jobs\NotTenantAware;
use Throwable;

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

    public function handle(CompleteStoreProvisioningAction $completeStoreProvisioningAction): void
    {
        $store = Store::query()->findOrFail($this->tenantId);

        $this->context($store)->scope(fn () => $completeStoreProvisioningAction->execute($store));
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

        $this->context($store)->scope(fn () => resolve(FailStoreProvisioningAction::class)->execute($this->tenantId, $exception));
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
