<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Services;

use Misaf\VendraStore\Contracts\StorefrontProvisioner;
use Misaf\VendraStore\Support\StorefrontConfigurationValidator;
use Misaf\VendraStore\Support\StorefrontContainer;
use Misaf\VendraStore\Support\StorefrontContainerDefinitionFactory;
use Misaf\VendraStore\Support\StorefrontObservation;
use Misaf\VendraStore\Support\StorefrontProvisionRequest;
use Misaf\VendraStore\Support\StorefrontProvisionResult;
use Misaf\VendraStore\Support\StorefrontReference;
use Misaf\VendraStore\Support\StorefrontRuntimeStatus;
use Misaf\VendraStore\Support\StorefrontSettings;
use RuntimeException;

/**
 * Runs each storefront as one container, through whichever runtime is bound.
 *
 * This class is the whole of the store layer's knowledge about containers,
 * It composes a storefront-specific definition and hands it to the runtime
 * adapter backed by `misaf/laravel-docker-engine`. Docker and Podman selection
 * stays in that package's Laravel Manager configuration.
 *
 * The platform owns the storefront containers and nothing around them. The
 * network, the reverse proxy, and the TLS material belong to whoever runs the
 * estate, so this reads that environment and refuses to guess at it: an absent
 * network is an error naming the network, not an improvised bridge the proxy is
 * not attached to.
 */
final class ContainerStorefrontProvisioner implements StorefrontProvisioner
{
    public function __construct(
        private readonly StorefrontContainerRuntime $runtime,
        private readonly StorefrontContainerHealthGate $healthGate,
        private readonly StorefrontConfigurationValidator $validator,
        private readonly StorefrontContainerDefinitionFactory $definitions,
        private readonly StorefrontSettings $settings,
    ) {}

    public function provision(StorefrontProvisionRequest $request): StorefrontProvisionResult
    {
        $this->validator->validate($request);

        $this->assertNetworkExists($this->assertRuntimeReachable());

        $definition = $this->definitions->build($request);

        if ($this->settings->pull) {
            $this->runtime->pull($definition->image);
        }

        /*
         | Replace rather than update: a runtime cannot change the image, labels,
         | or environment of an existing container, and a redeploy always changes
         | at least the encoded configuration. Removing first is also what makes
         | this idempotent, which reconciliation and retry both depend on.
         */
        $this->assertPlatformOwned($definition->name);
        $this->runtime->remove($definition->name);
        $this->runtime->create($definition);
        $this->runtime->start($definition->name);

        $ready = $this->healthGate->await($definition, $this->settings->healthTimeout);

        return StorefrontProvisionResult::make(
            ready: $ready,
            reference: $definition->name,
            imageDigest: $this->runtime->imageDigest($definition->image),
        );
    }

    public function start(StorefrontReference $storefront): void
    {
        $this->runtime->start($this->containerName($storefront));
    }

    public function stop(StorefrontReference $storefront): void
    {
        $this->runtime->stop($this->containerName($storefront));
    }

    public function restart(StorefrontReference $storefront): void
    {
        $this->runtime->restart($this->containerName($storefront));
    }

    public function destroy(StorefrontReference $storefront): void
    {
        $container = $this->containerName($storefront);

        $this->assertPlatformOwned($container);
        $this->runtime->remove($container);
    }

    /**
     * The runtime is pinged first, and deliberately so.
     *
     * `find()` cannot tell "no such container" from "the daemon answered with an
     * error" — both decode to null. Reconciliation reads a null as an absent
     * storefront and rebuilds it, so a momentarily confused daemon would cost the
     * estate a redeploy of everything it could not describe. Establishing the
     * runtime is answering first turns that into a named failure.
     */
    public function observe(StorefrontReference $storefront): StorefrontObservation
    {
        $this->assertRuntimeReachable();

        return StorefrontObservation::fromContainer(
            $this->runtime->find($this->containerName($storefront)),
        );
    }

    public function logs(StorefrontReference $storefront, int $lines = 200): string
    {
        return $this->runtime->logs($this->containerName($storefront), $lines);
    }

    private function containerName(StorefrontReference $storefront): string
    {
        return $this->settings->containerName($storefront->slug);
    }

    /**
     * Fail before anything is placed when the runtime is not answering.
     *
     * `ping()` reports rather than throws, so the message an operator sees names
     * the endpoint and whether it was the API version that was refused.
     */
    private function assertRuntimeReachable(): StorefrontRuntimeStatus
    {
        $status = $this->runtime->status();

        if (! $status->reachable) {
            throw new RuntimeException($status->message ?? 'The container runtime is not reachable.');
        }

        return $status;
    }

    /**
     * An absent network is reported against the daemon that was actually asked.
     *
     * Networks are per-daemon, so "it does not exist" and "you are talking to a
     * different daemon than you think" produce the identical symptom. Naming the
     * endpoint and what answered there separates them without the operator
     * having to go looking, and a configured/reported engine mismatch is called
     * out because it is the likeliest way to arrive here with the network sitting
     * in front of you on the other runtime.
     */
    private function assertNetworkExists(StorefrontRuntimeStatus $status): void
    {
        if ($this->runtime->findNetwork($this->settings->network) !== null) {
            return;
        }

        $message = sprintf(
            'The container network [%s] does not exist on %s. The platform manages storefront containers only — '
            .'create the network with the rest of the estate before provisioning.',
            $this->settings->network,
            $status->describeDaemon(),
        );

        if ($status->engineMismatch()) {
            $message .= sprintf(
                ' That endpoint is serving %s while CONTAINER_DRIVER is set to %s, so this may be the wrong daemon '
                .'rather than a missing network.',
                $status->reportedEngine(),
                $status->driver,
            );
        }

        throw new RuntimeException($message);
    }

    /**
     * Refuse to touch a container the platform did not place.
     *
     * Container names are chosen by an operator-configurable prefix, so a
     * collision with something else on the same runtime is possible. Removing
     * somebody else's container because it happened to answer to the name we
     * wanted is not a recoverable mistake, so ownership is checked first.
     */
    private function assertPlatformOwned(string $container): void
    {
        $existing = $this->runtime->find($container);

        if ($existing === null || $this->isPlatformOwned($existing)) {
            return;
        }

        throw new RuntimeException(sprintf(
            'The container [%s] already exists and was not placed by the platform. '
            .'Rename it, or change STOREFRONT_NAME_PREFIX, before deploying this storefront.',
            $container,
        ));
    }

    private function isPlatformOwned(StorefrontContainer $container): bool
    {
        return $container->hasLabel(
            StorefrontContainerDefinitionFactory::MANAGED_BY_LABEL,
            StorefrontContainerDefinitionFactory::MANAGED_BY,
        );
    }
}
