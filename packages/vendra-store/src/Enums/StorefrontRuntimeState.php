<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Enums;

use Misaf\VendraStore\Support\StorefrontContainer;

enum StorefrontRuntimeState: string
{
    case Absent = 'absent';
    case Created = 'created';
    case Running = 'running';
    case Unhealthy = 'unhealthy';
    case Stopped = 'stopped';
    case Unknown = 'unknown';

    /**
     * Map a container's state to a runtime state; no container means Absent.
     */
    public static function fromContainer(?StorefrontContainer $container): self
    {
        if ($container === null) {
            return self::Absent;
        }

        if ($container->hasStopped()) {
            return self::Stopped;
        }

        if (! $container->isRunning()) {
            return match ($container->state) {
                'created' => self::Created,
                default => self::Unknown,
            };
        }

        return $container->health === 'unhealthy'
            ? self::Unhealthy
            : self::Running;
    }

    public function isServing(): bool
    {
        return $this === self::Running;
    }
}
