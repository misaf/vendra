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
 * The network, proxy, and TLS belong to the host environment, so a missing
 * network is an error rather than something created here.
 */
final readonly class ContainerStorefrontProvisioner implements StorefrontProvisioner
{
    public function __construct(
        private StorefrontContainerRuntime $runtime,
        private StorefrontContainerHealthGate $healthGate,
        private StorefrontConfigurationValidator $validator,
        private StorefrontContainerDefinitionFactory $definitions,
        private StorefrontSettings $settings,
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
        $container = $this->containerName($storefront);

        $this->assertPlatformOwned($container);
        $this->runtime->start($container);
    }

    public function stop(StorefrontReference $storefront): void
    {
        $container = $this->containerName($storefront);

        $this->assertPlatformOwned($container);
        $this->runtime->stop($container);
    }

    public function restart(StorefrontReference $storefront): void
    {
        $container = $this->containerName($storefront);

        $this->assertPlatformOwned($container);
        $this->runtime->restart($container);
    }

    public function destroy(StorefrontReference $storefront): void
    {
        $container = $this->containerName($storefront);

        $this->assertPlatformOwned($container);
        $this->runtime->remove($container);
    }

    /**
     * Ping the runtime first, since `find()` returns null for both a missing
     * container and a daemon error.
     */
    public function observe(StorefrontReference $storefront): StorefrontObservation
    {
        $this->assertRuntimeReachable();

        $container = $this->runtime->find($this->containerName($storefront));

        /*
         | A foreign container under a storefront's name is neither absent nor
         | ours: reconciliation would otherwise stop or start it.
         */
        if ($container !== null && ! $this->isPlatformOwned($container)) {
            throw $this->foreignContainer($container->name);
        }

        return StorefrontObservation::fromContainer($container);
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
     * Fail with the endpoint's details when the runtime is not answering.
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
     * Fail when the network is missing, naming the daemon that was asked.
     *
     * Networks are per daemon, so an engine mismatch is called out explicitly.
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
     * The name prefix is configurable, so a collision with another container is possible.
     */
    private function assertPlatformOwned(string $container): void
    {
        $existing = $this->runtime->find($container);

        if ($existing === null || $this->isPlatformOwned($existing)) {
            return;
        }

        throw $this->foreignContainer($container);
    }

    private function foreignContainer(string $container): RuntimeException
    {
        return new RuntimeException(sprintf(
            'The container [%s] already exists and was not placed by the platform. '
            .'Rename it, or change STOREFRONT_NAME_PREFIX, before managing this storefront.',
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
