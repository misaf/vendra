<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tenancy;

final class TenantSeeders
{
    /**
     * @var array<string, int> seed command signature => priority
     */
    private array $commands = [];

    public function register(string $command, int $priority = 100): void
    {
        $this->commands[$command] = $priority;
    }

    /**
     * Get the seed commands by ascending priority, keeping registration order for ties.
     *
     * @return list<string>
     */
    public function ordered(): array
    {
        $commands = $this->commands;

        asort($commands);

        return array_keys($commands);
    }
}
