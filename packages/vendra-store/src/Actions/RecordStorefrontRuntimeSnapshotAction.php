<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Misaf\VendraStore\Contracts\StorefrontProvisioner;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraStore\Support\StorefrontReference;
use Misaf\VendraStore\Support\StorefrontRuntimeSnapshot;
use Misaf\VendraStore\Support\StorefrontRuntimeSnapshots;
use PDOException;
use Throwable;

final readonly class RecordStorefrontRuntimeSnapshotAction
{
    public function __construct(
        private StorefrontProvisioner $provisioner,
        private StorefrontRuntimeSnapshots $snapshots,
    ) {}

    public function execute(StorefrontDeployment $deployment, bool $logs = false): void
    {
        try {
            $reference = StorefrontReference::for($deployment);
            $snapshot = new StorefrontRuntimeSnapshot(
                checkedAt: now()->toImmutable(),
                observation: $logs ? null : $this->provisioner->observe($reference),
                logs: $logs ? $this->provisioner->logs($reference) : null,
            );
        } catch (Throwable $exception) {
            report($exception);

            $snapshot = new StorefrontRuntimeSnapshot(
                checkedAt: now()->toImmutable(),
                error: $exception instanceof PDOException ? 'The storefront runtime check failed.' : $exception->getMessage(),
            );
        }

        $this->snapshots->put($deployment, $snapshot, $logs);
    }
}
