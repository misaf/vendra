<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Services;

use Misaf\VendraStore\Support\StorefrontContainerDefinition;
use Psr\Log\LoggerInterface;
use RuntimeException;

final class StorefrontContainerHealthGate
{
    private const int POLL_MICROSECONDS = 2_000_000;

    public function __construct(
        private readonly StorefrontContainerRuntime $runtime,
        private readonly LoggerInterface $logger,
    ) {}

    public function await(StorefrontContainerDefinition $definition, int $timeoutSeconds): bool
    {
        $deadline = microtime(true) + max($timeoutSeconds, 1);

        do {
            $container = $this->runtime->inspect($definition->name);

            if ($container->hasStopped()) {
                throw new RuntimeException(sprintf(
                    'The container [%s] exited while starting with code %s.',
                    $definition->name,
                    $container->exitCode === null ? 'unknown' : (string) $container->exitCode,
                ));
            }

            if ($container->health === 'healthy') {
                return true;
            }

            if ($container->health === null && $container->isRunning()) {
                $this->logger->warning(
                    'Container reports no health state; treating it as ready once running.',
                    ['container' => $definition->name],
                );

                return true;
            }

            if (microtime(true) >= $deadline) {
                return false;
            }

            usleep(self::POLL_MICROSECONDS);
        } while (true);
    }
}
