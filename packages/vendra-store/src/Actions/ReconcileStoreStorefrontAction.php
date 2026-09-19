<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use InvalidArgumentException;
use Misaf\VendraStore\Contracts\StorefrontProvisioner;
use Misaf\VendraStore\Enums\StorefrontDeploymentStatus;
use Misaf\VendraStore\Enums\StorefrontReconciliationOutcome;
use Misaf\VendraStore\Enums\StorefrontRuntimeState;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraStore\Support\StorefrontObservation;
use Misaf\VendraStore\Support\StorefrontReference;

/**
 * Apply the smallest action that closes the gap, since a redeploy replaces the
 * container. Never write `desired_state`, or a deliberately stopped storefront
 * would be started again on every pass.
 */
final readonly class ReconcileStoreStorefrontAction
{
    public function __construct(
        private StorefrontProvisioner $provisioner,
        private DeployStoreStorefrontAction $deploy,
    ) {}

    public function execute(StorefrontDeployment $deployment): StorefrontReconciliationOutcome
    {
        $observed = $this->provisioner->observe(StorefrontReference::for($deployment));

        return $deployment->desired_state->expectsRunning()
            ? $this->converge($deployment, $observed)
            : $this->settle($deployment, $observed);
    }

    private function converge(
        StorefrontDeployment $deployment,
        StorefrontObservation $observed,
    ): StorefrontReconciliationOutcome {
        if ($observed->isAbsent()) {
            $this->redeploy($deployment);

            return StorefrontReconciliationOutcome::Deployed;
        }

        /*
         | Placed but not running. Starting it preserves the container, its image,
         | and its labels — rebuilding would discard all three to achieve the same
         | end, and cost the storefront a pull and a health gate to get there.
         */
        if (in_array($observed->state, [StorefrontRuntimeState::Stopped, StorefrontRuntimeState::Created], true)) {
            $this->provisioner->start(StorefrontReference::for($deployment));

            return StorefrontReconciliationOutcome::Started;
        }

        if ($observed->state->isServing()
            && ! $observed->isServingOtherThan($this->desiredImage($deployment))
            && ! $observed->isServingDomainOtherThan($deployment->domain)) {
            /*
             | A deployment whose health gate timed out was recorded as Requested
             | for this pass to revisit. Serving the desired image on its domain
             | is the proof it was waiting for.
             */
            if ($deployment->status === StorefrontDeploymentStatus::Requested) {
                $deployment->markReady(
                    $observed->containerName ?? $deployment->container_name,
                    $this->desiredImage($deployment),
                    $deployment->image_digest,
                );
            }

            return StorefrontReconciliationOutcome::InSync;
        }

        /*
         | Serving the wrong image, routed on a domain the store has moved off,
         | failing its health check, or in a state this layer has no vocabulary
         | for. Replacing it is the only verb that reaches a known-good storefront
         | from any of them — and for the domain it is the only one available at
         | all, since a container's routing labels are fixed when it is created.
         */
        $this->redeploy($deployment);

        return StorefrontReconciliationOutcome::Redeployed;
    }

    private function settle(
        StorefrontDeployment $deployment,
        StorefrontObservation $observed,
    ): StorefrontReconciliationOutcome {
        if ($observed->isAbsent() || $observed->state === StorefrontRuntimeState::Stopped) {
            return StorefrontReconciliationOutcome::InSync;
        }

        $this->provisioner->stop(StorefrontReference::for($deployment));

        return StorefrontReconciliationOutcome::Stopped;
    }

    /**
     * Force the deploy, since a Ready row with nothing running is exactly the
     * drift being repaired.
     */
    private function redeploy(StorefrontDeployment $deployment): void
    {
        $this->deploy->execute($deployment, force: true);
    }

    private function desiredImage(StorefrontDeployment $deployment): string
    {
        throw_unless($deployment->storefrontImage()->exists(), InvalidArgumentException::class, 'Select a storefront image before reconciling this storefront.');

        return $deployment->storefrontImage()->firstOrFail()->image;
    }
}
