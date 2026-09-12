<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use InvalidArgumentException;
use Misaf\VendraStore\Contracts\StorefrontProvisioner;
use Misaf\VendraStore\Enums\StorefrontReconciliationOutcome;
use Misaf\VendraStore\Enums\StorefrontRuntimeState;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraStore\Support\StorefrontObservation;
use Misaf\VendraStore\Support\StorefrontReference;

/**
 * Brings one storefront's runtime into line with what the platform intends.
 *
 * Observe, diff, then apply the *smallest* verb that closes the gap. That last
 * part is the whole point: this used to be a forced redeploy of everything, and
 * since provisioning replaces a container rather than updating it, asking to
 * reconcile an estate took every storefront in it down and back up. A stopped
 * storefront needs starting, not rebuilding.
 *
 * Reconciliation reads intent and never writes it. `desired_state` is a decision
 * somebody made — an administrator stopping a storefront, a deployment requesting
 * one — and a converge pass that edited it would be deciding rather than
 * converging, which is how a deliberately stopped storefront gets started again
 * every pass.
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

    /**
     * Close the gap for a storefront that is meant to be serving.
     */
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

    /**
     * Close the gap for a storefront that is meant to be down.
     */
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
     * Forced on purpose: the recorded status is exactly what is not to be
     * trusted here. A row reading Ready with nothing running is the case
     * reconciliation exists to catch, and an unforced deploy would return
     * without doing anything precisely then.
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
