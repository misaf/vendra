<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Support;

use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;

/**
 * The container runtime's health as the storefront worker last saw it.
 *
 * Only the storefront worker holds a runtime socket, so the panels never probe
 * the runtime themselves: they read the report the worker recorded. It travels
 * through the cache as a plain array, because the host's
 * `cache.serializable_classes` allow-list turns any cached object into
 * `__PHP_Incomplete_Class`.
 */
final readonly class StorefrontRuntimeHealthReport
{
    /**
     * A report older than this means the worker or the scheduler stopped
     * recording, so its contents can no longer be trusted as current.
     */
    public const int STALE_AFTER_SECONDS = 300;

    public function __construct(
        public StorefrontRuntimeStatus $status,
        public string $networkName,
        public ?StorefrontNetwork $network,
        public ?string $networkError,
        public CarbonImmutable $checkedAt,
    ) {}

    public function isStale(): bool
    {
        return $this->checkedAt->addSeconds(self::STALE_AFTER_SECONDS)->isPast();
    }

    public function isHealthy(): bool
    {
        return ! $this->isStale()
            && $this->status->reachable
            && ! $this->status->engineMismatch()
            && $this->network instanceof StorefrontNetwork;
    }

    /**
     * @return array{status: array{reachable: bool, driver: string, apiVersion: string, server: ?string, message: ?string, endpoint: ?string}, network_name: string, network: array{name: string, driver: ?string}|null, network_error: ?string, checked_at: string}
     */
    public function toArray(): array
    {
        return [
            'status' => [
                'reachable' => $this->status->reachable,
                'driver' => $this->status->driver,
                'apiVersion' => $this->status->apiVersion,
                'server' => $this->status->server,
                'message' => $this->status->message,
                'endpoint' => $this->status->endpoint,
            ],
            'network_name' => $this->networkName,
            'network' => $this->network instanceof StorefrontNetwork
                ? ['name' => $this->network->name, 'driver' => $this->network->driver]
                : null,
            'network_error' => $this->networkError,
            'checked_at' => $this->checkedAt->toIso8601String(),
        ];
    }

    /**
     * @param  array<mixed>  $data  as read back from the cache, so untrusted in shape
     */
    public static function fromArray(array $data): self
    {
        $status = Arr::array($data, 'status');
        $network = Arr::get($data, 'network');

        return new self(
            status: new StorefrontRuntimeStatus(
                reachable: Arr::boolean($status, 'reachable'),
                driver: Arr::string($status, 'driver'),
                apiVersion: Arr::string($status, 'apiVersion'),
                server: self::nullableString($status, 'server'),
                message: self::nullableString($status, 'message'),
                endpoint: self::nullableString($status, 'endpoint'),
            ),
            networkName: Arr::string($data, 'network_name'),
            network: is_array($network)
                ? new StorefrontNetwork(
                    name: Arr::string($network, 'name'),
                    driver: self::nullableString($network, 'driver'),
                )
                : null,
            networkError: self::nullableString($data, 'network_error'),
            checkedAt: Date::parse(Arr::string($data, 'checked_at'))->toImmutable(),
        );
    }

    /**
     * @param  array<mixed>  $data
     */
    private static function nullableString(array $data, string $key): ?string
    {
        $value = Arr::get($data, $key);

        return is_string($value) ? $value : null;
    }
}
