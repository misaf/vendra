<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Support;

use stdClass;

final readonly class StorefrontContainerDefinition
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
        public string $name,
        public string $image,
        public array $environment,
        public array $labels,
        public int $port,
        public array $binds,
        public string $network,
        public array $healthCheck,
        public string $logDriver,
        public array $logOptions,
        public StorefrontContainerResources $resources,
        public array $securityOptions,
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
