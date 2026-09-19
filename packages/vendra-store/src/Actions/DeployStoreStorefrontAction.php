<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Misaf\VendraStore\Contracts\StorefrontProvisioner;
use Misaf\VendraStore\Enums\StorefrontDeploymentStatus;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraStore\Support\StorefrontProvisionRequest;

/**
 * The provisioner replaces whatever is running, so retrying is safe. The row is
 * marked as processing before the runtime work, so a crash leaves a state that
 * reconciliation can repair.
 */
final readonly class DeployStoreStorefrontAction
{
    public function __construct(
        private StorefrontProvisioner $provisioner,
    ) {}

    /**
     * Pass `$force` to redeploy a storefront that is already ready.
     */
    public function execute(StorefrontDeployment $deployment, bool $force = false): StorefrontDeploymentStatus
    {
        if (! $force && $deployment->status === StorefrontDeploymentStatus::Ready) {
            return $deployment->status;
        }

        /*
         | The deployment reads intent and never writes it. A queued retry that
         | fires after a store was suspended or offboarded would otherwise bring
         | its storefront back up; callers that mean "run it" record Running
         | before dispatching, as RedeployStoreStorefrontAction does. A row a
         | thrown attempt left Processing is settled as Failed, so it does not
         | read as in progress forever; convergence redeploys it once the intent
         | is Running again.
         */
        if (! $deployment->desired_state->expectsRunning()) {
            if ($deployment->status === StorefrontDeploymentStatus::Processing) {
                $deployment->markFailed('Deployment abandoned: the storefront was stopped before it finished.');
            }

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

        return $deployment->status;
    }
}
