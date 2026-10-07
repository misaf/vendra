<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\Attributes\UniqueFor;
use Misaf\VendraStore\Actions\RecordStorefrontRuntimeSnapshotAction;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraStore\Support\StorefrontRuntimeSnapshot;
use Misaf\VendraStore\Support\StorefrontRuntimeSnapshots;
use Spatie\Multitenancy\Jobs\NotTenantAware;
use Throwable;

#[Timeout(60)]
#[Tries(1)]
#[UniqueFor(120)]
final class RecordStorefrontRuntimeSnapshotJob implements NotTenantAware, ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $deploymentId, public readonly bool $logs = false)
    {
        $this->onQueue(ProvisionStorefrontJob::QUEUE);
    }

    public function handle(RecordStorefrontRuntimeSnapshotAction $recordSnapshot): void
    {
        $deployment = StorefrontDeployment::query()->find($this->deploymentId);

        if ($deployment !== null) {
            $recordSnapshot->execute($deployment, $this->logs);
        }
    }

    public function uniqueId(): string
    {
        return $this->deploymentId.':'.($this->logs ? 'logs' : 'observation');
    }

    public function failed(?Throwable $exception): void
    {
        $deployment = StorefrontDeployment::query()->find($this->deploymentId);

        if ($deployment !== null) {
            resolve(StorefrontRuntimeSnapshots::class)->put($deployment, new StorefrontRuntimeSnapshot(
                checkedAt: now()->toImmutable(),
                error: 'The storefront runtime check failed. Try again shortly.',
            ), $this->logs);
        }
    }
}
