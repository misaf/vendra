<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Misaf\DockerEngine\Dto\Container\LogsOptions;
use Misaf\DockerEngine\Exceptions\Api\ApiException;
use Misaf\DockerEngine\Exceptions\Api\NotFoundException;
use Misaf\DockerEngine\Exceptions\DockerException;
use Misaf\DockerEngine\Streaming\RawStream;
use Misaf\LaravelDockerEngine\ContainerManager;
use Misaf\VendraStore\Support\StorefrontContainer;
use Misaf\VendraStore\Support\StorefrontContainerDefinition;
use Misaf\VendraStore\Support\StorefrontNetwork;
use Misaf\VendraStore\Support\StorefrontRuntimeStatus;
use RuntimeException;
use Throwable;

final readonly class StorefrontContainerRuntime
{
    public function __construct(private ContainerManager $containers) {}

    public function status(): StorefrontRuntimeStatus
    {
        $driver = Config::string('container.default', 'docker');
        $configuration = Config::array("container.drivers.{$driver}");
        $endpoint = Arr::get($configuration, 'host');
        $configuredApiVersion = Arr::get($configuration, 'api_version');

        try {
            $response = $this->containers->raw()->request('GET', '/_ping');

            return new StorefrontRuntimeStatus(
                reachable: true,
                driver: $driver,
                apiVersion: $this->containers->version()->value,
                server: $response->header('Server'),
                endpoint: is_string($endpoint) ? $endpoint : null,
            );
        } catch (Throwable $exception) {
            return new StorefrontRuntimeStatus(
                reachable: false,
                driver: $driver,
                apiVersion: is_string($configuredApiVersion) ? mb_ltrim($configuredApiVersion, 'vV') : 'auto',
                message: $exception->getMessage(),
                endpoint: is_string($endpoint) ? $endpoint : null,
            );
        }
    }

    public function pull(string $image): void
    {
        foreach ($this->containers->images()->pull($image) as $event) {
            if ($event->error !== null) {
                throw new RuntimeException(sprintf('Unable to pull image [%s]: %s.', $image, $event->error));
            }
        }
    }

    public function create(StorefrontContainerDefinition $definition): StorefrontContainer
    {
        $response = $this->containers->raw()->request(
            'POST',
            '/containers/create',
            ['name' => $definition->name],
            body: $definition->enginePayload(),
        );
        $id = $response->json('Id');

        if (! is_string($id) || $id === '') {
            throw new RuntimeException(sprintf('The runtime returned no id for container [%s].', $definition->name));
        }

        return $this->inspect($id);
    }

    public function start(string $container): void
    {
        $this->idempotentRequest('POST', $container, 'start', [304]);
    }

    public function stop(string $container): void
    {
        $this->idempotentRequest('POST', $container, 'stop', [304]);
    }

    public function restart(string $container): void
    {
        $this->idempotentRequest('POST', $container, 'restart');
    }

    public function remove(string $container): void
    {
        try {
            $this->containers->raw()->request('DELETE', '/containers/'.rawurlencode($container), [
                'force' => true,
                'v' => true,
            ]);
        } catch (NotFoundException) {
            // The requested end state is already satisfied.
        }
    }

    public function inspect(string $container): StorefrontContainer
    {
        return $this->find($container)
            ?? throw new RuntimeException(sprintf('The container [%s] does not exist.', $container));
    }

    public function find(string $container): ?StorefrontContainer
    {
        try {
            $payload = $this->containers->raw()
                ->request('GET', '/containers/'.rawurlencode($container).'/json')
                ->json();
        } catch (NotFoundException) {
            return null;
        }

        return StorefrontContainer::fromEnginePayload($payload);
    }

    public function logs(string $container, int $lines = 200): string
    {
        $stream = $this->containers->containers()->logs($container, new LogsOptions(
            tail: (string) max($lines, 1),
        ));

        if ($stream instanceof RawStream) {
            return implode('', iterator_to_array($stream->chunks()));
        }

        $output = '';
        $stream->consume(
            static function (string $chunk) use (&$output): void {
                $output .= $chunk;
            },
            static function (string $chunk) use (&$output): void {
                $output .= $chunk;
            },
        );

        return $output;
    }

    public function findNetwork(string $name): ?StorefrontNetwork
    {
        try {
            $payload = $this->containers->raw()
                ->request('GET', '/networks/'.rawurlencode($name))
                ->json();
        } catch (NotFoundException) {
            return null;
        }

        $driver = Arr::get($payload, 'Driver');
        $networkName = Arr::get($payload, 'Name');

        return new StorefrontNetwork(
            name: is_string($networkName) ? $networkName : $name,
            driver: is_string($driver) && $driver !== '' ? $driver : null,
        );
    }

    public function imageDigest(string $image): ?string
    {
        if (Str::contains($image, '@')) {
            return Str::after($image, '@');
        }

        try {
            $payload = $this->containers->raw()
                ->request('GET', '/images/'.rawurlencode($image).'/json')
                ->json();
        } catch (DockerException) {
            return null;
        }

        foreach ((array) Arr::get($payload, 'RepoDigests', []) as $digest) {
            if (is_string($digest) && Str::contains($digest, '@sha256:')) {
                return 'sha256:'.Str::after($digest, '@sha256:');
            }
        }

        return null;
    }

    /** @param list<int> $acceptedStatuses */
    private function idempotentRequest(string $method, string $container, string $operation, array $acceptedStatuses = []): void
    {
        try {
            $this->containers->raw()->request(
                $method,
                '/containers/'.rawurlencode($container).'/'.$operation,
            );
        } catch (ApiException $exception) {
            throw_unless(in_array($exception->statusCode, $acceptedStatuses, true), $exception);
        }
    }
}
