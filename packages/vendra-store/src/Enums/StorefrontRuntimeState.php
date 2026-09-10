<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Enums;

use Misaf\VendraStore\Support\StorefrontContainer;

/**
 * What is actually running for a storefront right now.
 *
 * The deployment row records what the platform *decided*; this reports what the
 * runtime *has*. They disagree whenever somebody stops a container by hand, a
 * host reboots, or a deployment failed halfway — which is exactly what
 * reconciliation exists to find.
 */
enum StorefrontRuntimeState: string
{
    case Absent = 'absent';
    case Created = 'created';
    case Running = 'running';
    case Unhealthy = 'unhealthy';
    case Stopped = 'stopped';
    case Unknown = 'unknown';

    /**
     * Reduce a container's reported state to the storefront's own vocabulary.
     *
     * A null container is Absent rather than an error: "there is nothing there"
     * is a legitimate answer to a status question and the common one before a
     * first deployment.
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

    /**
     * Whether the storefront is up and nothing contradicts it.
     */
    public function isServing(): bool
    {
        return $this === self::Running;
    }
}
