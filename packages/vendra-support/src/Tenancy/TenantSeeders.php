<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tenancy;

use Misaf\VendraSupport\Tenancy\Console\Commands\SeedCommand;

final class TenantSeeders
{
    /**
     * @var array<class-string<SeedCommand>, int> seed command class => priority
     */
    private array $commands = [];

    /**
     * @param  class-string<SeedCommand>  $command
     */
    public function register(string $command, int $priority = 100): void
    {
        $this->commands[$command] = $priority;
    }

    /**
     * Get the seed commands by ascending priority, keeping registration order for ties.
     *
     * @return list<class-string<SeedCommand>>
     */
    public function ordered(): array
    {
        $commands = $this->commands;

        asort($commands);

        return array_keys($commands);
    }
}
