<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use LogicException;
use Misaf\VendraStore\Enums\StorefrontDeploymentStatus;
use Misaf\VendraStore\Jobs\ProvisionStorefrontJob;
use Misaf\VendraStore\Models\StorefrontDeployment;

final class RetryFailedStorefrontDeploymentAction
{
    public function execute(StorefrontDeployment $deployment): void
    {
        if ($deployment->status !== StorefrontDeploymentStatus::Failed) {
            throw new LogicException("Storefront deployment [{$deployment->id}] is not failed.");
        }

        ProvisionStorefrontJob::dispatch($deployment->id)->afterCommit();
    }
}
