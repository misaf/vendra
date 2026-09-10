<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Support;

use Illuminate\Support\Str;

final readonly class StorefrontRuntimeStatus
{
    public function __construct(
        public bool $reachable,
        public string $driver,
        public string $apiVersion,
        public ?string $server = null,
        public ?string $message = null,
        public ?string $endpoint = null,
    ) {}

    public function reportedEngine(): ?string
    {
        return match (true) {
            Str::contains((string) $this->server, 'libpod', ignoreCase: true) => 'podman',
            Str::contains((string) $this->server, 'docker', ignoreCase: true) => 'docker',
            default => null,
        };
    }

    public function engineMismatch(): bool
    {
        $reportedEngine = $this->reportedEngine();

        return $this->reachable && $reportedEngine !== null && $reportedEngine !== $this->driver;
    }

    public function describeDaemon(): string
    {
        $endpoint = $this->endpoint ?? 'an unconfigured endpoint';

        return $this->server === null ? $endpoint : sprintf('%s (%s)', $endpoint, $this->server);
    }
}
