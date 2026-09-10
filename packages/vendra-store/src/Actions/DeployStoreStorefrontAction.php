<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Misaf\VendraStore\Contracts\StorefrontProvisioner;
use Misaf\VendraStore\Enums\StorefrontDeploymentStatus;
use Misaf\VendraStore\Enums\StorefrontDesiredState;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraStore\Support\StorefrontProvisionRequest;

/**
 * Deploys or redeploys one store's storefront, and records what happened.
 *
 * Deploy and redeploy are the same operation: the provisioner replaces whatever
 * is there, so a first deployment, a configuration change, and a moved image tag
 * all take this path. That is what makes retrying safe.
 *
 * Nothing here is transactional with the database on purpose. A container cannot
 * join a SQL transaction, so the row is moved to Processing first, the runtime
 * work happens outside, and the outcome is written afterwards — a crash in
 * between leaves a row that says "in progress", which reconciliation can fix,
 * rather than a committed lie in either direction.
 */
final class DeployStoreStorefrontAction
{
    public function __construct(
        private readonly StorefrontProvisioner $provisioner,
    ) {}

    /**
     * @param  bool  $force  redeploy even a storefront already recorded as ready
     */
    public function execute(StorefrontDeployment $deployment, bool $force = false): StorefrontDeploymentStatus
    {
        if (! $force && $deployment->status === StorefrontDeploymentStatus::Ready) {
            return $deployment->status;
        }

        $deployment->markProcessing();

        $request = StorefrontProvisionRequest::for($deployment);
        $result = $this->provisioner->provision($request);

        if ($result->ready) {
            $deployment->markReady($result->reference, $request->image, $result->imageDigest);
        } else {
            $deployment->markRequested($result->reference, $request->image, $result->imageDigest);
        }

        /*
         | A deployment is an instruction to run, so it also settles the intent:
         | a storefront that was deliberately stopped and is then redeployed is
         | meant to be up again.
         */
        $deployment->markDesiredState(StorefrontDesiredState::Running);

        return $deployment->status;
    }
}
