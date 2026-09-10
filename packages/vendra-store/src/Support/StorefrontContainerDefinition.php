<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Support;

use stdClass;

final class StorefrontContainerDefinition
{
    private const int NANOSECOND = 1_000_000_000;

    /**
     * @param  array<string, string>  $environment
     * @param  array<string, string>  $labels
     * @param  list<string>  $binds
     * @param  array<string, string>  $logOptions
     * @param  list<string>  $healthCheck
     * @param  list<string>  $securityOptions
     */
    public function __construct(
        public readonly string $name,
        public readonly string $image,
        public readonly array $environment,
        public readonly array $labels,
        public readonly int $port,
        public readonly array $binds,
        public readonly string $network,
        public readonly array $healthCheck,
        public readonly string $logDriver,
        public readonly array $logOptions,
        public readonly StorefrontContainerResources $resources,
        public readonly array $securityOptions,
    ) {}

    /** @return array<string, mixed> */
    public function enginePayload(): array
    {
        $port = $this->port.'/tcp';
        $hostConfiguration = [
            'RestartPolicy' => ['Name' => 'unless-stopped'],
            'SecurityOpt' => $this->securityOptions,
            'Binds' => $this->binds,
            'NetworkMode' => $this->network,
            ...$this->resources->enginePayload(),
        ];

        if ($this->logDriver !== '') {
            $hostConfiguration['LogConfig'] = array_filter([
                'Type' => $this->logDriver,
                'Config' => $this->logOptions,
            ], static fn (mixed $value): bool => $value !== []);
        }

        return [
            'Image' => $this->image,
            'Env' => array_map(
                static fn (string $name, string $value): string => $name.'='.$value,
                array_keys($this->environment),
                array_values($this->environment),
            ),
            'Labels' => $this->labels,
            'ExposedPorts' => [$port => new stdClass],
            'Healthcheck' => [
                'Test' => $this->healthCheck,
                'Interval' => 10 * self::NANOSECOND,
                'Timeout' => 3 * self::NANOSECOND,
                'Retries' => 12,
                'StartPeriod' => 15 * self::NANOSECOND,
            ],
            'HostConfig' => $hostConfiguration,
            'NetworkingConfig' => [
                'EndpointsConfig' => [$this->network => new stdClass],
            ],
        ];
    }
}
